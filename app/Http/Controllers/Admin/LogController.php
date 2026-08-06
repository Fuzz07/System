<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $role = trim($request->input('role', ''));
        $actionType = trim($request->input('action_type', ''));

        $query = ActivityLog::with('user');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('fullname', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if (!empty($role)) {
            if ($role === 'system') {
                $query->whereNull('user_id');
            } else {
                $query->whereHas('user', fn($u) => $u->where('role', $role));
            }
        }

        if (!empty($actionType)) {
            $query->where('action', $actionType);
        }

        $logs = $query->orderByDesc('id')->paginate(25)->withQueryString();

        $distinctActions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.logs', compact('logs', 'search', 'role', 'actionType', 'distinctActions'));
    }
}
