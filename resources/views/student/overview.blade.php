@extends('layouts.app')

@section('sidebar-nav')
    <a href="{{ route('student.overview') }}" class="nav-item active">
        <i class="bi bi-house-door"></i>
        <span>Overview</span>
    </a>
    <a href="{{ route('student.proposals') }}" class="nav-item">
        <i class="bi bi-file-text"></i>
        <span>Proposals</span>
    </a>
    <a href="{{ route('student.announcements') }}" class="nav-item">
        <i class="bi bi-megaphone"></i>
        <span>Announcements</span>
    </a>
    <a href="{{ route('student.officers') }}" class="nav-item">
        <i class="bi bi-people"></i>
        <span>Officers</span>
    </a>
    <a href="{{ route('student.feedback') }}" class="nav-item">
        <i class="bi bi-chat-dots"></i>
        <span>Feedback</span>
    </a>
    <a href="{{ route('student.voting') }}" class="nav-item">
        <i class="bi bi-ballot"></i>
        <span>Voting</span>
    </a>
    <a href="{{ route('student.candidacy') }}" class="nav-item">
        <i class="bi bi-award"></i>
        <span>Candidacy</span>
    </a>
    <a href="{{ route('student.enrollment.index') }}" class="nav-item">
        <i class="bi bi-cash-stack"></i>
        <span>Enrollment</span>
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Welcome Card -->
            <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4">
                    <h2 class="card-title mb-2">Welcome back, {{ Auth::user()->fullname }}! 👋</h2>
                    <p class="card-text mb-0">Stay updated with the latest announcements, proposals, and SSC activities.</p>
                </div>
            </div>

            <!-- Announcements Section -->
            @if($announcements->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center" style="border-radius: var(--radius-md) var(--radius-md) 0 0;">
                    <h5 class="mb-0"><i class="bi bi-megaphone text-warning me-2"></i>Latest Announcements</h5>
                    <a href="{{ route('student.announcements') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @foreach($announcements as $announcement)
                    <div class="p-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                        <h6 class="mb-1 fw-600">{{ $announcement->title }}</h6>
                        <p class="text-muted small mb-2">{{ Str::limit($announcement->content, 100) }}</p>
                        <small class="text-muted">
                            <i class="bi bi-calendar2"></i> {{ $announcement->created_at->format('M d, Y') }}
                            @if($announcement->officer)
                            • By {{ $announcement->officer->fullname }}
                            @endif
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Proposals Section -->
            @if($pendingProposals->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text text-info me-2"></i>Active Proposals</h5>
                    <a href="{{ route('student.proposals') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @foreach($pendingProposals as $proposal)
                    <div class="p-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-600">
                                    <a href="{{ route('student.proposal.show', $proposal) }}" class="text-decoration-none">
                                        {{ $proposal->title }}
                                    </a>
                                </h6>
                                <p class="text-muted small mb-2">{{ Str::limit($proposal->description, 80) }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-calendar2"></i> {{ $proposal->created_at->format('M d, Y') }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $proposal->status === 'Approved' ? 'success' : 'warning' }} ms-2">
                                {{ $proposal->status }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Candidacy Status -->
            @if($activeSy)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-award text-success me-2"></i>Candidacy Status</h5>
                </div>
                <div class="card-body">
                    @if($activeCandidacy)
                        <div class="alert alert-info mb-0 d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Active Application:</strong> {{ $activeCandidacy->position }}
                                <br>
                                <small>Status: <span class="badge bg-{{ $activeCandidacy->status === 'approved' ? 'success' : ($activeCandidacy->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($activeCandidacy->status) }}</span></small>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">You haven't submitted a candidacy application for the current school year.</p>
                        @if($activeSy->candidacy_open)
                        <a href="{{ route('student.candidacy') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-plus"></i> Apply for Candidacy
                        </a>
                        @endif
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Chatbot Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm position-sticky" id="chatCard" style="top: 20px; max-height: 85vh;">
                <div class="card-header bg-primary text-white d-flex align-items-center border-0">
                    <i class="bi bi-robot me-2"></i>
                    <h5 class="mb-0">AI Assistant</h5>
                </div>
                <div class="card-body p-0 d-flex flex-column" style="min-height: 400px; max-height: 70vh;">
                    <!-- Chat Messages Area -->
                    <div id="chatMessages" class="flex-grow-1 overflow-y-auto p-3" style="background-color: #f8f9fa;">
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-chat-left-text" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="mt-2 small">Hello! 👋 Ask me anything about the SSC system, voting, candidacy, budget, or other student activities.</p>
                        </div>
                    </div>

                    <!-- Chat Input Area -->
                    <div class="p-3 border-top bg-light">
                        <form id="chatForm" onsubmit="sendMessage(event)" class="d-flex gap-2">
                            <input 
                                type="text" 
                                id="chatInput" 
                                class="form-control form-control-sm" 
                                placeholder="Ask a question..." 
                                autocomplete="off"
                                maxlength="2000"
                            >
                            <button type="submit" class="btn btn-primary btn-sm" id="sendBtn">
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                        <small class="text-muted mt-2 d-block">
                            <i class="bi bi-info-circle"></i> I can answer questions about voting, candidacy, budget, announcements, and more.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <button id="mobileChatToggle" class="mobile-chat-toggle d-lg-none" aria-label="Open chat"><i class="bi bi-chat-dots" style="font-size:1.25rem;color:white"></i></button>

<style>
    .card {
        border-radius: var(--radius-md, 8px);
    }
    
    .nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        text-decoration: none;
        color: #666;
        border-radius: 6px;
        margin-bottom: 5px;
        transition: all 0.2s;
    }
    
    .nav-item:hover,
    .nav-item.active {
        background-color: #f0f4ff;
        color: #667eea;
    }
    
    #chatMessages {
        display: flex;
        flex-direction: column;
    }
    
    .chat-message {
        display: flex;
        margin-bottom: 12px;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .message-bubble {
        max-width: 85%;
        padding: 10px 14px;
        border-radius: 12px;
        word-wrap: break-word;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    .user-message {
        justify-content: flex-end;
    }
    
    .user-message .message-bubble {
        background-color: #667eea;
        color: white;
        border-bottom-right-radius: 4px;
    }
    
    .bot-message {
        justify-content: flex-start;
    }
    
    .bot-message .message-bubble {
        background-color: white;
        color: #333;
        border: 1px solid #e0e0e0;
        border-bottom-left-radius: 4px;
    }
    
    .loading-dots {
        display: inline-flex;
        gap: 4px;
    }
    
    .loading-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #999;
        animation: bounce 1.4s ease-in-out infinite;
    }
    
    .loading-dots span:nth-child(2) {
        animation-delay: 0.2s;
    }
    
    .loading-dots span:nth-child(3) {
        animation-delay: 0.4s;
    }
    
    @keyframes bounce {
        0%, 80%, 100% {
            opacity: 0.6;
            transform: translateY(0);
        }
        40% {
            opacity: 1;
            transform: translateY(-6px);
        }
    }

    /* Mobile styles: make chat a bottom drawer */
    #mobileChatToggle {
        display: none;
    }

    .mobile-chat-toggle {
        position: fixed;
        bottom: 18px;
        right: 18px;
        z-index: 1100;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        border: none;
    }

    @media (max-width: 767.98px) {
        /* Make the chat card fixed bottom and hidden by default */
        #chatCard.position-sticky {
            position: fixed !important;
            left: 12px;
            right: 12px;
            bottom: 0;
            top: auto !important;
            margin: 0;
            max-height: 70vh;
            border-radius: 12px 12px 0 0;
            transform: translateY(100%);
            transition: transform 0.28s ease;
            z-index: 1090;
        }

        #chatCard.position-sticky.chat-open {
            transform: translateY(0);
        }

        /* Make the floating toggle visible on mobile */
        .mobile-chat-toggle {
            display: inline-flex;
        }

        /* Smaller message bubbles on mobile */
        .message-bubble {
            max-width: 100%;
            font-size: 0.92rem;
        }
    }
