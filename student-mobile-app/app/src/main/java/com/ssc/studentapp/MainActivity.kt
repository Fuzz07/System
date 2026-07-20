package com.ssc.studentapp

import android.annotation.SuppressLint
import android.content.Context
import android.content.Intent
import android.graphics.Bitmap
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import android.net.Uri
import android.os.Bundle
import android.view.KeyEvent
import android.view.View
import android.os.Handler
import android.os.Looper
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import androidx.core.app.NotificationCompat
import android.webkit.CookieManager
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.ProgressBar
import android.widget.Toast
import androidx.activity.result.ActivityResultLauncher
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout

class MainActivity : AppCompatActivity() {

    private val NOTIF_CHANNEL_ID = "ssc_notifications"
    private val NOTIF_ID = 1001
    private val POLL_INTERVAL_MS = 30000L
    private var lastUnreadCount = 0
    private lateinit var pollHandler: Handler
    private val pollingRunnable = object : Runnable {
        override fun run() {
            fetchUnreadNotifications()
            pollHandler.postDelayed(this, POLL_INTERVAL_MS)
        }
    }

    private lateinit var webView: WebView
    private lateinit var swipeRefresh: SwipeRefreshLayout
    private lateinit var progressBar: ProgressBar
    private lateinit var enrollmentFab: com.google.android.material.floatingactionbutton.FloatingActionButton
    private var filePathCallback: ValueCallback<Array<Uri>>? = null
    private lateinit var fileChooserLauncher: ActivityResultLauncher<Intent>

    // Default portal URL. For Android emulators, debug uses local host mapping while release uses the production endpoint.
    private val portalUrl = BuildConfig.PORTAL_URL
    private val enrollmentUrl = portalUrl.replace("/login/student", "") + "/m/student/enrollment"

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        webView = findViewById(R.id.webView)
        swipeRefresh = findViewById(R.id.swipeRefresh)
        progressBar = findViewById(R.id.progressBar)
        enrollmentFab = findViewById(R.id.enrollmentFab)

        // Setup Swipe to Refresh
        swipeRefresh.setOnRefreshListener {
            if (isNetworkAvailable()) {
                webView.reload()
            } else {
                swipeRefresh.isRefreshing = false
                startActivity(Intent(this, OfflineActivity::class.java))
            }
        }
        swipeRefresh.setColorSchemeResources(R.color.indigo_600)

        // Setup WebView Settings
        val settings = webView.settings
        settings.javaScriptEnabled = true
        settings.domStorageEnabled = true
        settings.databaseEnabled = true
        settings.useWideViewPort = true
        settings.loadWithOverviewMode = true
        settings.allowFileAccess = true
        settings.javaScriptCanOpenWindowsAutomatically = true
        
        // Append Custom User-Agent to allow backend sniffing if needed
        val defaultUserAgent = settings.userAgentString
        settings.userAgentString = "$defaultUserAgent SSCStudentApp/1.0"

        // Set Cache Mode
        settings.cacheMode = WebSettings.LOAD_DEFAULT

        // Set Cookie Policy
        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        cookieManager.setAcceptThirdPartyCookies(webView, true)

        // Setup WebViewClient
        webView.webViewClient = object : WebViewClient() {
            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                super.onPageStarted(view, url, favicon)
                progressBar.visibility = View.VISIBLE
                progressBar.progress = 10
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                progressBar.visibility = View.GONE
                swipeRefresh.isRefreshing = false
            }

            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url?.toString() ?: return false

                // Allow navigation within the app's own hosts (local dev + production Vercel)
                val appHost = BuildConfig.APP_HOST
                if (url.contains("10.0.2.2") ||
                    url.contains("localhost") ||
                    url.contains("mcclawis.edu.ph") ||
                    url.contains("vercel.app") ||
                    url.contains(appHost)) {
                    return false  // Let the WebView handle it internally
                }

