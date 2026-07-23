package com.ssc.studentapp

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.webkit.CookieManager
import android.widget.EditText
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
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
    val sender: String // "user" or "bot"
)

class ChatbotActivity : AppCompatActivity() {

    private lateinit var recyclerView: RecyclerView
    private lateinit var editTextMessage: EditText
    private lateinit var buttonSend: MaterialButton
    private lateinit var adapter: ChatAdapter
    private val messagesList = mutableListOf<ChatMessage>()
    private val portalUrl = BuildConfig.PORTAL_URL

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_chatbot)

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

    private fun addMessage(text: String, sender: String) {
        messagesList.add(ChatMessage(text, sender))
        adapter.notifyItemInserted(messagesList.size - 1)
        recyclerView.scrollToPosition(messagesList.size - 1)
    }

    private fun handleUserSendMessage(text: String) {
        addMessage(text, "user")
        editTextMessage.setText("")

        // Add typing indicator
        addMessage("Thinking...", "bot")
        val typingIndex = messagesList.size - 1

        thread {
            try {
                val baseUrl = portalUrl.replace("/login/student", "").trimEnd('/')
                val url = URL("$baseUrl/student/chatbot/chat")
                val conn = url.openConnection() as HttpURLConnection
                conn.requestMethod = "POST"
                conn.setRequestProperty("Content-Type", "application/json")
                conn.setRequestProperty("Accept", "application/json")
                conn.connectTimeout = 8000
                conn.readTimeout = 8000
                conn.doOutput = true

                // Retrieve and attach active WebView session cookies
                val cookie = CookieManager.getInstance().getCookie(portalUrl)
                if (!cookie.isNullOrEmpty()) {
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

                if (conn.responseCode == 200) {
                    val stream = conn.inputStream.bufferedReader().use { it.readText() }
                    val responseObj = JSONObject(stream)
                    if (responseObj.optBoolean("success", false)) {
                        val answer = responseObj.optString("answer", "")
                        runOnUiThread {
                            removeMessageAt(typingIndex)
                            addMessage(answer, "bot")
                        }
                        conn.disconnect()
                        return@thread
                    }
                }
                conn.disconnect()
                fallbackResponse(typingIndex, text)
            } catch (e: Exception) {
                e.printStackTrace()
                fallbackResponse(typingIndex, text)
            }
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
        }
    }

    private fun getBotLocalResponse(input: String): String {
        return when {
            input.contains("budget") -> {
                "Want to track where your student fees go? 📊\n\nWe maintain full transparency of our budget:\n• Check the Dashboard to see summary charts of allocated versus spent funds.\n• Check the Proposals Portal to review specific project budgets, liquidation logs, and uploaded receipts for completed projects."
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