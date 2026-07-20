<?php

namespace App\Services;

use App\Models\DeviceToken;
use Google\Auth\ApplicationDefaultCredentialsProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    private static $accessToken = null;
    private static $tokenExpire = null;

    /**
     * Send a push notification to specific users via FCM.
     *
     * @param array $userIds
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public static function sendToUsers($userIds, $title, $body, $data = [])
    {
        try {
            // Get all active device tokens for the specified users
            $tokens = DeviceToken::whereIn('user_id', (array) $userIds)
                ->where('is_active', true)
                ->pluck('fcm_token')
                ->toArray();

            if (empty($tokens)) {
                return false;
            }

            return self::sendNotification($tokens, $title, $body, $data);
        } catch (\Exception $e) {
            Log::error('Error sending push notification to users', [
                'error' => $e->getMessage(),
                'user_ids' => $userIds,
            ]);
            return false;
        }
    }

    /**
     * Send a push notification to specific device tokens.
     *
     * @param array|string $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public static function sendNotification($tokens, $title, $body, $data = [])
    {
        try {
            $tokens = is_array($tokens) ? $tokens : [$tokens];

            // Get FCM v1 API URL
            $projectId = config('services.firebase.project_id');
            $fcmUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
            
            // Get access token
            $accessToken = self::getAccessToken();
            
            if (!$accessToken) {
                Log::error('Failed to get Firebase access token');
                return false;
            }

            // Send notifications in batches of 500
            foreach (array_chunk($tokens, 500) as $tokenBatch) {
                self::sendBatch($fcmUrl, $tokenBatch, $title, $body, $data, $accessToken);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending push notification', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send a batch of notifications using FCM v1 API.
     */
    private static function sendBatch($url, $tokens, $title, $body, $data, $accessToken)
    {
        try {
            foreach ($tokens as $token) {
                $message = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_merge($data, [
                            'timestamp' => now()->toIso8601String(),
                        ]),
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'sound' => 'default',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                    ],
                ];

                $response = Http::withToken($accessToken)
                    ->timeout(10)
                    ->post($url, $message);

                if (!$response->successful()) {
                    Log::warning('FCM send failed', [
                        'status' => $response->status(),
                        'response' => $response->json(),
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending FCM batch', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get access token for Firebase Service Account.
     */
    private static function getAccessToken()
    {
        // Check if we have a cached token that's still valid
        if (self::$accessToken && self::$tokenExpire && now()->timestamp < self::$tokenExpire) {
            return self::$accessToken;
        }

        try {
            $keyPath = config('services.firebase.service_account_key_path');
            
            // Try to get service account from file first
            if (!file_exists($keyPath)) {
                // Fallback: Try to decode from base64 environment variable
                $keyBase64 = env('FIREBASE_SERVICE_ACCOUNT_KEY_B64');
                if ($keyBase64) {
                    $keyContent = base64_decode($keyBase64, true);
                    if ($keyContent) {
                        $serviceAccount = json_decode($keyContent, true);
                        if ($serviceAccount) {
                            return self::getAccessTokenFromArray($serviceAccount);
                        }
                    }
                }
                
                Log::error('Firebase service account key not found', ['path' => $keyPath]);
                return null;
            }

            $serviceAccount = json_decode(file_get_contents($keyPath), true);
            
            if (!$serviceAccount) {
                Log::error('Failed to parse Firebase service account key');
                return null;
            }

            return self::getAccessTokenFromArray($serviceAccount);
        } catch (\Exception $e) {
            Log::error('Error getting Firebase access token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get access token from service account array.
     */
    private static function getAccessTokenFromArray($serviceAccount)
    {
        try {
            // Create JWT token
            $jwt = self::createJWT($serviceAccount);

            // Exchange JWT for access token
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

            if (!$response->successful()) {
                Log::error('Failed to get Firebase access token', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return null;
            }

            $data = $response->json();
            
            // Cache token (expires in ~3600 seconds, cache for 3400)
            self::$accessToken = $data['access_token'];
            self::$tokenExpire = now()->timestamp + 3400;

            return self::$accessToken;
        } catch (\Exception $e) {
            Log::error('Error exchanging JWT for access token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create JWT token for Firebase Service Account.
     */
    private static function createJWT($serviceAccount)
    {
        $now = time();
        $expire = $now + 3600;

        $payload = [
            'iss' => $serviceAccount['client_email'],
            'sub' => $serviceAccount['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $expire,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        // Encode header and payload
        $base64Header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64Payload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signatureInput = "{$base64Header}.{$base64Payload}";

        // Sign with private key
        $privateKey = $serviceAccount['private_key'];
        openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "{$signatureInput}.{$base64Signature}";
    }

    /**
     * Send notification for a new announcement.
     */
    public static function sendAnnouncementNotification($announcement)
    {
        try {
            $title = 'New Announcement';
            $body = $announcement->title;
            
            $data = [
                'type' => 'announcement',
                'id' => $announcement->id,
                'author' => $announcement->author->name ?? 'SSC',
            ];

            // Send to all students
            $studentIds = \App\Models\User::where('role', 'student')->pluck('id')->toArray();
            
            return self::sendToUsers($studentIds, $title, $body, $data);
        } catch (\Exception $e) {
            Log::error('Error sending announcement notification', [
                'error' => $e->getMessage(),
                'announcement_id' => $announcement->id ?? null,
            ]);
            return false;
        }
    }

    /**
     * Send notification for enrollment payment status.
     */
    public static function sendEnrollmentNotification($userId, $title, $body, $data = [])
    {
        try {
            $data = array_merge($data, [
                'type' => 'enrollment',
            ]);

            return self::sendToUsers([$userId], $title, $body, $data);
        } catch (\Exception $e) {
            Log::error('Error sending enrollment notification', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return false;
        }
    }

    /**
     * Test FCM connection by sending a test notification to a topic.
     */
    public static function testConnection()
    {
        try {
            $projectId = config('services.firebase.project_id');
            $keyPath = config('services.firebase.service_account_key_path');

            if (!$keyPath || !file_exists($keyPath)) {
                return [
                    'success' => false,
                    'message' => 'Firebase service account key not found',
                ];
            }

            $accessToken = self::getAccessToken();
            
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to get Firebase access token',
                ];
            }

            $fcmUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $message = [
                'message' => [
                    'topic' => 'test',
                    'notification' => [
                        'title' => 'Test Notification',
                        'body' => 'Firebase FCM Connection Test',
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($fcmUrl, $message);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $response->successful() ? 'FCM Connection Successful' : 'FCM Connection Failed',
                'response' => $response->successful() ? null : $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error testing FCM connection: ' . $e->getMessage(),
            ];
        }
    }
}