                // Handle external links (tel, mailto, maps) outside WebView
                return try {
                    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                    startActivity(intent)
                    true
                } catch (e: Exception) {
                    Toast.makeText(this@MainActivity, "No application can handle this action", Toast.LENGTH_SHORT).show()
                    true
                }
            }

            override fun onReceivedError(view: WebView?, request: WebResourceRequest?, error: WebResourceError?) {
                super.onReceivedError(view, request, error)
                // Only show offline page if the MAIN frame fails (not subresources like images/css)
                // and the error is a genuine connection failure (not a HTTP error like 404)
                if (request?.isForMainFrame == true) {
                    val errorCode = error?.errorCode ?: -1
                    // Only treat network-level errors as offline (not HTTP errors)
                    val networkErrors = setOf(
                        -2,   // ERROR_HOST_LOOKUP (DNS failure)
                        -6,   // ERROR_CONNECT (connection refused)
                        -8,   // ERROR_TIMEOUT
                        -15,  // ERROR_UNKNOWN
                        -10,  // ERROR_IO
                        -12   // ERROR_REDIRECT_LOOP
                    )
                    if (errorCode in networkErrors) {
                        swipeRefresh.isRefreshing = false
                        startActivity(Intent(this@MainActivity, OfflineActivity::class.java))
                    }
                }
            }
        }

        // Setup file chooser launcher
        fileChooserLauncher = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result ->
            if (result.resultCode == RESULT_OK) {
                filePathCallback?.onReceiveValue(WebChromeClient.FileChooserParams.parseResult(result.resultCode, result.data))
            } else {
                filePathCallback?.onReceiveValue(null)
            }
            filePathCallback = null
        }

        // Setup WebChromeClient
        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                super.onProgressChanged(view, newProgress)
                progressBar.progress = newProgress
                if (newProgress >= 100) {
                    progressBar.visibility = View.GONE
                }
            }

            // Handle file uploads (e.g. for student feedback / candidacy documents)
            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                this@MainActivity.filePathCallback?.onReceiveValue(null)
                this@MainActivity.filePathCallback = filePathCallback

                val intent = fileChooserParams?.createIntent()
                return try {
                    fileChooserLauncher.launch(intent)
                    true
                } catch (e: Exception) {
                    this@MainActivity.filePathCallback = null
                    Toast.makeText(this@MainActivity, "Cannot open file chooser", Toast.LENGTH_LONG).show()
                    false
                }
            }
        }

        enrollmentFab.setOnClickListener {
            if (isNetworkAvailable()) {
                webView.loadUrl(enrollmentUrl)
            } else {
                Toast.makeText(this, "No network available to open enrollment", Toast.LENGTH_SHORT).show()
            }
        }

        // Initial Load
        if (isNetworkAvailable()) {
            webView.loadUrl(portalUrl)
            // Start polling student notifications for native alerts
            pollHandler = Handler(Looper.getMainLooper())
            createNotificationChannel()
            pollHandler.postDelayed(pollingRunnable, 5000)
        } else {
            startActivity(Intent(this, OfflineActivity::class.java))
        }
    }

    private fun createNotificationChannel() {
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            val name = "SSC Notifications"
            val descriptionText = "Notifications from SSC system"
            val importance = NotificationManager.IMPORTANCE_DEFAULT
            val channel = NotificationChannel(NOTIF_CHANNEL_ID, name, importance).apply {
                description = descriptionText
            }
            val notificationManager: NotificationManager = getSystemService(NotificationManager::class.java)
            notificationManager.createNotificationChannel(channel)
        }
    }

    private fun fetchUnreadNotifications() {
        Thread {
            try {
                val url = java.net.URL("$portalUrl/student/notifications/unread-count")
                val conn = url.openConnection() as java.net.HttpURLConnection
                conn.requestMethod = "GET"
                conn.connectTimeout = 8000
                conn.readTimeout = 8000
                // Forward cookies from WebView session so the API call is authenticated
                val cookie = CookieManager.getInstance().getCookie(portalUrl)
                if (!cookie.isNullOrEmpty()) {
                    conn.setRequestProperty("Cookie", cookie)
                }
                val code = conn.responseCode
                if (code == 200) {
                    val stream = conn.inputStream.bufferedReader().use { it.readText() }
                    val obj = org.json.JSONObject(stream)
                    val unread = obj.optInt("unread", 0)
                    if (unread > 0 && unread != lastUnreadCount) {
                        lastUnreadCount = unread
                        showNotification("You have $unread new notification(s) from SSC.")
                    }
                }
                conn.disconnect()
            } catch (e: Exception) {
                // ignore network errors
            }
        }.start()
    }

    private fun showNotification(message: String) {
        val intent = Intent(this, MainActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_SINGLE_TOP
        val pendingIntent = PendingIntent.getActivity(this, 0, intent, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
        val builder = NotificationCompat.Builder(this, NOTIF_CHANNEL_ID)
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setContentTitle("SSC Notification")
            .setContentText(message)
            .setPriority(NotificationCompat.PRIORITY_DEFAULT)
            .setContentIntent(pendingIntent)
            .setAutoCancel(true)

        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        notificationManager.notify(NOTIF_ID, builder.build())
    }

    override fun onResume() {
        super.onResume()
        // If coming back from OfflineActivity, reload the WebView
        if (webView.url == null) {
            if (isNetworkAvailable()) {
                webView.loadUrl(portalUrl)
            }
        }
    }

    // Handle Back Press to navigate WebView history rather than closing the activity
    override fun onKeyDown(keyCode: Int, event: KeyEvent?): Boolean {
        if (keyCode == KeyEvent.KEYCODE_BACK && webView.canGoBack()) {
            webView.goBack()
            return true
        }
        return super.onKeyDown(keyCode, event)
    }


    private fun isNetworkAvailable(): Boolean {
        val connectivityManager = getSystemService(Context.CONNECTIVITY_SERVICE) as ConnectivityManager
        val network = connectivityManager.activeNetwork ?: return false
        val activeNetwork = connectivityManager.getNetworkCapabilities(network) ?: return false
        return when {
            activeNetwork.hasTransport(NetworkCapabilities.TRANSPORT_WIFI) -> true
            activeNetwork.hasTransport(NetworkCapabilities.TRANSPORT_CELLULAR) -> true
            activeNetwork.hasTransport(NetworkCapabilities.TRANSPORT_ETHERNET) -> true
            else -> false
        }
    }
}
