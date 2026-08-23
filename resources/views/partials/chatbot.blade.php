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
          <span class="logo-text">SSC Assistant</span>
        </div>
        <div class="header-status">
          <span class="status-dot"></span> Online
        </div>
        <button class="chatbot-close-btn" id="chatbotClose"><i class="bi bi-x-lg"></i></button>
      </div>
      <h2 class="home-welcome-text">Hi there 👋</h2>
      <p class="home-welcome-sub">How can we help you today?</p>
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
        <button class="shortcut-btn" data-query="feedback">
          <span class="btn-emoji">💬</span> Post Anonymous Feedback
        </button>
        <button class="shortcut-btn" data-query="budget">
          <span class="btn-emoji">📊</span> Track Project Budgets
        </button>
        <button class="shortcut-btn" data-query="contact">
          <span class="btn-emoji">📞</span> Contact SSC Officers
        </button>
        <button class="shortcut-btn" data-query="location">
          <span class="btn-emoji">📍</span> SSC Office & School Location
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
  :root {
    --chatbot-primary: #2563eb;
    --chatbot-primary-dark: #1d4ed8;
    --chatbot-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    /* Idle-animation tokens. Named distinctly from --chatbot-shadow below,
       which already holds a full box-shadow value. */
    --chatbot-ring: rgba(37, 99, 235, 0.45);
    --chatbot-glow: rgba(37, 99, 235, 0.3);
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
    bottom: calc(var(--nav-height, 64px) + var(--safe-bottom, 0px) + 15px);
    right: 16px;
    z-index: 10000;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    /* Force this fixed-position widget onto its own compositor layer — Android
       WebView is known to repaint/flicker plain position:fixed elements during
       page transitions and scrolling without this hint. */
    transform: translateZ(0);
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    will-change: transform;
  }

  /* Chatbot Label */
  .chatbot-label {
    position: absolute;
    right: 80px;
    top: 50%;
    transform: translateY(-50%) translateX(10px);
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
    transition: opacity 0.25s ease, transform 0.25s ease;
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

  .chatbot-toggle-wrapper:hover .chatbot-label {
    opacity: 1;
    transform: translateY(-50%) translateX(0);
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
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .chatbot-toggle:hover {
    transform: scale(1.08) rotate(5deg);
    box-shadow: 0 12px 40px rgba(37, 99, 235, 0.45);
  }

  /* ── Idle attention animation ──
     A slow float plus an expanding halo, so the assistant reads as available
     without nagging. The icon gives a periodic wiggle on a long delay.
     Everything is transform/opacity only, so it stays on the compositor. */
  @keyframes chatbotFloat {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-6px); }
  }

  /* Expanding ring drawn with box-shadow rather than a pseudo-element: the
     float animation makes the button its own stacking context, which would
     bury a z-index:-1 pseudo-element behind its own background. */
  @keyframes chatbotHalo {
    0%   { box-shadow: 0 8px 32px var(--chatbot-glow), 0 0 0 0 var(--chatbot-ring); }
    70%  { box-shadow: 0 8px 32px var(--chatbot-glow), 0 0 0 16px transparent; }
    100% { box-shadow: 0 8px 32px var(--chatbot-glow), 0 0 0 0 transparent; }
  }

  @keyframes chatbotWiggle {
    0%, 88%, 100%   { transform: rotate(0deg); }
    91%             { transform: rotate(-14deg); }
    94%             { transform: rotate(12deg); }
    97%             { transform: rotate(-6deg); }
  }

  /* The float lives on the WRAPPER, not the button: an animation on the
     button's own transform would outrank the :hover scale below and the
     button would stop responding to hover. */
  .chatbot-toggle-wrapper {
    animation: chatbotFloat 3.2s ease-in-out infinite;
  }

  .chatbot-toggle {
    animation: chatbotHalo 2.6s ease-out infinite;
  }

  .chatbot-toggle > i {
    display: block;
    animation: chatbotWiggle 6s ease-in-out infinite;
  }

  /* Drop the idle motion on interaction and while the panel is open, so it
     never competes with what the user is actually doing. `animation: none`
     rather than a pause, so the :hover box-shadow can take effect. */
  .chatbot-toggle-wrapper:hover,
  .chatbot-container.chatbot-open .chatbot-toggle-wrapper {
    animation: none;
  }

  .chatbot-toggle:hover,
  .chatbot-toggle:hover > i,
  .chatbot-container.chatbot-open .chatbot-toggle,
  .chatbot-container.chatbot-open .chatbot-toggle > i {
    animation: none;
  }

  @media (prefers-reduced-motion: reduce) {
    .chatbot-toggle-wrapper,
    .chatbot-toggle,
    .chatbot-toggle > i {
      animation: none;
    }
  }

  /* Chat Window */
  .chatbot-window {
    position: absolute;
    bottom: 85px;
    right: calc(100% + 12px);
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
    visibility: hidden;
    transform: translateY(30px) scale(0.95);
    transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease;
    border: 1px solid var(--chatbot-border);
  }

  .chatbot-window.active {
    opacity: 1;
    pointer-events: auto;
    visibility: visible;
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

  .header-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
    background: rgba(255, 255, 255, 0.12);
    padding: 5px 12px;
    border-radius: 20px;
  }

  .status-dot {
    width: 8px;
    height: 8px;
    background-color: var(--chatbot-success);
    border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
  }

  .home-welcome-text {
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.3;
    margin: 0;
    letter-spacing: -0.5px;
  }

  .home-welcome-sub {
    font-size: 0.82rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.75);
    margin: 4px 0 0;
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

  /* Messenger hand-off card. Offered once, after the assistant has had a few
     tries at a question. It is an offer to leave the conversation, so it reads
     as a card in Facebook's blue rather than as one more thing the bot said. */
  .chat-handoff {
    align-self: stretch;
    margin-left: 36px;
    background: #f2f8ff;
    border: 1px solid #c7e0ff;
    border-radius: 18px;
    padding: 14px;
    animation: messageSlide 0.3s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
  }

  .chat-handoff-head {
    display: flex;
    align-items: center;
    gap: 11px;
  }

  .chat-handoff-icon {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #0084ff 0%, #0064d2 100%);
    color: #fff;
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .chat-handoff-title {
    font-size: 0.94rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
  }

  .chat-handoff-sub {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 1px;
  }

  .chat-handoff-body {
    margin-top: 10px;
    font-size: 0.84rem;
    line-height: 1.55;
    color: #334155;
  }

  .chat-handoff-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 12px;
    height: 44px;
    border-radius: 14px;
    background: #0084ff;
    color: #fff;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s ease, transform 0.12s ease;
  }

  .chat-handoff-btn:hover {
    background: #0064d2;
    color: #fff;
  }

  .chat-handoff-btn:active {
    transform: scale(0.98);
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
      right: 16px;
      left: auto;
      /* Keep translateZ(0) (not "none") so the widget stays on its own
         compositor layer on phone-width screens — this is where the
         WebView position:fixed flicker was actually visible. */
      transform: translateZ(0);
      width: auto;
    }

    .chatbot-container.chatbot-open {
      left: 16px;
      right: 16px;
      width: calc(100% - 32px);
    }

    /* Hide the looping speech bubble on mobile screens to prevent constant pop-in/pop-out */
    .chatbot-label {
      display: none !important;
    }

    .chatbot-window {
      position: absolute;
      bottom: 80px;
      right: 0;
      width: 100%;
      height: calc(70vh);
      max-height: calc(100vh - (var(--nav-height, 64px) + var(--safe-bottom, 0px) + 32px));
      border-radius: 24px;
      z-index: 99999;
    }

    /* When open on mobile, the window floats above the bottom nav instead of full-screen */
    .chatbot-window.active {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      width: 100%;
      height: calc(70vh);
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

    // Load saved position on desktop only to avoid drag-jump flashes on mobile page load
    if (!isMobile()) {
      const savedPos = localStorage.getItem('chatbotPosition');
      if (savedPos) {
        try {
          const pos = JSON.parse(savedPos);
          xOffset = pos.x;
          yOffset = pos.y;
          container.style.transform = `translate3d(${xOffset}px, ${yOffset}px, 0)`;
        } catch (e) {}
      }
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
      if (!isMobile()) {
        localStorage.setItem('chatbotPosition', JSON.stringify({ x: xOffset, y: yOffset }));
      }
    }

    // Pointer events for desktop and mobile drag support
    toggleBtn.addEventListener('pointerdown', dragStart, { passive: false });
    header.addEventListener('pointerdown', dragStart, { passive: false });
    document.addEventListener('pointermove', drag, { passive: false });
    document.addEventListener('pointerup', dragEnd);
    document.addEventListener('pointercancel', dragEnd);

    // --- Restore Open/Close State Across Refresh & Navigation ---
    const storedState = sessionStorage.getItem('chatbotWindowState');
    if (storedState === 'open' && !navigator.userAgent.includes('SSCStudentApp')) {
      openChatbot(false);
    }

    // --- Toggle chatbot window ---
    toggleBtn.addEventListener('click', (e) => {
      if (hasMoved) {
        hasMoved = false; // Reset for next click
        return;
      }
      if (navigator.userAgent.includes('SSCStudentApp')) {
        e.preventDefault();
        window.location.href = '/student/chatbot-native';
        return;
      }
      const isActive = chatWindow.classList.contains('active');
      isActive ? closeChatbot() : openChatbot();
    });

    function openChatbot(focusInput = true) {
      chatWindow.classList.add('active');
      container.classList.add('chatbot-open');
      toggleIcon.className = 'bi bi-chevron-down';
      sessionStorage.setItem('chatbotWindowState', 'open');
      if (focusInput) setTimeout(() => chatbotInput.focus(), 100);
    }

    function closeChatbot() {
      chatWindow.classList.remove('active');
      container.classList.remove('chatbot-open');
      toggleIcon.className = 'bi bi-robot';
      sessionStorage.setItem('chatbotWindowState', 'closed');
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

    // Where to send a student who wants a human. Swap this for the page's
    // https://m.me/<page-username> link if you have it: that opens a Messenger
    // thread straight away instead of the page itself.
    const SSC_MESSENGER_URL = 'https://web.facebook.com/photo/?fbid=1287391326723459&set=a.467203248742275&__tn__=%3C';
    // After this many questions the assistant stops guessing and points the
    // student at a real officer. Offered once per chat session, not every turn.
    const MESSENGER_AFTER_MESSAGES = 3;
    let userMessageCount = 0;
    let messengerOffered = false;

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

    // Every bot answer goes through here, so the Messenger hand-off is offered no
    // matter which path produced the reply (API, local fallback, or timeout).
    function botReply(text) {
      addMessage(text, 'bot');
      maybeOfferMessenger();
    }

    // Three questions in, the assistant has had a fair go. Rather than keep
    // guessing, hand the student to an officer who can actually answer.
    function maybeOfferMessenger() {
      if (messengerOffered || userMessageCount < MESSENGER_AFTER_MESSAGES) return;
      messengerOffered = true;
      const card = document.createElement('div');
      card.className = 'chat-handoff';
      card.innerHTML =
        '<div class="chat-handoff-head">'
        + '<div class="chat-handoff-icon"><i class="bi bi-chat-dots-fill"></i></div>'
        + '<div><div class="chat-handoff-title">Still need a hand?</div>'
        + '<div class="chat-handoff-sub">Chat with a real SSC officer</div></div>'
        + '</div>'
        + '<div class="chat-handoff-body">Our officers reply to messages on the official '
        + 'SSC Facebook page, so you get a real person instead of me.</div>'
        + '<a class="chat-handoff-btn" href="' + SSC_MESSENGER_URL + '" '
        + 'target="_blank" rel="noopener"><i class="bi bi-messenger"></i> Chat on Messenger</a>';
      messagesContainer.appendChild(card);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function handleSend(overrideText = null) {
      const text = overrideText || chatbotInput.value.trim();
      if (!text) return;

      addMessage(text, 'user');
      userMessageCount++;
      chatbotInput.value = '';

      const typingDiv = document.createElement('div');
      typingDiv.classList.add('chat-msg', 'bot-msg', 'typing-indicator');
      typingDiv.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
      messagesContainer.appendChild(typingDiv);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;

      // Try the Laravel/OpenAI backend first, always falling back to the local
      // rule-based responder so the widget never leaves the user without an answer.
      const chatRoute = "{{ Route::has('student.chatbot.chat') ? route('student.chatbot.chat') : '' }}";
      if (chatRoute) {
        fetchWithTimeout(chatRoute, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ message: text })
        }, 15000)
        .then(res => res.json())
        .then(data => {
          typingDiv.remove();
          if (data.success && data.answer) {
            botReply(data.answer);
          } else {
            botReply(getBotResponse(text.toLowerCase()));
          }
        })
        .catch(() => {
          typingDiv.remove();
          botReply(getBotResponse(text.toLowerCase()));
        });
      } else {
        setTimeout(() => {
          typingDiv.remove();
          botReply(getBotResponse(text.toLowerCase()));
        }, 800);
      }
    }

    // Wraps fetch() with a hard timeout so a stalled network never leaves the
    // "typing..." indicator stuck on screen — it fails fast into the local fallback.
    function fetchWithTimeout(url, options, timeoutMs) {
      const controller = new AbortController();
      const timer = setTimeout(() => controller.abort(), timeoutMs);
      return fetch(url, { ...options, signal: controller.signal }).finally(() => clearTimeout(timer));
    }

    sendBtn.addEventListener('click', () => handleSend());
    chatbotInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') handleSend();
    });

    function getBotResponse(input) {
      const responses = {
        'budget': "Want to track where your student fees go? 📊\n\nWe maintain full transparency of our budget:\n• Visit the <a href='{{ route('student.overview') }}' class='chat-link'>Dashboard</a> to see summary charts of allocated versus spent funds.\n• Check the <a href='{{ route('student.proposals') }}' class='chat-link'>Proposals Portal</a> to review specific project budgets, liquidation logs, and uploaded receipts for completed projects.",
        'dashboard': "Your <a href='{{ route('student.overview') }}' class='chat-link'>Dashboard</a> is your home base. 🏠\n\nIt gives you a quick overview of budget summaries, recent announcements, and your account status the moment you log in.",
        'overview': "Your <a href='{{ route('student.overview') }}' class='chat-link'>Dashboard</a> is your home base. 🏠\n\nIt gives you a quick overview of budget summaries, recent announcements, and your account status the moment you log in.",
        'enroll': "Need to settle your enrollment fee? 💳\n\nHead to the <a href='{{ route('student.enrollment.index') }}' class='chat-link'>Enrollment</a> page on your sidebar to:\n• View your current payment status for this school year.\n• Pay via GCash/bank transfer and upload proof, or wait for admin confirmation of a walk-in payment.\n• Once confirmed, your status updates automatically and you'll be notified.",
        'payment': "Need to settle your enrollment fee? 💳\n\nHead to the <a href='{{ route('student.enrollment.index') }}' class='chat-link'>Enrollment</a> page on your sidebar to:\n• View your current payment status for this school year.\n• Pay via GCash/bank transfer and upload proof, or wait for admin confirmation of a walk-in payment.\n• Once confirmed, your status updates automatically and you'll be notified.",
        'announcement': "Want to stay in the loop? 📰\n\nAll official SSC announcements, project updates, and campus news are posted on the <a href='{{ route('student.announcements') }}' class='chat-link'>Announcements</a> page, accessible from your sidebar.",
        'news': "Want to stay in the loop? 📰\n\nAll official SSC announcements, project updates, and campus news are posted on the <a href='{{ route('student.announcements') }}' class='chat-link'>Announcements</a> page, accessible from your sidebar.",
        'proposal': "Want to submit a project proposal? 📝\n\nStudent organizations and department representatives can request Supreme Student Council (SSC) funding easily:\n1. Navigate to the <a href='{{ route('student.proposals') }}' class='chat-link'>Proposals Portal</a> on your sidebar.\n2. Click the <b>Submit Proposal</b> button and fill in the project title, expected timeline, and estimated budget.\n3. Once submitted, it will appear on the discussions list for student feedback and voting.\n4. The SSC Board will review and vote on official approval.",
        'feedback': "Your voice is essential to build a better campus! 💬\n\nTo share feedback, suggestions, or concerns with the council:\n1. Open the <a href='{{ route('student.feedback') }}' class='chat-link'>Student Feedback Wall</a>.\n2. Write your message and choose the type (Suggestion, Inquiry, or Concern).\n3. Check <b>Submit Anonymously</b> to keep your identity private if preferred.\n4. All submissions are read and addressed directly by the SSC Executive Committee.",
        'contact': "Let's stay connected! 📞\n\nYou can reach the SSC officers through our official channels:\n• <b>Email:</b> <a href='mailto:ssc.official@mcclawis.edu.ph' class='chat-link'>ssc.official@mcclawis.edu.ph</a>\n• <b>Facebook:</b> <a href='https://www.facebook.com/share/17N13YJMUC/' target='_blank' class='chat-link'>SSC Official Page</a>\n• <b>Office:</b> Student Center, 2nd Floor, MCC Campus\n• <b>Office Hours:</b> Mon-Fri | 8:00 AM – 5:00 PM",
        'vote': "Interested in participating in the elections? 🗳️\n\nWhen voting is active, you can cast your secure ballot in 3 simple steps:\n1. Open the <a href='{{ route('student.voting') }}' class='chat-link'>Voting Portal</a> on your sidebar.\n2. Review candidate platform and position details.\n3. Select your preferred candidates and tap the <b>Cast Ballot</b> button to safely record your vote.",
        'voting': "Interested in participating in the elections? 🗳️\n\nWhen voting is active, you can cast your secure ballot in 3 simple steps:\n1. Open the <a href='{{ route('student.voting') }}' class='chat-link'>Voting Portal</a> on your sidebar.\n2. Review candidate platform and position details.\n3. Select your preferred candidates and tap the <b>Cast Ballot</b> button to safely record your vote.",
        'candidacy': "Are you running for office? 🚀\n\nStudents can file for official candidacy through our platform:\n1. Visit the <a href='{{ route('student.candidacy') }}' class='chat-link'>Candidacy Portal</a>.\n2. Select your desired role and enter your campaign platform details.\n3. Note that eligibility is limited by department restrictions and active election timelines set by the administration.",
        'hello': "Hi there! 👋 I'm your SSC assistant. I can help you with student concerns, proposals, anonymous feedback, and budget tracking. What can I do for you today?",
        'hi': "Hello! 🌟 Hope you're having a good day. Need help with project proposals, tracking budgets, or posting feedback?",
        'thanks': "You're very welcome! Let me know if there's anything else I can do to help you navigate the system. 🚀",
        'thank': "Anytime! Stay awesome. Let me know if you have other student concerns!",
        'location': "Our campus and the SSC Office are located at:<br>📍 <b>Madridejos Community College (MCC)</b><br>Bunakan, Madridejos, Cebu, Philippines.<br><br>🏢 <b>SSC Office Location:</b> Student Center, 2nd Floor, MCC Campus.<br><br>🗺️ <b>Google Maps Location:</b><br><div class='map-container mb-2' style='width:100%; height:160px; border-radius:8px; overflow:hidden; border:1px solid #cbd5e1;'><iframe src='https://maps.google.com/maps?q=Madridejos%20Community%20College,%20Cebu,%20Philippines&t=&z=15&ie=UTF8&iwloc=&output=embed' width='100%' height='100%' style='border:0;' allowfullscreen='' loading='lazy' referrerpolicy='no-referrer-when-downgrade'></iframe></div><a href='https://maps.google.com/maps?q=Madridejos%20Community%20College,%20Cebu,%20Philippines' target='_blank' class='chat-link'><i class='bi bi-box-arrow-up-right'></i> Open in Google Maps</a>"
      };

      for (const key in responses) {
        if (input.includes(key)) return responses[key];
      }

      return "I'm sorry, I don't have a specific answer for that. \n\nTry asking about: \n• proposals \n• anonymous feedback \n• track budgets \n• contact ssc \n• voting \n• candidacy";
    }
  });
</script>
