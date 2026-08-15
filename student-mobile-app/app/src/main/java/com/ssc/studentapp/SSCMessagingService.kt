package com.ssc.studentapp

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.media.AudioAttributes
import android.media.RingtoneManager
import android.os.Build
import androidx.core.app.NotificationCompat
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import java.net.HttpURLConnection
import java.net.URL
import kotlin.concurrent.thread

class SSCMessagingService : FirebaseMessagingService() {

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        // Handle notification when the app is in foreground
        remoteMessage.notification?.let {
            val imageUrl = it.imageUrl?.toString() ?: remoteMessage.data["image_url"]
            sendNotification(it.title ?: "SSC Announcement", it.body ?: "", remoteMessage.data, imageUrl)
        }

        // Handle data payload
        if (remoteMessage.data.isNotEmpty()) {
            handleDataMessage(remoteMessage.data)
        }
    }

    override fun onNewToken(token: String) {
        // Send the FCM token to your backend when a new token is generated
        sendTokenToBackend(token)
    }

    private fun sendNotification(title: String, messageBody: String, data: Map<String, String>, imageUrl: String? = null) {
        val intent = Intent(this, SplashActivity::class.java).apply {
            addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
            putExtra("notification_type", data["type"])
            putExtra("notification_id", data["id"])
        }

        val pendingIntent: PendingIntent = PendingIntent.getActivity(
            this, System.currentTimeMillis().toInt(), intent,
            PendingIntent.FLAG_IMMUTABLE or PendingIntent.FLAG_UPDATE_CURRENT
        )

        val defaultSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION)
        val notificationId  = System.currentTimeMillis().toInt()

        createNotificationChannel()
        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager

        // Fetching the image is a network call, so it can't happen on the main
        // thread that onMessageReceived runs on — build and post the
        // notification from a background thread once the bitmap (if any) is ready.
        thread {
            val bitmap = if (!imageUrl.isNullOrBlank()) fetchBitmap(imageUrl) else null

            val notificationBuilder = NotificationCompat.Builder(this, NOTIF_CHANNEL_ID)
                .setContentTitle(title)
                .setContentText(messageBody)
                .setAutoCancel(true)
                .setSound(defaultSoundUri)
                .setContentIntent(pendingIntent)
                .setPriority(NotificationCompat.PRIORITY_MAX)
                .setDefaults(NotificationCompat.DEFAULT_ALL)
                .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                .setSmallIcon(R.drawable.ic_launcher)  // Use the app's own launcher icon

            if (bitmap != null) {
                notificationBuilder
                    .setLargeIcon(bitmap)
                    .setStyle(
                        NotificationCompat.BigPictureStyle()
                            .bigPicture(bitmap)
                            .bigLargeIcon(null as Bitmap?)
                            .setBigContentTitle(title)
                            .setSummaryText(messageBody)
                    )
            } else {
                notificationBuilder.setStyle(
                    NotificationCompat.BigTextStyle()
                        .bigText(messageBody)
                        .setBigContentTitle(title)
                )
            }

            notificationManager.notify(notificationId, notificationBuilder.build())
        }
    }

    private fun fetchBitmap(imageUrl: String): Bitmap? {
        return try {
            val connection = URL(imageUrl).openConnection() as HttpURLConnection
            connection.connectTimeout = 8000
            connection.readTimeout = 8000
            connection.doInput = true
            connection.connect()
            connection.inputStream.use { BitmapFactory.decodeStream(it) }
        } catch (e: Exception) {
            android.util.Log.e("FCM_DEBUG", "Failed to fetch push notification image", e)
            null
        }
    }


    private fun handleDataMessage(data: Map<String, String>) {
        // Handle additional data payload processing if needed
        val type = data["type"]
        val id = data["id"]
        // Store in SharedPreferences or database for later processing
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                NOTIF_CHANNEL_ID,
                "SSC Notifications",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "Announcements and important updates from SSC"
                enableVibration(true)
                setSound(
                    RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION),
                    AudioAttributes.Builder()
                        .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                        .build()
                )
            }

            val manager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            manager.createNotificationChannel(channel)
        }
    }

    private fun sendTokenToBackend(token: String) {
        // This will be called to send the FCM token to the backend
        // We'll implement this in MainActivity using a proper API call
        val sharedPref = getSharedPreferences("fcm_prefs", Context.MODE_PRIVATE)
        sharedPref.edit().putString("fcm_token", token).apply()
        
        // Try to send immediately if user is logged in
        // Otherwise it will be sent during login
    }

    companion object {
        private const val NOTIF_CHANNEL_ID = "ssc_notifications"
    }
}
