package com.ssc.studentapp

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.webkit.CookieManager
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.google.android.material.button.MaterialButton
import org.json.JSONObject
import java.io.OutputStreamWriter
import java.net.HttpURLConnection
import java.net.URL
import kotlin.concurrent.thread

data class ChatMessage(
    val text: String,
    val sender: String, // "user" or "bot"
    // When set, the bubble becomes tappable and opens this link. Used by the
    // hand-off that points a student at a real officer on Messenger.
    val actionUrl: String? = null
)

class ChatbotActivity : AppCompatActivity() {

    private lateinit var recyclerView: RecyclerView
    private lateinit var editTextMessage: EditText
    private lateinit var buttonSend: MaterialButton
    private lateinit var adapter: ChatAdapter
    private val messagesList = mutableListOf<ChatMessage>()
    private var portalUrl = BuildConfig.PORTAL_URL

    // Where to send a student who wants a human. Swap this for the page's
    // https://m.me/<page-username> link if you have it: that opens a Messenger
    // thread straight away instead of the page itself.
    private val SSC_MESSENGER_URL =
        "https://web.facebook.com/photo/?fbid=1287391326723459&set=a.467203248742275&__tn__=%3C"
    // After this many questions the assistant stops guessing and points the
    // student at a real officer. Offered once per chat session, not every turn.
    private val MESSENGER_AFTER_MESSAGES = 3
    private var userMessageCount = 0
    private var messengerOffered = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_chatbot)

        portalUrl = intent.getStringExtra("portal_url") ?: BuildConfig.PORTAL_URL

        val toolbar: Toolbar = findViewById(R.id.toolbar)
        toolbar.setNavigationIcon(androidx.appcompat.R.drawable.abc_ic_ab_back_material)
        toolbar.setNavigationOnClickListener { finish() }

        recyclerView = findViewById(R.id.recyclerViewMessages)
        editTextMessage = findViewById(R.id.editTextMessage)
        buttonSend = findViewById(R.id.buttonSend)

        adapter = ChatAdapter(messagesList)
        recyclerView.layoutManager = LinearLayoutManager(this).apply {
            stackFromEnd = true
        }
        recyclerView.adapter = adapter

        // Add initial bot messages
        addMessage("Hello! 👋 I'm your SSC Virtual Assistant. How can I help you today?", "bot")
        addMessage("I can assist you with your student concerns. Try asking about: proposals, anonymous feedback, tracking budgets, contacting officers, voting, or candidacy.", "bot")

        buttonSend.setOnClickListener {
            val text = editTextMessage.text.toString().trim()
            if (text.isNotEmpty()) {
                handleUserSendMessage(text)
            }
        }
    }

    private fun addMessage(text: String, sender: String, actionUrl: String? = null) {
        messagesList.add(ChatMessage(text, sender, actionUrl))
        adapter.notifyItemInserted(messagesList.size - 1)
        recyclerView.scrollToPosition(messagesList.size - 1)
    }

    /**
     * Three questions in, the assistant has had a fair go. Rather than keep
     * guessing, hand the student to an officer who can actually answer. Offered
     * once per chat session so it reads as help rather than nagging.
     */
    private fun maybeOfferMessenger() {
        if (messengerOffered || userMessageCount < MESSENGER_AFTER_MESSAGES) return
        messengerOffered = true
        addMessage(
            "Still not finding what you need? 💬\n\n" +
                "Our officers reply to messages on the official SSC Facebook page, so " +
                "you'll be talking to a real person instead of me.\n\n" +
                "Tap this message to open Messenger.",
            "bot",
            SSC_MESSENGER_URL
        )
    }

    /** Opens a link outside the app; used by the Messenger hand-off bubble. */
    private fun openLink(url: String) {
        try {
            startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
        } catch (e: Exception) {
            Toast.makeText(this, "No app can open that link", Toast.LENGTH_SHORT).show()
        }
    }

    private fun handleUserSendMessage(text: String) {
        addMessage(text, "user")
        userMessageCount++
        editTextMessage.setText("")

        // Add typing indicator
        addMessage("Thinking...", "bot")
        val typingIndex = messagesList.size - 1

        // Retrieve cookies on the MAIN thread to guarantee thread-safe, accurate session cookies!
        val cookie = CookieManager.getInstance().getCookie(portalUrl) ?: ""

        thread {
            // Retry once on a transient failure (dropped connection, cold server, etc.)
            // before giving up and showing the local rule-based answer.
            var answer = fetchServerAnswer(text, cookie)
            if (answer == null) {
                Thread.sleep(400)
                answer = fetchServerAnswer(text, cookie)
            }

            if (answer != null) {
                runOnUiThread {
                    removeMessageAt(typingIndex)
                    addMessage(answer, "bot")
                    maybeOfferMessenger()
                }
            } else {
                fallbackResponse(typingIndex, text)
            }
        }
    }

    /** Calls the student chatbot API once; returns the answer, or null on any failure. */
    private fun fetchServerAnswer(text: String, cookie: String): String? {
        return try {
            // Determine base URL dynamically and robustly from portal URL
            val parsedUrl = java.net.URL(portalUrl)
            val baseUrl = "${parsedUrl.protocol}://${parsedUrl.host}" + if (parsedUrl.port != -1) ":${parsedUrl.port}" else ""

            val url = URL("$baseUrl/student/chatbot/chat")
            val conn = url.openConnection() as HttpURLConnection
            conn.requestMethod = "POST"
            conn.setRequestProperty("Content-Type", "application/json")
            conn.setRequestProperty("Accept", "application/json")
            conn.setRequestProperty("User-Agent", "Mozilla/5.0 (Linux; Android 10) SSCStudentApp/1.0")
            conn.connectTimeout = 8000
            conn.readTimeout = 12000
            conn.doOutput = true

            // Retrieve and attach active WebView session cookies
            if (cookie.isNotEmpty()) {
                conn.setRequestProperty("Cookie", cookie)
            }

            // Attach CSRF Token if we can, but normally the Cookie session is sufficient for API
            val jsonParam = JSONObject().apply {
                put("message", text)
            }

            val os = conn.outputStream
            val writer = OutputStreamWriter(os, "UTF-8")
            writer.write(jsonParam.toString())
            writer.flush()
            writer.close()
            os.close()

            val answer = if (conn.responseCode == 200) {
                val stream = conn.inputStream.bufferedReader().use { it.readText() }
                val responseObj = JSONObject(stream)
                if (responseObj.optBoolean("success", false)) {
                    responseObj.optString("answer", "").trim()
                } else ""
            } else ""

            conn.disconnect()
            answer.ifEmpty { null }
        } catch (e: Exception) {
            e.printStackTrace()
            null
        }
    }

    private fun removeMessageAt(index: Int) {
        if (index >= 0 && index < messagesList.size) {
            messagesList.removeAt(index)
            adapter.notifyItemRemoved(index)
        }
    }

    private fun fallbackResponse(typingIndex: Int, text: String) {
        runOnUiThread {
            removeMessageAt(typingIndex)
            addMessage(getBotLocalResponse(text.toLowerCase()), "bot")
            maybeOfferMessenger()
        }
    }

    private fun getBotLocalResponse(input: String): String {
        return when {
            input.contains("budget") -> {
                "Want to track where your student fees go? 📊\n\nWe maintain full transparency of our budget:\n• Check the Dashboard to see summary charts of allocated versus spent funds.\n• Check the Proposals Portal to review specific project budgets, liquidation logs, and uploaded receipts for completed projects."
            }
            input.contains("enroll") || input.contains("payment") || input.contains("gcash") -> {
                "Need to settle your enrollment fee? 💳\n\nHead to the Enrollment page on your sidebar to:\n• View your current payment status for this school year.\n• Pay via GCash/bank transfer and upload proof, or wait for admin confirmation of a walk-in payment.\n• Once confirmed, your status updates automatically and you'll be notified."
            }
            input.contains("announcement") || input.contains("news") || input.contains("update") -> {
                "Want to stay in the loop? 📰\n\nAll official SSC announcements, project updates, and campus news are posted on the Announcements page, accessible from your sidebar."
            }
            input.contains("dashboard") || input.contains("overview") || input.contains("summary") -> {
                "Your Dashboard is your home base. 🏠\n\nIt gives you a quick overview of budget summaries, recent announcements, and your account status the moment you log in."
            }
            input.contains("proposal") -> {
                "Want to submit a project proposal? 📝\n\nStudent organizations and department representatives can request Supreme Student Council (SSC) funding easily:\n1. Navigate to the Proposals Portal on your sidebar.\n2. Click the Submit Proposal button and fill in the project title, expected timeline, and estimated budget.\n3. Once submitted, it will appear on the discussions list for student feedback and voting."
            }
            input.contains("feedback") -> {
                "Your voice is essential to build a better campus! 💬\n\nTo share feedback, suggestions, or concerns with the council:\n1. Open the Student Feedback Wall.\n2. Write your message and choose the type (Suggestion, Inquiry, or Concern).\n3. Check Submit Anonymously to keep your identity private if preferred."
            }
            input.contains("contact") || input.contains("officer") -> {
                "Let's stay connected! 📞\n\nYou can reach the SSC officers through our official channels:\n• Email: ssc.official@mcclawis.edu.ph\n• Facebook: SSC Official Facebook Page\n• Office: Student Center, 2nd Floor, MCC Campus\n• Office Hours: Mon-Fri | 8:00 AM – 5:00 PM"
            }
            input.contains("vote") || input.contains("voting") -> {
                "Interested in participating in the elections? 🗳️\n\nWhen voting is active, you can cast your secure ballot in 3 simple steps:\n1. Open the Voting Portal in the app menu.\n2. Review candidate platform and position details.\n3. Select your preferred candidates and tap the Cast Ballot button to safely record your vote."
            }
            input.contains("candidacy") || input.contains("run") -> {
                "Are you running for office? 🚀\n\nStudents can file for official candidacy through our platform:\n1. Visit the Candidacy Portal.\n2. Select your desired role and enter your campaign platform details.\n3. Note that eligibility is limited by department restrictions and active election timelines set by the administration."
            }
            input.contains("location") || input.contains("where") || input.contains("map") || input.contains("address") -> {
                "Our campus and the SSC Office are located at:\n📍 Madridejos Community College (MCC)\nBunakan, Madridejos, Cebu, Philippines.\n\n🏢 SSC Office Location: Student Center, 2nd Floor, MCC Campus."
            }
            input.contains("hello") || input.contains("hi") -> {
                "Hi there! 👋 I'm your SSC assistant. I can help you with student concerns, proposals, anonymous feedback, and budget tracking. What can I do for you today?"
            }
            input.contains("thanks") || input.contains("thank") -> {
                "You're very welcome! Let me know if there's anything else I can do to help you navigate the system. 🚀"
            }
            else -> {
                "I'm sorry, I don't have a specific answer for that.\n\nTry asking about:\n• proposals\n• anonymous feedback\n• track budgets\n• contact ssc\n• voting\n• candidacy"
            }
        }
    }

    inner class ChatAdapter(private val messages: List<ChatMessage>) :
        RecyclerView.Adapter<ChatAdapter.ViewHolder>() {

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
            val view = LayoutInflater.from(parent.context)
                .inflate(R.layout.item_chat_message, parent, false)
            return ViewHolder(view)
        }

        override fun onBindViewHolder(holder: ViewHolder, position: Int) {
            val message = messages[position]
            if (message.sender == "bot") {
                holder.layoutBot.visibility = View.VISIBLE
                holder.layoutUser.visibility = View.GONE
                holder.textBotMessage.text = message.text
                // Holders are recycled, so both states have to be set explicitly
                // or a plain answer inherits the previous bubble's tap target.
                val url = message.actionUrl
                val ctx = holder.itemView.context
                if (url != null) {
                    holder.textBotMessage.setTextColor(ContextCompat.getColor(ctx, R.color.brand_600))
                    holder.textBotMessage.setOnClickListener { openLink(url) }
                } else {
                    holder.textBotMessage.setTextColor(ContextCompat.getColor(ctx, R.color.slate_800))
                    holder.textBotMessage.setOnClickListener(null)
                    holder.textBotMessage.isClickable = false
                }
            } else {
                holder.layoutBot.visibility = View.GONE
                holder.layoutUser.visibility = View.VISIBLE
                holder.textUserMessage.text = message.text
            }
        }

        override fun getItemCount() = messages.size

        inner class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val layoutBot: View = view.findViewById(R.id.layoutBot)
            val layoutUser: View = view.findViewById(R.id.layoutUser)
            val textBotMessage: TextView = view.findViewById(R.id.textBotMessage)
            val textUserMessage: TextView = view.findViewById(R.id.textUserMessage)
        }
    }
}