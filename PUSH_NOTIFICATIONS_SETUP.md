# Push Notification Setup Guide

This guide explains how to set up Firebase Cloud Messaging (FCM) for the SSC Student App push notifications.

## What's been implemented

✅ Mobile App (Android):
- Firebase Cloud Messaging SDK integrated
- FCM Service (SSCMessagingService.kt) to handle incoming messages
- Automatic device token retrieval and registration
- Notification permission handling for Android 13+
- Notifications display even when app is closed

✅ Backend (Laravel):
- Device Token model and database table for storing FCM tokens
- DeviceTokenController API endpoints for token management
- PushNotificationService for sending FCM messages
- Automatic push notifications sent when announcements are created

## Frontend Configuration Required

### Step 1: Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Create a new project" or select an existing project
3. Give it a name (e.g., "SSC-Student-App")
4. Enable Google Analytics if desired
5. Create the project

### Step 2: Register Android App

1. In Firebase Console, click the Android icon to register your app
2. Enter:
   - **Package name**: `com.ssc.studentapp`
   - **App nickname**: `SSC Student App` (optional)
   - **SHA-1 certificate fingerprint**: (See Step 3)
3. Click "Register app"

### Step 3: Get SHA-1 Certificate Fingerprint

Windows (PowerShell):
```powershell
# Navigate to your project directory
cd "c:\laragon\www\SSC_BDGT\student-mobile-app"

# Run gradle task to get SHA-1 (if signed key exists)
.\gradlew.bat signingReport

# Or generate debug key SHA-1
$ANDROID_SDK_ROOT = "$env:USERPROFILE\AppData\Local\Android\Sdk"
$keytool = "$ANDROID_SDK_ROOT\platform-tools\keytool.exe"
& $keytool -list -v -keystore "$env:USERPROFILE\.android\debug.keystore" -alias androiddebugkey -storepass android -keypass android
```

Look for the line: `SHA1: XX:XX:XX:...` and copy that fingerprint.

### Step 4: Download google-services.json

1. After registering your app, Firebase will generate `google-services.json`
2. Download the file
3. Place it in: `student-mobile-app/app/google-services.json`

This file should NOT be committed to version control - add to `.gitignore`:
```
student-mobile-app/app/google-services.json
```

### Step 5: Enable Firebase Cloud Messaging API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project: **ssc-student-app**
3. Search for "Cloud Messaging API"
4. Click it and press **Enable**

This ensures the API is active for your service account to send messages.

## Backend Configuration Required

### Step 1: Add Firebase Configuration to Laravel

Add to your `.env` file:
```env
FIREBASE_PROJECT_ID=ssc-student-app
FIREBASE_SERVICE_ACCOUNT_KEY_PATH=storage/firebase-key.json
```

### Step 2: Place Service Account Key

The `firebase-key.json` file (service account credentials) should already be in:
```
storage/firebase-key.json
```

This file should NOT be committed to version control - add to `.gitignore`:
```
storage/firebase-key.json
!storage/.gitkeep
```

### Step 3: Configure Firebase in config/services.php

This is already configured. Verify it has:
```php
'firebase' => [
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'service_account_key_path' => env('FIREBASE_SERVICE_ACCOUNT_KEY_PATH', 'storage/firebase-key.json'),
],
```

### Step 4: Run Database Migration

```bash
# From project root
php artisan migrate
```

This will create the `device_tokens` table.

## Testing

### Test Mobile App FCM Token Registration

1. Install and run the app on an Android device/emulator
2. Log in with a student account
3. The app will:
   - Request notification permission (Android 13+)
   - Retrieve FCM token from Firebase
   - Automatically send token to backend via `/student/api/device-token` endpoint

Check database:
```bash
php artisan tinker
>>> DB::table('device_tokens')->where('user_id', 1)->get();
```

### Test Push Notification

Create an announcement from the portal:
1. Log in as Officer/Admin
2. Create a new announcement
3. All students with the app installed should receive a push notification
4. Notification will appear even if the app is closed

Or use Artisan command:
```bash
php artisan tinker
>>> App\Services\PushNotificationService::testConnection();
```

## Notification Events

The system automatically sends push notifications for:

1. **New Announcements** - Sent to all students when an announcement is created
2. **Enrollment Payment Updates** - Can be triggered manually (see extending below)

## Extending: Add More Notification Types

### Send Notification for Enrollment Payment Approval

