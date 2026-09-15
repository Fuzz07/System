<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class MaintenanceHelper
{
    protected const FILE_PATH = 'framework/maintenance.json';
    protected const CACHE_KEY = 'system_maintenance_data';

    /**
     * Check whether maintenance mode is currently active.
     */
    public static function isDown(): bool
    {
        $data = static::getData();
        return !empty($data['active']);
    }

    /**
     * Get maintenance mode configuration data.
     */
    public static function getData(): array
    {
        $default = [
            'active' => false,
            'message' => 'The system is currently undergoing scheduled maintenance. Please check back shortly.',
            'enabled_at' => null,
            'enabled_by' => null,
            'enabled_by_name' => null,
        ];

        try {
            // Check cache first for rapid retrieval
            $cached = Cache::get(self::CACHE_KEY);
            if (is_array($cached)) {
                return array_merge($default, $cached);
            }

            // Fallback to storage file
            $filePath = storage_path(self::FILE_PATH);
            if (File::exists($filePath)) {
                $content = json_decode(File::get($filePath), true);
                if (is_array($content)) {
                    $merged = array_merge($default, $content);
                    Cache::forever(self::CACHE_KEY, $merged);
                    return $merged;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MaintenanceHelper getData error: ' . $e->getMessage());
        }

        return $default;
    }

    /**
     * Enable maintenance mode.
     */
    public static function enable(?int $userId = null, ?string $userName = null, ?string $message = null): void
    {
        $data = [
            'active' => true,
            'message' => trim($message ?: 'The system is currently undergoing scheduled maintenance. Please check back shortly.'),
            'enabled_at' => now()->toDateTimeString(),
            'enabled_by' => $userId,
            'enabled_by_name' => $userName ?: 'Administrator',
        ];

        static::saveData($data);
    }

    /**
     * Disable maintenance mode.
     */
    public static function disable(): void
    {
        $data = [
            'active' => false,
            'message' => 'The system is currently operational.',
            'enabled_at' => null,
            'enabled_by' => null,
            'enabled_by_name' => null,
        ];

        static::saveData($data);
    }

    /**
     * Persist maintenance mode state to cache and storage file.
     */
    protected static function saveData(array $data): void
    {
        try {
            Cache::forever(self::CACHE_KEY, $data);

            $filePath = storage_path(self::FILE_PATH);
            $dir = dirname($filePath);
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }

            File::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('MaintenanceHelper saveData error: ' . $e->getMessage());
        }
    }
}
