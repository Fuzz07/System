<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use App\Models\SchoolYear;
use Illuminate\Support\Carbon;

class SscHelper
{
    public static function formatCurrency(float $amount): string
    {
        return '₱' . number_format($amount, 2);
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'Approved' => '<span class="badge badge-approved">Approved</span>',
            'Pending'  => '<span class="badge badge-pending">Pending</span>',
            'Rejected' => '<span class="badge badge-rejected">Rejected</span>',
            'Reviewed' => '<span class="badge" style="background:rgba(14,165,233,.1);color:#0ea5e9;">Reviewed</span>',
            'active'   => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            default    => '<span class="badge bg-secondary">' . e($status) . '</span>',
        };
    }

    public static function roleBadge(string $role): string
    {
        return match ($role) {
            'admin'     => '<span class="badge" style="background:rgba(79,70,229,.1);color:#4f46e5;">Admin</span>',
            'treasurer' => '<span class="badge" style="background:rgba(16,185,129,.1);color:#10b981;">Treasurer</span>',
            'officer'   => '<span class="badge" style="background:rgba(14,165,233,.1);color:#0ea5e9;">Officer</span>',
            'student'   => '<span class="badge" style="background:rgba(245,158,11,.1);color:#f59e0b;">Student</span>',
            default     => '<span class="badge bg-secondary">' . ucfirst($role) . '</span>',
        };
    }

    public static function timeAgo(string|Carbon $date): string
    {
        return Carbon::parse($date)->diffForHumans();
    }

    public static function logActivity(?int $userId, string $action, string $details = ''): void
    {
        $ua      = request()->userAgent() ?? 'Unknown';
        $details = $details ? "{$details} | UA: {$ua}" : "UA: {$ua}";

        ActivityLog::create([
            'user_id'    => ($userId === 0 || $userId === null) ? null : $userId,
            'action'     => $action,
            'details'    => $details,
            'ip_address' => request()->ip(),
        ]);
    }

    public static function getActiveSchoolYear(): string
    {
        $sy = SchoolYear::where('is_active', 1)->first();
        return $sy ? $sy->label : 'N/A';
    }
}