In [app/Models/EnrollmentPayment.php](../app/Models/EnrollmentPayment.php):

```php
protected static function booted()
{
    static::updated(function ($payment) {
        if ($payment->status === 'paid' && $payment->wasChanged('status')) {
            \App\Services\PushNotificationService::sendEnrollmentNotification(
                $payment->user_id,
                'Enrollment Fee Approved',
                'Your enrollment payment has been confirmed.',
                ['enrollment_id' => $payment->id]
            );
        }
    });
}
```

### Send Custom Notification

```php
use App\Services\PushNotificationService;

// Send to specific users
PushNotificationService::sendToUsers(
    [1, 2, 3], // user IDs
    'Meeting Reminder',
    'Officer meeting starts in 30 minutes',
    ['meeting_id' => 123]
);

// Send to specific tokens
PushNotificationService::sendNotification(
    ['token1', 'token2'], // FCM tokens
    'Title',
    'Message body',
    ['custom_field' => 'value']
);
```

## Troubleshooting

### Notifications Not Appearing

1. **Check device token is registered**:
   ```bash
   php artisan tinker
   >>> DB::table('device_tokens')->where('user_id', 1)->where('is_active', true)->get();
   ```

2. **Verify Firebase configuration is correct**:
   ```bash
   php artisan tinker
   >>> config('services.firebase.project_id')
   >>> file_exists(config('services.firebase.service_account_key_path'))
   ```

3. **Test FCM connection**:
   ```bash
   php artisan tinker
   >>> App\Services\PushNotificationService::testConnection();
   ```

4. **Check Laravel logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Verify Firebase project settings**:
   - Ensure Cloud Messaging API is enabled in Google Cloud Console
   - Check service account has correct permissions
   - Verify SHA-1 fingerprint matches your app

### App Crashes on Startup

1. Ensure `google-services.json` is in `student-mobile-app/app/`
2. Check for build errors in Android Studio
3. Verify Firebase dependencies are correctly added to `build.gradle.kts`

### Device Token Not Sending

1. Ensure user is authenticated
2. Check network connectivity
3. Verify user session cookies are being sent correctly
4. Check PHP logs for API errors

## Security Notes

⚠️ **Never commit these files**:
- `student-mobile-app/app/google-services.json`
- `storage/firebase-key.json`
- `.env` file

✅ **Keep these secret**:
- Firebase Service Account Private Key (in firebase-key.json)
- Any Firebase API keys

## File Changes Summary

### Mobile App Files:
- `student-mobile-app/build.gradle.kts` - Added Firebase dependencies
- `student-mobile-app/app/build.gradle.kts` - Added Google Services plugin
- `student-mobile-app/app/src/main/AndroidManifest.xml` - Added FCM service & permissions
- `student-mobile-app/app/src/main/java/com/ssc/studentapp/MainActivity.kt` - Added FCM initialization
- `student-mobile-app/app/src/main/java/com/ssc/studentapp/SSCMessagingService.kt` - **NEW** FCM message handler

### Backend Files:
- `database/migrations/2026_07_20_000000_create_device_tokens_table.php` - **NEW** Device tokens table
- `app/Models/DeviceToken.php` - **NEW** Device token model
- `app/Models/Announcement.php` - Updated to send notifications
- `app/Http/Controllers/DeviceTokenController.php` - **NEW** Token management API
- `app/Services/PushNotificationService.php` - **NEW** FCM service
- `routes/web.php` - Added API routes for device tokens
- `config/services.php` - **NEW** Firebase configuration (add manually)

## Next Steps

1. Create Firebase project and get credentials
2. Place `google-services.json` in mobile app folder
3. Configure backend `.env` with Firebase keys
4. Run database migrations
5. Install and test the app
6. Create announcements and verify notifications appear

## Notification Lifecycle

```
Announcement Created
    ↓
Announcement::created() event fires
    ↓
PushNotificationService::sendAnnouncementNotification() called
    ↓
Get all active student device tokens from database
    ↓
Send FCM HTTP request to each token
    ↓
SSCMessagingService receives message on device
    ↓
Notification appears in system tray (even if app is closed)
    ↓
User taps notification → opens MainActivity
```

## Support

For Firebase issues:
- [Firebase Documentation](https://firebase.google.com/docs)
- [FCM Documentation](https://firebase.google.com/docs/cloud-messaging)
- [Android FCM Integration](https://firebase.google.com/docs/cloud-messaging/android/client)
