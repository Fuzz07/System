<!-- ===== CHATBOT WIDGET ===== -->
<div id="ssc-chatbot" class="chatbot-container">
  <!-- Draggable/floating Toggle Button & Label -->
  <div class="chatbot-toggle-wrapper">
    <div class="chatbot-label" id="chatbotLabel">Need help?</div>
    <div class="chatbot-toggle" id="chatbotToggle">
      <i class="bi bi-robot" id="toggleIcon"></i>
    </div>
  </div>

  <!-- Chatbot Window -->
  <div class="chatbot-window" id="chatbotWindow">
    <!-- Header (Draggable) -->
    <div class="chatbot-header" id="chatbotHeader">
      <div class="header-top-row">
        <div class="header-logo" style="display: flex; align-items: center; gap: 8px;">
          <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo" style="width: 30px; height: 30px; object-fit: contain;">
          <span class="logo-text">SSC Help</span>
        </div>
        <div class="header-avatars">
          <div class="avatar-group">
            <img class="avatar-img" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Officer 1">
            <img class="avatar-img" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Officer 2">
            <img class="avatar-img" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" alt="Officer 3">
            <span class="online-badge"></span>
          </div>
        </div>
        <button class="chatbot-close-btn" id="chatbotClose"><i class="bi bi-x-lg"></i></button>
      </div>
      <h2 class="home-welcome-text">Hi there 👋<br>How can we help?</h2>
    </div>

    <!-- Messages Log -->
    <div class="chatbot-messages" id="chatbotMessages">
      <div class="chat-msg bot-msg">
        Hello! 👋 I'm your SSC Virtual Assistant. How can I help you today?
      </div>
      <div class="chat-msg bot-msg">
        I can assist you with your student concerns. Tap a shortcut below or type your question.
      </div>
    </div>

    <!-- Collapsible Shortcuts Wrapper -->
    <div class="chatbot-shortcuts-wrapper" id="shortcutsWrapper">
      <div class="shortcuts-toggle-bar" id="btnToggleShortcuts">
        <span class="drag-handle-line"></span>
        <span class="shortcuts-toggle-text">Quick Actions</span>
        <i class="bi bi-chevron-down" id="shortcutsChevron"></i>
      </div>
      <div class="chatbot-shortcuts" id="chatbotShortcuts">
        <button class="shortcut-btn" data-query="proposal">
          <span class="btn-emoji">📝</span> Submit Project Proposals
        </button>
        <button class="shortcut-btn" data-query="feedback">
          <span class="btn-emoji">💬</span> Post Anonymous Feedback
        </button>
        <button class="shortcut-btn" data-query="budget">
          <span class="btn-emoji">📊</span> Track Project Budgets
        </button>
        <button class="shortcut-btn" data-query="contact">
          <span class="btn-emoji">📞</span> Contact SSC Officers
        </button>
      </div>
    </div>

    <!-- Input Area -->
    <div class="chatbot-input-area">
      <input type="text" id="chatbotInput" placeholder="Write a message..." autocomplete="off">
      <button id="chatbotSend"><i class="bi bi-send-fill"></i></button>
    </div>
  </div>
