package com.ssc.studentapp

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.webkit.CookieManager
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL
import kotlin.concurrent.thread

data class Announcement(
    val id: Int,
    val title: String,
    val content: String,
    val author: String,
    val date: String,
    val timeAgo: String,
    val proofUrl: String?
)

class AnnouncementsActivity : AppCompatActivity() {

    private lateinit var swipeRefresh: SwipeRefreshLayout
    private lateinit var recyclerView: RecyclerView
    private lateinit var progressBar: ProgressBar
    private lateinit var emptyText: TextView
    private lateinit var adapter: AnnouncementAdapter
    private lateinit var skeletonLayout: LinearLayout
    private var portalUrl = BuildConfig.PORTAL_URL

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_announcements)

        portalUrl = intent.getStringExtra("portal_url") ?: BuildConfig.PORTAL_URL

        val toolbar: Toolbar = findViewById(R.id.toolbar)
        toolbar.setNavigationIcon(androidx.appcompat.R.drawable.abc_ic_ab_back_material)
        toolbar.setNavigationOnClickListener { finish() }

        swipeRefresh = findViewById(R.id.swipeRefresh)
        recyclerView = findViewById(R.id.recyclerView)
        progressBar = findViewById(R.id.progressBar)
        emptyText = findViewById(R.id.emptyText)
        skeletonLayout = findViewById(R.id.skeletonLayout)

        recyclerView.layoutManager = LinearLayoutManager(this)
        adapter = AnnouncementAdapter { announcement ->
            showAnnouncementDetails(announcement)
        }
        recyclerView.adapter = adapter

        swipeRefresh.setOnRefreshListener { fetchAnnouncements() }

        skeletonLayout.visibility = View.VISIBLE
        recyclerView.visibility = View.GONE
        fetchAnnouncements()
    }

    private fun fetchAnnouncements() {
        // Retrieve cookies on the MAIN thread to guarantee thread-safe, accurate session cookies!
        val cookie = CookieManager.getInstance().getCookie(portalUrl) ?: ""

        thread {
            try {
                // Determine base URL dynamically and robustly from portal URL
                val parsedUrl = java.net.URL(portalUrl)
                val baseUrl = "${parsedUrl.protocol}://${parsedUrl.host}" + if (parsedUrl.port != -1) ":${parsedUrl.port}" else ""

                val url = URL("$baseUrl/student/api/announcements")
                val conn = url.openConnection() as HttpURLConnection
                conn.requestMethod = "GET"
                conn.connectTimeout = 8000
                conn.readTimeout = 8000
                conn.setRequestProperty("User-Agent", "Mozilla/5.0 (Linux; Android 10) SSCStudentApp/1.0")

                // Attach cookies to keep authenticated
                if (cookie.isNotEmpty()) {
                    conn.setRequestProperty("Cookie", cookie)
                }

                if (conn.responseCode == 200) {
                    val stream = conn.inputStream.bufferedReader().use { it.readText() }
                    val obj = JSONObject(stream)
                    val array = obj.optJSONArray("announcements")
                    val list = mutableListOf<Announcement>()
                    if (array != null) {
                        for (i in 0 until array.length()) {
                            val aObj = array.getJSONObject(i)
                            list.add(
                                Announcement(
                                    id = aObj.optInt("id"),
                                    title = aObj.optString("title", ""),
                                    content = aObj.optString("content", ""),
                                    author = aObj.optString("author", "SSC Admin"),
                                    date = aObj.optString("date", ""),
                                    timeAgo = aObj.optString("time_ago", ""),
                                    proofUrl = if (aObj.isNull("proof")) null else aObj.optString("proof")
                                )
                            )
                        }
                    }

                    runOnUiThread {
                        skeletonLayout.visibility = View.GONE
                        recyclerView.visibility = View.VISIBLE
                        swipeRefresh.isRefreshing = false
                        adapter.submitList(list)
                        emptyText.text = "No announcements available."
                        emptyText.visibility = if (list.isEmpty()) View.VISIBLE else View.GONE
                    }
                } else {
                    runOnUiThread {
                        skeletonLayout.visibility = View.GONE
                        recyclerView.visibility = View.VISIBLE
                        swipeRefresh.isRefreshing = false
                        emptyText.text = "Failed to load announcements (Server error: ${conn.responseCode})."
                        emptyText.visibility = View.VISIBLE
                    }
                }
                conn.disconnect()
            } catch (e: Exception) {
                e.printStackTrace()
                runOnUiThread {
                    skeletonLayout.visibility = View.GONE
                    recyclerView.visibility = View.VISIBLE
                    swipeRefresh.isRefreshing = false
                    emptyText.text = "Failed to load announcements (Connection error)."
                    emptyText.visibility = View.VISIBLE
                }
            }
        }
    }

    private fun showAnnouncementDetails(announcement: Announcement) {
        val message = StringBuilder()
        message.append(announcement.content)
        
        val builder = MaterialAlertDialogBuilder(this)
            .setTitle(announcement.title)
            .setMessage(message.toString())
            .setPositiveButton("Close", null)

        if (!announcement.proofUrl.isNullOrEmpty()) {
            builder.setNeutralButton("View Proof") { _, _ ->
                val intent = Intent(Intent.ACTION_VIEW, Uri.parse(announcement.proofUrl))
                startActivity(intent)
            }
        }

        builder.show()
    }

    inner class AnnouncementAdapter(private val onClick: (Announcement) -> Unit) :
        RecyclerView.Adapter<AnnouncementAdapter.ViewHolder>() {

        private var items = listOf<Announcement>()

        fun submitList(newItems: List<Announcement>) {
            items = newItems
            notifyDataSetChanged()
        }

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
            val view = LayoutInflater.from(parent.context)
                .inflate(R.layout.item_announcement, parent, false)
            return ViewHolder(view)
        }

        override fun onBindViewHolder(holder: ViewHolder, position: Int) {
            val item = items[position]
            holder.textTitle.text = item.title
            holder.textDate.text = "${item.date} • ${item.timeAgo}"
            holder.textContent.text = item.content
            holder.textAuthor.text = item.author

            holder.itemView.setOnClickListener { onClick(item) }
        }

        override fun getItemCount() = items.size

        inner class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val textTitle: TextView = view.findViewById(R.id.textTitle)
            val textDate: TextView = view.findViewById(R.id.textDate)
            val textContent: TextView = view.findViewById(R.id.textContent)
            val textAuthor: TextView = view.findViewById(R.id.textAuthor)
        }
    }
}