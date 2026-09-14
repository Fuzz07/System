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
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.google.android.material.button.MaterialButton
import org.json.JSONArray
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

    // Opens a Messenger thread with the SSC page directly, rather than the page
    // itself: m.me hands straight off to the Messenger app when it is installed.
    private val SSC_MESSENGER_URL = "https://m.me/madridejoscollege"
    // After this many questions the assistant stops guessing and points the
    // student at a real officer. Offered once per chat session, not every turn.
    private val MESSENGER_AFTER_MESSAGES = 3
    private var userMessageCount = 0
    private var messengerOffered = false
    private val conversationHistory = mutableListOf<Pair<String, String>>()

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
        addMessage("Hello. I am the SSC portal assistant. How may I help you today?", "bot")
        addMessage("I can provide verified portal information about payments, proposals, budgets, announcements, confidential feedback, candidacy, and voting.", "bot")

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
            "Our officers reply to messages on the official SSC Facebook page, so " +
                "you'll be talking to a real person instead of me.",
            "bot",
            SSC_MESSENGER_URL
        )
    }

    /** Opens a link outside the app; used by the Messenger hand-off card. */
    private fun openLink(url: String) {
        try {
            startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
        } catch (e: Exception) {
            Toast.makeText(this, "No app can open that link", Toast.LENGTH_SHORT).show()
        }
    }

    private fun handleUserSendMessage(text: String) {
        addMessage(text, "user")
        val requestHistory = conversationHistory.takeLast(8)
        rememberConversation("user", text)
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
            var answer = fetchServerAnswer(text, cookie, requestHistory)
            if (answer == null) {
                Thread.sleep(400)
                answer = fetchServerAnswer(text, cookie, requestHistory)
            }

            if (answer != null) {
                runOnUiThread {
                    removeMessageAt(typingIndex)
                    addMessage(answer, "bot")
                    rememberConversation("assistant", answer)
                    maybeOfferMessenger()
                }
            } else {
                fallbackResponse(typingIndex, text)
            }
        }
    }

    /** Calls the student chatbot API once; returns the answer, or null on any failure. */
    private fun fetchServerAnswer(
        text: String,
        cookie: String,
        history: List<Pair<String, String>>
    ): String? {
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
                put("history", JSONArray().apply {
                    history.forEach { (role, content) ->
                        put(JSONObject().apply {
                            put("role", role)
                            put("content", content)
                        })
                    }
                })
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
            val answer = getBotLocalResponse(text.lowercase())
            addMessage(answer, "bot")
            rememberConversation("assistant", answer)
            maybeOfferMessenger()
        }
    }

    private fun rememberConversation(role: String, content: String) {
        conversationHistory.add(role to content)
        while (conversationHistory.size > 8) {
            conversationHistory.removeAt(0)
        }
    }

    private fun getBotLocalResponse(input: String): String {
        return when {
            input.contains("budget") -> {
                "Open the portal's Proposals page to review the latest visible project and budget records. I cannot quote a current amount while the live assistant service is unavailable."
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
                "Students can view and discuss visible proposals on the Proposals page. New proposals are submitted through officer accounts."
            }
            input.contains("feedback") -> {
                "Open the Feedback page to submit a concern or suggestion. Feedback is linked to your signed-in account and treated as confidential; it is not anonymous."
            }
            input.contains("contact") || input.contains("officer") -> {
                "Open the Officers page for the current SSC roster and available contact details."
            }
            input.contains("vote") || input.contains("voting") -> {
                "Interested in participating in the elections? 🗳️\n\nWhen voting is active, you can cast your secure ballot in 3 simple steps:\n1. Open the Voting Portal in the app menu.\n2. Review candidate platform and position details.\n3. Select your preferred candidates and tap the Cast Ballot button to safely record your vote."
            }
            input.contains("candidacy") || input.contains("run") -> {
                "Are you running for office? 🚀\n\nStudents can file for official candidacy through our platform:\n1. Visit the Candidacy Portal.\n2. Select your desired role and enter your campaign platform details.\n3. Note that eligibility is limited by department restrictions and active election timelines set by the administration."
            }
            input.contains("location") || input.contains("where") || input.contains("map") || input.contains("address") -> {
                "I cannot verify the current SSC office location while the live assistant service is unavailable. Please check the Officers page or contact an SSC officer."
            }
            input.contains("hello") || input.contains("hi") -> {
                "Hello. I am the SSC portal assistant. I can help with payments, proposals, budgets, announcements, feedback, candidacy, voting, and portal navigation."
            }
            input.contains("thanks") || input.contains("thank") -> {
                "You're very welcome! Let me know if there's anything else I can do to help you navigate the system. 🚀"
            }
            else -> {
                "I do not have enough verified information to answer that accurately while the live assistant service is unavailable. Please use the relevant portal page or contact an SSC officer."
            }
        }
    }

    inner class ChatAdapter(private val messages: List<ChatMessage>) :
        RecyclerView.Adapter<RecyclerView.ViewHolder>() {

        // A message that carries a link is the Messenger hand-off, and it gets a
        // card of its own rather than another chat bubble: it is an offer to
        // leave the conversation, not one more thing the assistant said.
        override fun getItemViewType(position: Int): Int =
            if (messages[position].actionUrl != null) TYPE_HANDOFF else TYPE_BUBBLE

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RecyclerView.ViewHolder {
            val inflater = LayoutInflater.from(parent.context)
            return if (viewType == TYPE_HANDOFF) {
                HandoffViewHolder(inflater.inflate(R.layout.item_chat_handoff, parent, false))
            } else {
                BubbleViewHolder(inflater.inflate(R.layout.item_chat_message, parent, false))
            }
        }

        override fun onBindViewHolder(holder: RecyclerView.ViewHolder, position: Int) {
            val message = messages[position]
            when (holder) {
                is HandoffViewHolder -> {
                    holder.body.text = message.text
                    val url = message.actionUrl
                    holder.button.setOnClickListener { if (url != null) openLink(url) }
                }
                is BubbleViewHolder -> {
                    if (message.sender == "bot") {
                        holder.layoutBot.visibility = View.VISIBLE
                        holder.layoutUser.visibility = View.GONE
                        holder.textBotMessage.text = message.text
                    } else {
                        holder.layoutBot.visibility = View.GONE
                        holder.layoutUser.visibility = View.VISIBLE
                        holder.textUserMessage.text = message.text
                    }
                }
            }
        }

        override fun getItemCount() = messages.size

        inner class BubbleViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val layoutBot: View = view.findViewById(R.id.layoutBot)
            val layoutUser: View = view.findViewById(R.id.layoutUser)
            val textBotMessage: TextView = view.findViewById(R.id.textBotMessage)
            val textUserMessage: TextView = view.findViewById(R.id.textUserMessage)
        }

        inner class HandoffViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val body: TextView = view.findViewById(R.id.textHandoffBody)
            val button: MaterialButton = view.findViewById(R.id.buttonMessenger)
        }
    }

    private companion object {
        const val TYPE_BUBBLE = 0
        const val TYPE_HANDOFF = 1
    }
}
