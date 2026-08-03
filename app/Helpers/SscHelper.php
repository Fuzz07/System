<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use App\Models\SchoolYear;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
            'Replied'  => '<span class="badge" style="background:rgba(16,185,129,.1);color:#10b981;">Replied</span>',
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

    public static function getUploadUrl(?string $path): string
    {
        if (!$path) {
            return '#';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . $path);
    }

    /**
     * Checks if coordinates fall within the geographical boundary box of the Philippines.
     */
    public static function isWithinPhilippines(float $latitude, float $longitude): bool
    {
        return ($latitude >= 4.0 && $latitude <= 21.5) && ($longitude >= 116.0 && $longitude <= 127.0);
    }

    /**
     * Verifies if the client IP address originates from the Philippines (or is local/private).
     */
    public static function isIpInPhilippines(?string $ip): bool
    {
        if (empty($ip)) {
            return true;
        }

        // Allow localhost & private networks (RFC 1918 / RFC 4193 / loopback)
        if (
            in_array($ip, ['127.0.0.1', '::1'], true) ||
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
        ) {
            return true;
        }

        // 1. Check Cloudflare / CDN header if present
        $cfCountry = request()->header('CF-IPCountry') ?? request()->server('HTTP_CF_IPCOUNTRY');
        if (!empty($cfCountry)) {
            return strtoupper($cfCountry) === 'PH';
        }

        // 2. Server-side IP lookup with caching (24 hours per IP)
        return Cache::remember("geo_ip_ph_{$ip}", 86400, function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,countryCode");
                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'success') {
                        return strtoupper($data['countryCode'] ?? '') === 'PH';
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("IP Geolocation lookup failed for IP {$ip}: " . $e->getMessage());
                return true;
            }
            return true;
        });
    }

    /**
     * Uploads a file directly to Cloudinary using the official PHP SDK, bypassing Laravel service provider discovery.
     */
    public static function uploadToCloudinary($file, string $folder): string
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');
        if (!$cloudinaryUrl) {
            throw new \Exception('CLOUDINARY_URL is not set in the environment.');
        }

        $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);
        $response = $cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder
        ]);

        if (empty($response['secure_url'])) {
            throw new \Exception('Cloudinary upload response did not return secure_url.');
        }

        return $response['secure_url'];
    }
}
