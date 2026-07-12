<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $query = ActivityLog::with('user');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%$search%")
                  ->orWhere('details', 'like', "%$search%")
                  ->orWhereHas('user', fn($u) => $u->where('fullname', 'like', "%$search%"));
            });
        }
        $logs = $query->orderByDesc('created_at')->paginate(25);
        return view('admin.logs', compact('logs', 'search'));
    }
}