</style>

<script>
const chatMessagesContainer = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const chatForm = document.getElementById('chatForm');
const sendBtn = document.getElementById('sendBtn');

function addMessageToChat(message, isUser) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'}`;
    
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    bubble.textContent = message;
    
    messageDiv.appendChild(bubble);
    chatMessagesContainer.appendChild(messageDiv);
    
    // Scroll to bottom
    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
}

function showLoadingState() {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message bot-message';
    messageDiv.id = 'loadingMessage';
    
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    bubble.innerHTML = '<div class="loading-dots"><span></span><span></span><span></span></div>';
    
    messageDiv.appendChild(bubble);
    chatMessagesContainer.appendChild(messageDiv);
    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
}

function removeLoadingState() {
    const loadingMessage = document.getElementById('loadingMessage');
    if (loadingMessage) {
        loadingMessage.remove();
    }
}

async function sendMessage(event) {
    event.preventDefault();
    
    const message = chatInput.value.trim();
    if (!message) return;
    
    // Add user message to chat
    addMessageToChat(message, true);
    chatInput.value = '';
    
    // Show loading state
    showLoadingState();
    sendBtn.disabled = true;
    
    try {
        const response = await fetch('{{ route("student.chatbot.chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message: message })
        });
        
        const data = await response.json();
        
        removeLoadingState();
        
        if (data.success) {
            addMessageToChat(data.answer, false);
        } else {
            addMessageToChat('Sorry, I encountered an error. Please try again later.', false);
        }
    } catch (error) {
        removeLoadingState();
        console.error('Error:', error);
        addMessageToChat('Sorry, I encountered an error. Please try again later.', false);
    } finally {
        sendBtn.disabled = false;
        chatInput.focus();
    }
}

document.addEventListener('DOMContentLoaded', function() {
// Set focus to input on load
document.addEventListener('DOMContentLoaded', function() {
    chatInput.focus();

    // Mobile chat toggle
    const mobileToggle = document.getElementById('mobileChatToggle');
    const chatCard = document.getElementById('chatCard');
    if (mobileToggle && chatCard) {
        mobileToggle.addEventListener('click', function () {
            chatCard.classList.toggle('chat-open');
            // Focus input when opening
            if (chatCard.classList.contains('chat-open')) {
                setTimeout(() => chatInput.focus(), 200);
                mobileToggle.style.display = 'none';
            } else {
                mobileToggle.style.display = '';
            }
        });

        // Close when clicking outside the chat area on mobile
        document.addEventListener('click', function (e) {
            if (!chatCard.classList.contains('chat-open')) return;
            const isClickInside = chatCard.contains(e.target) || mobileToggle.contains(e.target);
            if (!isClickInside) {
                chatCard.classList.remove('chat-open');
                mobileToggle.style.display = '';
            }
        });
    }
});
</script>
@endsection
