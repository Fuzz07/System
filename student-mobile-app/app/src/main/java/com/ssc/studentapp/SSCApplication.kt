package com.ssc.studentapp

import android.app.Application

class SSCApplication : Application() {

    override fun onCreate() {
        super.onCreate()
        // Runs whenever the process starts, including when FCM wakes a closed
        // app to deliver a push, so the channel exists before any notification
        // needs it rather than only after the student has opened a screen.
        NotificationChannels.ensure(this)
    }
}
