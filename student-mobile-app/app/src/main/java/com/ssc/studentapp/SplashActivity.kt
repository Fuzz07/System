package com.ssc.studentapp

import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity

class SplashActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash)

        // Report the real build rather than a hardcoded label that drifts.
        findViewById<TextView>(R.id.splash_version)?.text = "v${BuildConfig.VERSION_NAME}"

        // Tapping a push opens this launcher screen with the page to show in
        // the "url" extra, whether the app drew the notification or FCM did.
        val notificationUrl = intent.getStringExtra(MainActivity.EXTRA_URL)

        // Delay for 2 seconds and then launch MainActivity
        Handler(Looper.getMainLooper()).postDelayed({
            startActivity(Intent(this, MainActivity::class.java).apply {
                // Reuse the running MainActivity (it gets onNewIntent) rather
                // than stacking a second copy with its own login session.
                addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
                putExtra(MainActivity.EXTRA_URL, notificationUrl)
            })
            finish()
        }, 2000)
    }
}
