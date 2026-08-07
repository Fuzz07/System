<?php

require 'C:/laragon/www/SSC_BDGT/vendor/autoload.php';
$app = require_once 'C:/laragon/www/SSC_BDGT/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\DeviceToken;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Auth;

echo "=== 1. CHECK USERS & DEVICE TOKENS ===\n";
$user = User::where('role', 'student')->first();
if (!$user) {
    echo "No student user found!\n";
    exit;
}
echo "Found student user ID: {$user->id} ({$user->email})\n";

$tokens = DeviceToken::all();
echo "Total device tokens in DB: " . $tokens->count() . "\n";
foreach ($tokens as $t) {
    echo "  - Token ID: {$t->id}, User ID: {$t->user_id}, FCM: " . substr($t->fcm_token, 0, 20) . "..., Active: {$t->is_active}\n";
}

echo "\n=== 2. TEST REGISTERING DEVICE TOKEN ===\n";
Auth::login($user);
$controller = new \App\Http\Controllers\DeviceTokenController();
$request = \Illuminate\Http\Request::create('/student/api/device-token', 'POST', [
    'fcm_token' => 'fcm_test_dummy_token_99999',
    'device_type' => 'android',
    'device_name' => 'Pixel 7',
]);
$response = $controller->store($request);
echo "Store response: " . $response->getContent() . "\n";

echo "\n=== 3. TEST FIREBASE ACCESS TOKEN GENERATION ===\n";
$reflection = new ReflectionClass(PushNotificationService::class);
$method = $reflection->getMethod('getAccessToken');
$method->setAccessible(true);
$accessToken = $method->invoke(null);

if ($accessToken) {
    echo "SUCCESS: Obtained FCM Access Token!\nToken starts with: " . substr($accessToken, 0, 25) . "...\n";
} else {
    echo "FAILED: Could not obtain FCM Access Token!\n";
}

echo "\n=== 4. TEST SENDING PUSH NOTIFICATION ===\n";
$sendResult = PushNotificationService::sendToUsers([$user->id], 'Test Title', 'Test Body Message');
echo "sendToUsers result: " . ($sendResult ? "TRUE (Sent)" : "FALSE (Failed)") . "\n";
