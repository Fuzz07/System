package com.ssc.studentapp

import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.Context
import android.media.AudioAttributes
import android.media.RingtoneManager
import android.os.Build

object NotificationChannels {

    /**
     * The one channel every SSC push goes to. The server names it in each FCM
     * message (PushNotificationService) and the manifest makes it the default.
     *
     * It replaces "ssc_notifications", which MainActivity used to create at
     * IMPORTANCE_DEFAULT before the messaging service could create it at HIGH.
     * Android never lets an app raise an existing channel's importance, so on
     * those installs pushes arrived silently in the tray with no pop-up. A new
     * ID is the only way to get HIGH on phones that already have the old one.
     */
    const val ALERTS = "ssc_alerts"
    private const val LEGACY = "ssc_notifications"

    /** Safe to call repeatedly; creating an existing channel is a no-op. */
    fun ensure(context: Context) {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.O) return

        val manager = context.getSystemService(NotificationManager::class.java)
        val channel = NotificationChannel(
            ALERTS,
            "SSC Notifications",
            NotificationManager.IMPORTANCE_HIGH
        ).apply {
            description = "Announcements and important updates from SSC"
            enableVibration(true)
            lockscreenVisibility = android.app.Notification.VISIBILITY_PUBLIC
            setSound(
                RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION),
                AudioAttributes.Builder()
                    .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                    .build()
            )
        }
        manager.createNotificationChannel(channel)
        manager.deleteNotificationChannel(LEGACY)
    }
}
