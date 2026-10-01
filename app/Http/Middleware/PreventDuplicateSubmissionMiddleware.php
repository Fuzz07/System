<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Accepts each form submission once.
 *
 * The submit guard (partials/submit-guard) stamps every form it sends with a
 * random _submit_token that stays the same until the page is left. When the
 * same token arrives a second time — a double click that slipped past the
 * browser, a retry on a slow connection, a "Confirm Form Resubmission" refresh —
 * the repeat is turned away before it reaches a controller, so nothing is
 * created, approved or paid twice.
 */
class PreventDuplicateSubmissionMiddleware
{
    public const FIELD = '_submit_token';

    /** How long a used token is remembered, in seconds. */
    private const TTL = 6 * 60 * 60;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $token = $request->input(self::FIELD);

        // Controllers never see the token, so it cannot end up in a mass assignment.
        $request->request->remove(self::FIELD);

        if (! is_string($token) || ! preg_match('/^[A-Za-z0-9_-]{16,100}$/', $token)) {
            return $next($request);
        }

        // Sessions are in the database and production runs on several serverless
        // instances, so the token is recorded there too: a file cache would only
        // see the requests that happen to land on the same instance.
        try {
            $store = Cache::store('database');
            // add() is a single INSERT IGNORE on the key, so when two copies race,
            // exactly one of them gets through.
            $isFirst = $store->add('submit-token:'.$token, true, self::TTL);

            if (random_int(1, 100) === 1) {
                $this->pruneExpired();
            }
        } catch (Throwable $e) {
            // Every form post passes through here; if the cache table cannot be
            // reached, let the request through rather than block all of them.
            report($e);

            return $next($request);
        }

        if (! $isFirst) {
            return $this->rejectDuplicate($request);
        }

        return $next($request);
    }

    private function rejectDuplicate(Request $request): Response
    {
        $message = 'This form was already submitted, so the repeat submission was ignored.';

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 409);
        }

        return redirect()->back()->with('warning', $message);
    }

    /** The database cache never deletes expired rows on its own; one token is stored per form post. */
    private function pruneExpired(): void
    {
        DB::connection(config('cache.stores.database.connection'))
            ->table(config('cache.stores.database.table', 'cache'))
            ->where('expiration', '<=', time())
            ->delete();
    }
}
