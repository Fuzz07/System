# Firebase Push Notifications - Production Deployment Guide

This guide covers how to deploy Firebase push notifications to production on Vercel (backend) + Railway (database).

## Overview

The push notification system uses Firebase Cloud Messaging (FCM) with service account authentication. The service account key is deployed as a base64-encoded environment variable to avoid committing sensitive credentials to git.

## Local Development Setup

1. **Firebase Service Account Key**
   - Place `firebase-key.json` in `storage/firebase-key.json`
   - The PushNotificationService will read from this file first
   - Never commit this file (it's in .gitignore)

2. **.env Configuration**
   ```
   FIREBASE_PROJECT_ID=ssc-student-app
   FIREBASE_SERVICE_ACCOUNT_KEY_PATH=storage/firebase-key.json
   ```

3. **Testing Locally**
   ```bash
   php artisan tinker
   ```
   Then run:
   ```php
   \App\Services\PushNotificationService::testConnection();
   ```

## Production Deployment (Vercel + Railway)

### Step 1: Generate Base64-Encoded Firebase Key

```powershell
# Windows PowerShell
certutil -encode storage/firebase-key.json firebase-key-base64.txt
type firebase-key-base64.txt
```

Or on Linux/macOS:
```bash
base64 storage/firebase-key.json | tr -d '\n' > firebase-key-base64.txt
cat firebase-key-base64.txt
```

**Important:** The certutil output includes `-----BEGIN CERTIFICATE-----` and `-----END CERTIFICATE-----` headers. Remove these headers before using the value in environment variables. Keep only the base64 content between them.

### Step 2: Set Environment Variables in Vercel

1. Go to your Vercel project dashboard
2. Navigate to Settings → Environment Variables
3. Add the following variables:

   ```
   FIREBASE_PROJECT_ID=ssc-student-app
   FIREBASE_SERVICE_ACCOUNT_KEY_PATH=storage/firebase-key.json
   FIREBASE_SERVICE_ACCOUNT_KEY_B64=<your-base64-encoded-key>
   ```

   Replace `<your-base64-encoded-key>` with the base64 content (without the certificate headers).

4. Make sure these variables are set for all environments (Production, Preview, Development)

### Step 3: Set Environment Variables in Railway

If using Railway's environment variable system:

1. Go to your Railway project
2. Add the same environment variables as shown above
3. Redeploy the application

### Step 4: Push Code Changes

```bash
git add .
git commit -m "feat: add Firebase key base64 support for Vercel/Railway deployment

- Update PushNotificationService to handle FIREBASE_SERVICE_ACCOUNT_KEY_B64 env var
- Add setupFirebaseKey() hook to AppServiceProvider for runtime key file creation
- Fallback to file-based key for local development
- Supports both file-based (dev) and base64 env variable (production) deployment"

git push origin main
```

### Step 5: Deploy to Vercel

After pushing, Vercel should automatically deploy. You can also:

```bash
vercel deploy --prod
```

## How It Works

### Local Development
1. PushNotificationService::getAccessToken() checks for `storage/firebase-key.json`
2. If file exists, reads and uses it directly
3. File path is configured in `config/services.firebase`

### Production (Vercel/Railway)
1. Environment variable `FIREBASE_SERVICE_ACCOUNT_KEY_B64` contains the base64-encoded key
2. On application boot, AppServiceProvider::setupFirebaseKey() runs:
   - Decodes the base64 environment variable
   - Creates `storage/firebase-key.json` at runtime
   - File is created only once and reused
3. PushNotificationService uses the file as usual

### Fallback Mechanism
If `storage/firebase-key.json` doesn't exist:
1. PushNotificationService checks for `FIREBASE_SERVICE_ACCOUNT_KEY_B64`
2. Decodes and uses it directly
3. This ensures notifications work even if file creation fails

## Verifying Setup

### On Vercel (via Logs)

```bash
# View Vercel logs
vercel logs your-domain.vercel.app

# Look for:
# "Firebase service account key successfully created from environment variable"
```

### Test Notifications

Create an announcement in the student portal to trigger a test notification:

```bash
php artisan tinker
```

Then:
```php
// Test FCM connection
\App\Services\PushNotificationService::testConnection();

// Send test notification to specific users
\App\Services\PushNotificationService::sendToUsers([1, 2, 3], 'Test', 'This is a test notification', []);
```

## Troubleshooting

### Notifications Not Sending

1. **Check Firebase Credentials**
   - Verify `FIREBASE_PROJECT_ID` matches your Firebase project
   - Verify `FIREBASE_SERVICE_ACCOUNT_KEY_B64` is valid base64 (decode to JSON)

2. **Check Logs**
   - Railway/Vercel logs for errors in PushNotificationService
   - Look for: "Error getting Firebase access token"

3. **Verify Device Tokens**
   - Check `device_tokens` table: `SELECT * FROM device_tokens WHERE user_id = 1;`
   - Ensure tokens exist and `is_active = 1`

4. **Test FCM Connection**
   - Use `PushNotificationService::testConnection()` in tinker
   - Should return success message if configured correctly

### Key File Not Created

If `storage/firebase-key.json` not created:
1. Check if `storage` directory is writable
2. Check Vercel/Railway filesystem permissions
3. Fall back to direct base64 decoding in PushNotificationService

### Base64 Decoding Fails

- Ensure `FIREBASE_SERVICE_ACCOUNT_KEY_B64` doesn't have certificate headers
- Test decoding: `base64_decode($value, true)` should return valid JSON
- Use online tools to validate base64 encoding if unsure

## Security Notes

- 🔒 Never commit `storage/firebase-key.json` (it's in .gitignore)
- 🔒 Never commit `firebase-key-base64.txt` (it's in .gitignore)
- 🔒 Treat `FIREBASE_SERVICE_ACCOUNT_KEY_B64` as a secret
- 🔒 Use Vercel/Railway's encrypted secret management, not plain text
- 🔒 Rotate Firebase keys periodically
- 🔒 File permissions set to 0600 (read/write only by owner)

## File Permissions on Vercel

Vercel's filesystem is ephemeral (resets on redeploy). Each deployment:
1. AppServiceProvider::setupFirebaseKey() recreates the key file
2. File exists for the lifetime of that deployment
3. No need to manually set up permissions per deployment

## Additional Resources

- [Firebase Cloud Messaging Docs](https://firebase.google.com/docs/cloud-messaging)
- [Firebase Admin SDK for PHP](https://github.com/kreait/firebase-php)
- [Vercel Environment Variables](https://vercel.com/docs/projects/environment-variables)
- [Railway Environment Variables](https://docs.railway.app/reference/variables)

## Quick Reference: Environment Variables

| Variable | Dev | Prod | Source |
|----------|-----|------|--------|
| FIREBASE_PROJECT_ID | ✓ | ✓ | Firebase Console |
| FIREBASE_SERVICE_ACCOUNT_KEY_PATH | ✓ | ✓ | storage/firebase-key.json |
| FIREBASE_SERVICE_ACCOUNT_KEY_B64 | ✗ | ✓ | Generated from key file |

## Next Steps

1. ✅ Convert firebase-key.json to base64
2. ✅ Update PushNotificationService to handle env variable
3. ✅ Add AppServiceProvider hook
4. ✅ Push code changes
5. → Set `FIREBASE_SERVICE_ACCOUNT_KEY_B64` in Vercel/Railway
6. → Redeploy application
7. → Test notifications from student portal