</div>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

  :root {
    --chatbot-primary: #2563eb;
    --chatbot-primary-dark: #1d4ed8;
    --chatbot-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    --chatbot-bg: #f8fafc;
    --chatbot-card-bg: #ffffff;
    --chatbot-text-main: #0f172a;
    --chatbot-text-muted: #64748b;
    --chatbot-border: #e2e8f0;
    --chatbot-success: #10b981;
    --chatbot-shadow: 0 12px 40px rgba(15, 23, 42, 0.12), 0 4px 12px rgba(15, 23, 42, 0.04);
  }

  /* Chatbot Container */
  .chatbot-container {
    position: fixed;
    bottom: calc(var(--nav-height, 64px) + var(--safe-bottom, 0px) + 50px);
    right: 16px;
    z-index: 10000;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
  }

  @media (max-width: 480px) {
    .chatbot-container {
      bottom: calc(var(--nav-height, 64px) + var(--safe-bottom, 0px) + 12px);
      left: 50%;
      right: auto;
      transform: translateX(-50%);
    }
  }

  /* Chatbot Label */
  .chatbot-label {
    position: absolute;
    right: 80px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--chatbot-primary-dark);
    color: #fff;
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: var(--chatbot-shadow);
    pointer-events: none;
    opacity: 0;
    animation: labelSlide 6s infinite;
  }

  .chatbot-label::after {
    content: '';
    position: absolute;
    right: -6px;
    top: 50%;
    transform: translateY(-50%);
    border-left: 6px solid var(--chatbot-primary-dark);
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
  }

  @keyframes labelSlide {
    0%, 15%, 85%, 100% {
      opacity: 0;
      transform: translateY(-50%) translateX(10px);
    }
    25%, 75% {
      opacity: 1;
      transform: translateY(-50%) translateX(0);
    }
  }

  /* Toggle Button Wrapper */
  .chatbot-toggle-wrapper {
    position: relative;
  }

  /* Toggle Button */
  .chatbot-toggle {
    width: 64px;
    height: 64px;
    background: var(--chatbot-gradient);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    cursor: pointer;
    box-shadow: 0 8px 32px rgba(37, 99, 235, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: chatbotPulse 3s infinite;
  }

  .chatbot-toggle:hover {
    transform: scale(1.08) rotate(5deg);
    box-shadow: 0 12px 40px rgba(37, 99, 235, 0.45);
    animation-play-state: paused;
  }

  @keyframes chatbotPulse {
    0%, 100% {
      transform: scale(1);
      box-shadow: 0 8px 32px rgba(37, 99, 235, 0.3);
    }
    50% {
      transform: scale(1.06);
      box-shadow: 0 12px 40px rgba(37, 99, 235, 0.38);
    }
  }

  /* Chat Window */
  .chatbot-window {
    position: absolute;
    bottom: 85px;
    right: 0;
    width: 390px;
    height: 600px;
    background: var(--chatbot-bg);
    border-radius: 24px;
    box-shadow: var(--chatbot-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
    transform: translateY(30px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--chatbot-border);
  }

  .chatbot-window.active {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0) scale(1);
  }

  /* Shared Close Button */
  .chatbot-close-btn {
    background: transparent;
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.1rem;
    transition: background-color 0.2s;
  }

  .chatbot-close-btn:hover {
    background: rgba(255, 255, 255, 0.15);
  }

  /* Header Section (Draggable) */
  .chatbot-header {
    background: var(--chatbot-gradient);
    color: #fff;
    padding: 24px 20px 20px 20px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    cursor: grab;
    user-select: none;
    touch-action: none;
  }

  .chatbot-header:active {
    cursor: grabbing;
  }

  .chatbot-toggle {
    touch-action: none;
  }

  .header-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }

  .header-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.2rem;
    font-weight: 800;
    letter-spacing: -0.5px;
  }

  .avatar-group {
    display: flex;
    align-items: center;
    position: relative;
    padding-right: 6px;
  }

  .avatar-img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #1e3a8a;
    margin-left: -10px;
    object-fit: cover;
    transition: transform 0.2s;
    background: #e2e8f0;
  }

  .avatar-img:first-child {
    margin-left: 0;
  }

  .online-badge {
    position: absolute;
    bottom: 1px;
    right: 2px;
    width: 10px;
    height: 10px;
    background-color: var(--chatbot-success);
    border-radius: 50%;
    border: 2px solid #1e3a8a;
    z-index: 11;
  }

  .home-welcome-text {
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.3;
    margin: 0;
    letter-spacing: -0.5px;
  }

  /* Chat Messages Area */
  .chatbot-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: var(--chatbot-bg);
    scrollbar-width: thin;
  }

  .chatbot-messages::-webkit-scrollbar {
    width: 5px;
  }

  .chatbot-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
  }

  .chat-msg {
    max-width: 80%;
    padding: 12px 16px;
    font-size: 0.88rem;
    line-height: 1.5;
    animation: messageSlide 0.3s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.02);
  }

  .bot-msg {
    background: #ffffff;
    color: var(--chatbot-text-main);
    align-self: flex-start;
    border-radius: 4px 16px 16px 16px;
    border: 1px solid var(--chatbot-border);
    margin-left: 36px;
  }

  .bot-msg::before {
    content: '\F52A';
    font-family: 'bootstrap-icons';
    position: absolute;
    left: -38px;
    bottom: 0;
    width: 28px;
    height: 28px;
    background: var(--chatbot-gradient);
    color: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    box-shadow: 0 4px 8px rgba(37, 99, 235, 0.15);
  }

  .user-msg {
    background: var(--chatbot-gradient);
    color: #fff;
    align-self: flex-end;
    border-radius: 16px 16px 4px 16px;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
  }

  .chat-link {
    color: var(--chatbot-primary);
    text-decoration: none;
    font-weight: 600;
    border-bottom: 1px dashed rgba(37, 99, 235, 0.4);
    transition: all 0.2s;
  }

  .chat-link:hover {
    color: var(--chatbot-primary-dark);
    border-bottom-color: var(--chatbot-primary-dark);
  }

  .bot-msg b {
    color: var(--chatbot-primary-dark);
    font-weight: 700;
  }

  /* Collapsible Shortcuts Panel */
  .chatbot-shortcuts-wrapper {
    background: #fff;
    border-top: 1px solid var(--chatbot-border);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .shortcuts-toggle-bar {
    padding: 12px 16px 8px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    background: #fff;
    user-select: none;
    position: relative;
  }

  .shortcuts-toggle-bar:hover {
    background: #f8fafc;
  }

  .drag-handle-line {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 32px;
    height: 4px;
    background: #cbd5e1;
    border-radius: 2px;
    top: 6px;
  }

  .shortcuts-toggle-text {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    color: var(--chatbot-text-muted);
    letter-spacing: 0.05em;
  }

  #shortcutsChevron {
    font-size: 0.85rem;
    color: var(--chatbot-text-muted);
    transition: transform 0.3s ease;
  }

  .chatbot-shortcuts {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    padding: 4px 16px 16px 16px;
    max-height: 250px;
    overflow-y: auto;
    transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease, opacity 0.3s ease;
    opacity: 1;
  }

  /* Stacked Pill-Shaped Buttons matching screenshot */
  .shortcut-btn {
    width: auto;
    max-width: 100%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.88rem;
    color: #2563eb;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 8px;
    text-align: left;
    outline: none;
  }

  .btn-emoji {
    font-size: 1.05rem;
  }

  .shortcut-btn:hover, .shortcut-btn:focus {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    transform: translateY(-1px);
  }

  /* Collapsed state rules */
  .chatbot-shortcuts-wrapper.collapsed .chatbot-shortcuts {
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
    opacity: 0;
    pointer-events: none;
    overflow: hidden;
  }

  .chatbot-shortcuts-wrapper.collapsed #shortcutsChevron {
    transform: rotate(-180deg);
  }

  /* Typing Dots */
  .typing-dots {
    display: flex;
    gap: 4px;
    padding: 6px 0;
    align-items: center;
    justify-content: center;
  }

  .typing-dots span {
    width: 6px;
    height: 6px;
    background: var(--chatbot-primary);
    border-radius: 50%;
    opacity: 0.4;
    animation: typingBounce 1.4s infinite ease-in-out both;
  }

  .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
  .typing-dots span:nth-child(2) { animation-delay: -0.16s; }

  /* Input Area */
  .chatbot-input-area {
    padding: 12px 16px;
    background: #fff;
    border-top: 1px solid var(--chatbot-border);
    display: flex;
    gap: 10px;
    align-items: center;
    flex-shrink: 0;
  }

  #chatbotInput {
    flex: 1;
    padding: 10px 16px;
    border: 1px solid var(--chatbot-border);
    border-radius: 12px;
    font-size: 0.88rem;
    transition: all 0.2s;
    background: var(--chatbot-bg);
  }

  #chatbotInput:focus {
    outline: none;
    border-color: var(--chatbot-primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  }

  #chatbotSend {
    background: var(--chatbot-primary);
    color: #fff;
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.95rem;
  }

  #chatbotSend:hover {
    background: var(--chatbot-primary-dark);
    transform: scale(1.04);
  }

  /* Animations keyframes */
  @keyframes messageSlide {
    from {
      opacity: 0;
      transform: translateY(8px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes typingBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
  }

  /* Responsive styling */
  @media (max-width: 480px) {
    .chatbot-container {
      bottom: calc(var(--nav-height, 64px) + var(--safe-bottom, 0px) + 12px);
      left: 16px;
      right: auto;
      transform: none;
      width: calc(100% - 32px);
      max-width: 440px;
    }

    /* When open on mobile, the window floats above the bottom nav instead of full-screen */
    .chatbot-window.active {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      width: 100%;
      height: calc(72vh);
      max-height: calc(100vh - (var(--nav-height, 64px) + var(--safe-bottom, 0px) + 32px));
      border-radius: 24px;
      z-index: 99999;
      transform: translateY(0) scale(1);
    }

    .chatbot-container.chatbot-open .chatbot-toggle-wrapper {
      opacity: 0;
      pointer-events: none;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.chatbot-container');
    const toggleBtn = document.getElementById('chatbotToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    const chatWindow = document.getElementById('chatbotWindow');
    const chatbotInput = document.getElementById('chatbotInput');
    const sendBtn = document.getElementById('chatbotSend');
    const messagesContainer = document.getElementById('chatbotMessages');
    const shortcuts = document.querySelectorAll('.shortcut-btn');
    const closeBtn = document.getElementById('chatbotClose');
    const header = document.getElementById('chatbotHeader');

    // Shortcuts Toggle Elements
    const btnToggleShortcuts = document.getElementById('btnToggleShortcuts');
    const shortcutsWrapper = document.getElementById('shortcutsWrapper');

    if (!toggleBtn) return;

    // --- Detect Mobile ---
    function isMobile() {
      return window.innerWidth <= 480;
    }

    // --- Draggable Functionality ---
    let isDragging = false;
    let initialX, initialY;
    let xOffset = 0, yOffset = 0;
    let hasMoved = false;

    // Load saved position (desktop and mobile)
    const savedPos = localStorage.getItem('chatbotPosition');
    if (savedPos) {
      try {
        const pos = JSON.parse(savedPos);
        xOffset = pos.x;
        yOffset = pos.y;
        container.style.transform = `translate3d(${xOffset}px, ${yOffset}px, 0)`;
      } catch (e) {}
    }

    function getClientPos(e) {
      if (e.touches && e.touches.length) return { x: e.touches[0].clientX, y: e.touches[0].clientY };
      if (e.changedTouches && e.changedTouches.length) return { x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY };
      return { x: e.clientX, y: e.clientY };
    }

    function dragStart(e) {
      if (e.target.closest('#chatbotClose') || e.target.closest('.chatbot-close-btn')) return;

      const pos = getClientPos(e);
      initialX = pos.x - xOffset;
      initialY = pos.y - yOffset;

      const isHeader = e.target.closest('#chatbotHeader');
      const isToggle = e.target.closest('#chatbotToggle');

      if (isHeader || isToggle) {
        isDragging = true;
        hasMoved = false;
        if (isToggle) toggleBtn.style.animation = 'none';
      }
    }

    function drag(e) {
      if (!isDragging) return;
      e.preventDefault();
      const pos = getClientPos(e);
      const newX = pos.x - initialX;
      const newY = pos.y - initialY;
      xOffset = newX;
      yOffset = newY;

      if (Math.abs(newX) > 5 || Math.abs(newY) > 5) hasMoved = true;
      container.style.transform = `translate3d(${newX}px, ${newY}px, 0)`;
    }

    function dragEnd() {
      if (!isDragging) return;
      isDragging = false;
      toggleBtn.style.animation = 'chatbotPulse 3s infinite';
      localStorage.setItem('chatbotPosition', JSON.stringify({ x: xOffset, y: yOffset }));
    }

    // Pointer events for desktop and mobile drag support
    toggleBtn.addEventListener('pointerdown', dragStart, { passive: false });
    header.addEventListener('pointerdown', dragStart, { passive: false });
    document.addEventListener('pointermove', drag, { passive: false });
    document.addEventListener('pointerup', dragEnd);
    document.addEventListener('pointercancel', dragEnd);

    // --- Toggle chatbot window ---
    toggleBtn.addEventListener('click', (e) => {
      if (hasMoved) {
        hasMoved = false; // Reset for next click
        return;
      }
      const isActive = chatWindow.classList.contains('active');
      isActive ? closeChatbot() : openChatbot();
    });

    function openChatbot() {
      chatWindow.classList.add('active');
      toggleIcon.className = 'bi bi-chevron-down';
      setTimeout(() => chatbotInput.focus(), 100);
    }

    function closeChatbot() {
      chatWindow.classList.remove('active');
      toggleIcon.className = 'bi bi-robot';
    }

    closeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      closeChatbot();
    });

    // --- Collapsible Shortcuts Panel Trigger ---
    btnToggleShortcuts.addEventListener('click', () => {
      shortcutsWrapper.classList.toggle('collapsed');
      setTimeout(() => {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
      }, 300);
    });

    // --- Message Handling Logic ---
    shortcuts.forEach(btn => {
      btn.addEventListener('click', () => {
        handleSend(btn.dataset.query);
      });
    });

    function addMessage(text, sender) {
      const msgDiv = document.createElement('div');
      msgDiv.classList.add('chat-msg', sender === 'user' ? 'user-msg' : 'bot-msg');

      if (sender === 'bot') {
        msgDiv.innerHTML = text.replace(/\n/g, '<br>');
      } else {
        msgDiv.textContent = text;
      }

      messagesContainer.appendChild(msgDiv);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function handleSend(overrideText = null) {
      const text = overrideText || chatbotInput.value.trim();
      if (!text) return;

      addMessage(text, 'user');
      chatbotInput.value = '';

      const typingDiv = document.createElement('div');
      typingDiv.classList.add('chat-msg', 'bot-msg', 'typing-indicator');
      typingDiv.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
      messagesContainer.appendChild(typingDiv);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;

      setTimeout(() => {
        typingDiv.remove();
        addMessage(getBotResponse(text.toLowerCase()), 'bot');
      }, 1000);
    }

    sendBtn.addEventListener('click', () => handleSend());
    chatbotInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') handleSend();
    });

    function getBotResponse(input) {
      const responses = {
        'budget': "Want to track where your student fees go? 📊\n\nWe maintain full transparency of our budget:\n• Visit the <a href='{{ route('student.proposals') }}' class='chat-link'>Dashboard</a> to see summary charts of allocated versus spent funds.\n• Check the <a href='{{ route('student.proposals') }}' class='chat-link'>Proposals Portal</a> to review specific project budgets, liquidation logs, and uploaded receipts for completed projects.",
        'proposal': "Want to submit a project proposal? 📝\n\nStudent organizations and department representatives can request Supreme Student Council (SSC) funding easily:\n1. Navigate to the <a href='{{ route('student.proposals') }}' class='chat-link'>Proposals Portal</a> on your sidebar.\n2. Click the <b>Submit Proposal</b> button and fill in the project title, expected timeline, and estimated budget.\n3. Once submitted, it will appear on the discussions list for student feedback and voting.\n4. The SSC Board will review and vote on official approval.",
        'feedback': "Your voice is essential to build a better campus! 💬\n\nTo share feedback, suggestions, or concerns with the council:\n1. Open the <a href='{{ route('student.feedback') }}' class='chat-link'>Student Feedback Wall</a>.\n2. Write your message and choose the type (Suggestion, Inquiry, or Concern).\n3. Check <b>Submit Anonymously</b> to keep your identity private if preferred.\n4. All submissions are read and addressed directly by the SSC Executive Committee.",
        'contact': "Let's stay connected! 📞\n\nYou can reach the SSC officers through our official channels:\n• <b>Email:</b> <a href='mailto:ssc.official@mcclawis.edu.ph' class='chat-link'>ssc.official@mcclawis.edu.ph</a>\n• <b>Facebook:</b> <a href='https://www.facebook.com/share/17N13YJMUC/' target='_blank' class='chat-link'>SSC Official Page</a>\n• <b>Office:</b> Student Center, 2nd Floor, MCC Campus\n• <b>Office Hours:</b> Mon-Fri | 8:00 AM – 5:00 PM",
        'hello': "Hi there! 👋 I'm your SSC assistant. I can help you with student concerns, proposals, anonymous feedback, and budget tracking. What can I do for you today?",
        'hi': "Hello! 🌟 Hope you're having a good day. Need help with project proposals, tracking budgets, or posting feedback?",
        'thanks': "You're very welcome! Let me know if there's anything else I can do to help you navigate the system. 🚀",
        'thank': "Anytime! Stay awesome. Let me know if you have other student concerns!"
      };

      for (const key in responses) {
        if (input.includes(key)) return responses[key];
      }

      return "I'm sorry, I don't have a specific answer for that. \n\nTry asking about: \n• proposals \n• anonymous feedback \n• track budgets \n• contact ssc";
    }
  });
</script>
