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
            'created_at' => Carbon::now(),
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

    /**
     * Parses a raw User-Agent header string to return OS, Browser, Device Type and Icon.
     */
    public static function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'platform' => 'Unknown OS',
                'browser' => 'Unknown Browser',
                'device_type' => 'Desktop',
                'icon' => 'bi-laptop',
                'label' => 'Unknown Device',
            ];
        }

        $platform = 'Unknown OS';
        $browser = 'Unknown Browser';
        $icon = 'bi-laptop';
        $deviceType = 'Desktop';

        // Platform detection
        if (preg_match('/windows|win32|win64/i', $userAgent)) {
            $platform = 'Windows';
            $icon = 'bi-laptop';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
            $icon = 'bi-laptop';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
            $deviceType = 'Mobile';
            $icon = 'bi-phone';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
            $deviceType = 'Mobile';
            $icon = 'bi-phone';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
            $icon = 'bi-laptop';
        }

        // Browser & App detection
        if (str_contains($userAgent, 'SSCStudentApp')) {
            $browser = 'SSC Mobile App';
            $icon = 'bi-phone-fill';
            $deviceType = 'Mobile';
        } elseif (preg_match('/edg/i', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        return [
            'platform' => $platform,
            'browser' => $browser,
            'device_type' => $deviceType,
            'icon' => $icon,
            'label' => "{$platform} • {$browser}",
        ];
    }

    /**
     * Formats activity log details, converting latitude and longitude coordinates into clickable Google Maps links.
     */
    public static function formatLogDetails(?string $details): string
    {
        if (empty($details)) {
            return '<span class="text-muted">—</span>';
        }

        $escaped = e($details);

        // Matches formats like:
        // - "Location: Lat 14.5995, Lng 120.9842"
        // - "Lat: 14.5995, Lng: 120.9842"
        // - "Lat 14.5995, Lng 120.9842"
        // - "Latitude: 14.5995, Longitude: 120.9842"
        // - "Coords: 14.5995, 120.9842"
        // - "Coordinates: 14.5995, 120.9842"
        $pattern = '/(?:Location:\s*)?(?:Lat(?:itude)?[:\s]*([+-]?\d+(?:\.\d+)?)\s*,\s*Lng(?:itude)?[:\s]*([+-]?\d+(?:\.\d+)?)|(?:Coords|Coordinates)[:\s]*([+-]?\d+(?:\.\d+)?)\s*,\s*([+-]?\d+(?:\.\d+)?))/i';

        return preg_replace_callback($pattern, function ($matches) {
            $lat = !empty($matches[1]) ? $matches[1] : $matches[3];
            $lng = !empty($matches[2]) ? $matches[2] : $matches[4];

            $mapsUrl = 'https://www.google.com/maps?q=' . urlencode("{$lat},{$lng}");

            return '<a href="' . $mapsUrl . '" target="_blank" rel="noopener noreferrer" class="badge bg-white text-danger border text-decoration-none shadow-sm d-inline-flex align-items-center gap-1 mx-1 my-1 px-2 py-1 location-map-link" title="Open in Google Maps (' . $lat . ', ' . $lng . ')" style="font-size: 0.72rem; vertical-align: middle; border-color: #fca5a5 !important; color: #b91c1c !important; background-color: #fef2f2 !important; cursor: pointer; text-decoration: none !important;">'
                . '<i class="bi bi-geo-alt-fill" style="color: #ef4444; font-size: 0.78rem;"></i> '
                . '<span>Lat ' . $lat . ', Lng ' . $lng . '</span> '
                . '<i class="bi bi-box-arrow-up-right" style="font-size: 0.62rem; opacity: 0.75;"></i>'
                . '</a>';
        }, $escaped);
    }
}
