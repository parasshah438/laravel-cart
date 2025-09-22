<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Chat Support - Laravel Cart</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 50%, #CDDC39 100%);
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(76, 175, 80, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 195, 74, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(205, 220, 57, 0.2) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            height: calc(100vh - 100px);
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px 20px 0 0;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .chat-messages {
            background: rgba(255, 255, 255, 0.95);
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            max-height: 500px;
            font-family: 'Inter', sans-serif;
        }

        /* Force normal text display */
        .chat-messages span {
            display: inline !important;
            white-space: normal !important;
            word-break: keep-all !important;
            font-family: inherit !important;
        }

        .chat-input {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 0 0 20px 20px;
            padding: 20px;
            border-top: 1px solid #dee2e6;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .message.user-message {
            justify-content: flex-end;
        }

        .message.agent-message {
            justify-content: flex-start;
        }

        .message.system-message {
            justify-content: center;
        }

        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
            word-break: normal;
            white-space: normal;
            overflow-wrap: break-word;
            display: block !important;
            font-family: inherit;
            line-height: 1.4;
        }

        .message-bubble * {
            display: inline !important;
            white-space: normal !important;
        }

        .user-message .message-bubble {
            background: linear-gradient(45deg, #4CAF50, #66BB6A);
            color: white;
            border-bottom-right-radius: 6px;
        }

        .agent-message .message-bubble {
            background: #f1f3f4;
            color: #333;
            border-bottom-left-radius: 6px;
        }

        .system-message .message-bubble {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            font-style: italic;
            text-align: center;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .message-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 4px;
        }

        .user-message .message-time {
            text-align: right;
        }

        .agent-message .message-time {
            text-align: left;
        }

        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            margin: 0 10px;
            flex-shrink: 0;
        }

        .user-avatar {
            background: linear-gradient(45deg, #4CAF50, #66BB6A);
        }

        .agent-avatar {
            background: linear-gradient(45deg, #2196F3, #42A5F5);
        }

        .chat-input-group {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .chat-input-field {
            flex: 1;
            border-radius: 25px;
            border: 1px solid #ddd;
            padding: 12px 20px;
            resize: none;
            min-height: 45px;
            max-height: 120px;
        }

        .chat-input-field:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .send-button {
            background: linear-gradient(45deg, #4CAF50, #66BB6A);
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .send-button:hover {
            background: linear-gradient(45deg, #388E3C, #4CAF50);
            transform: scale(1.05);
        }

        .send-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .online-indicator {
            width: 12px;
            height: 12px;
            background: #28a745;
            border-radius: 50%;
            border: 2px solid white;
            position: absolute;
            bottom: 2px;
            right: 2px;
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #f1f3f4;
            border-radius: 18px;
            margin-bottom: 15px;
            max-width: 70%;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #6c757d;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes typing {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            padding: 10px 20px;
        }

        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: white;
        }

        .chat-status {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.2);
            border-radius: 10px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }

        .end-chat-btn {
            background: #dc3545;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            color: white;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .end-chat-btn:hover {
            background: #c82333;
            color: white;
        }

        .file-attachment {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.2);
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 8px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('support.index') }}">
                        <i class="fas fa-home me-1"></i> Support Center
                    </a>
                </li>
                <li class="breadcrumb-item active">Live Chat</li>
            </ol>
        </nav>

        <div class="chat-container">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="position-relative me-3">
                            <div class="agent-avatar">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="online-indicator"></div>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Support Team</h5>
                            @if($activeChat && $activeChat->agent_name)
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-circle text-success me-1" style="font-size: 0.5rem;"></i>
                                    {{ $activeChat->agent_name }} is online
                                </p>
                            @else
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-circle text-success me-1" style="font-size: 0.5rem;"></i>
                                    Online - Available for chat
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    @if($activeChat)
                    <div class="d-flex align-items-center gap-3">
                        <small class="text-muted">
                            Session: {{ $activeChat->created_at->format('M j, g:i A') }}
                        </small>
                        <button type="button" class="end-chat-btn" data-bs-toggle="modal" data-bs-target="#endChatModal">
                            <i class="fas fa-times me-1"></i> End Chat
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessages">
                @if(!$activeChat)
                <!-- Welcome Message -->
                <div class="message system-message">
                    <div class="message-bubble">
                        <i class="fas fa-robot me-2"></i>
                        Welcome to Live Chat Support! An agent will be with you shortly.
                    </div>
                </div>
                @else
                    <!-- Chat Session Messages -->
                    <div class="message system-message">
                        <div class="message-bubble">
                            Chat session started at {{ $activeChat->created_at->format('M j, Y g:i A') }}
                        </div>
                    </div>

                    @foreach($activeChat->messages->sortBy('created_at') as $message)
                        @if($message->is_system)
                            <div class="message system-message">
                                <div class="message-bubble">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ $message->message }}
                                </div>
                            </div>
                        @else
                            <div class="message {{ $message->is_from_agent ? 'agent-message' : 'user-message' }}">
                                @if($message->is_from_agent)
                                    <div class="agent-avatar">
                                        {{ $message->user ? substr($message->user->name, 0, 1) : 'A' }}
                                    </div>
                                @endif
                                
                                <div>
                                    <div class="message-bubble">
                                        {{ $message->message }}
                                        @if($message->attachment)
                                            <div class="file-attachment">
                                                <i class="fas fa-paperclip me-1"></i>
                                                <a href="#" class="text-decoration-none">{{ basename($message->attachment) }}</a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="message-time">
                                        {{ $message->created_at->format('g:i A') }}
                                    </div>
                                </div>

                                @if(!$message->is_from_agent)
                                    <div class="user-avatar">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    <!-- Typing Indicator (shown when agent is typing) -->
                    <div class="typing-indicator" id="typingIndicator" style="display: none;">
                        <div class="agent-avatar me-2">
                            {{ substr($activeChat->agent_name ?? 'A', 0, 1) }}
                        </div>
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span class="ms-2 text-muted small">Agent is typing...</span>
                    </div>
                @endif
            </div>

            <!-- Chat Input -->
            <div class="chat-input">
                @if(!$activeChat)
                    <!-- Start Chat Form -->
                    <form action="{{ route('support.chat.start') }}" method="POST" id="startChatForm">
                        @csrf
                        <div class="chat-status text-center">
                            <h6 class="fw-semibold text-success mb-2">
                                <i class="fas fa-comments me-2"></i>Start Live Chat
                            </h6>
                            <p class="text-muted small mb-3">
                                Connect with our support team for real-time assistance
                            </p>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-play me-2"></i>Start Chat Session
                            </button>
                        </div>
                    </form>
                @else
                    <!-- Message Input -->
                    <form action="{{ route('support.chat.send', $activeChat) }}" method="POST" enctype="multipart/form-data" id="messageForm" onsubmit="return false;">
                        @csrf
                        <input type="hidden" name="chat_id" value="{{ $activeChat->id }}">
                        <div class="chat-input-group">
                            <textarea 
                                class="form-control chat-input-field" 
                                name="message" 
                                id="messageInput" 
                                placeholder="Type your message..."
                                rows="1"
                                required
                                maxlength="1000"
                            ></textarea>
                            <input type="file" id="fileInput" name="attachment" style="display: none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <button type="submit" class="send-button" id="sendButton">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="selectedFile" class="mt-2" style="display: none;">
                            <small class="text-muted">
                                <i class="fas fa-paperclip me-1"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2" onclick="clearFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </small>
                        </div>
                        <!-- Character Count -->
                        <div class="text-end mt-2">
                            <span id="charCount" class="small text-muted">0/1000</span>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if($activeChat)
    <!-- End Chat Modal -->
    <div class="modal fade" id="endChatModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">End Chat Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to end this chat session?</p>
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rate your experience (optional)</label>
                        <select class="form-select" id="rating" name="rating">
                            <option value="">Select rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Very Poor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Feedback (optional)</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="3" placeholder="How was your chat experience?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Chat</button>
                    <form action="{{ route('support.chat.end', $activeChat) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="rating" id="modalRating">
                        <input type="hidden" name="feedback" id="modalFeedback">
                        <button type="submit" class="btn btn-danger">End Chat</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-resize textarea
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Send message on Enter (not Shift+Enter)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.getElementById('messageForm').submit();
                }
            });
        }

        // File upload handling
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    document.getElementById('fileName').textContent = file.name;
                    document.getElementById('selectedFile').style.display = 'block';
                }
            });
        }

        function clearFile() {
            document.getElementById('fileInput').value = '';
            document.getElementById('selectedFile').style.display = 'none';
        }

        // Auto-scroll to bottom
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Transfer modal values
        document.getElementById('endChatModal')?.addEventListener('show.bs.modal', function() {
            const rating = document.getElementById('rating').value;
            const feedback = document.getElementById('feedback').value;
            document.getElementById('modalRating').value = rating;
            document.getElementById('modalFeedback').value = feedback;
        });

        // Update modal values on change
        document.getElementById('rating')?.addEventListener('change', function() {
            document.getElementById('modalRating').value = this.value;
        });

        document.getElementById('feedback')?.addEventListener('input', function() {
            document.getElementById('modalFeedback').value = this.value;
        });

        // Scroll to bottom on page load
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
        });

        // Simulate real-time chat updates (in a real app, use WebSockets or polling)
        @if($activeChat)
        setInterval(function() {
            // In a real implementation, this would check for new messages
            // and update the chat interface accordingly
        }, 3000);
        @endif
    </script>
</body>
</html>
                    {{ $activeChat->formatted_status }}
                </span>
                @if($activeChat->agent)
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                        Agent: {{ $activeChat->agent->name }}
                    </span>
                @endif
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Chat JS started');
            
            // Get form elements with correct IDs
            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            const charCount = document.getElementById('charCount');
            const chatMessages = document.getElementById('chatMessages');
            
            // Debug: Check if elements exist
            console.log('Message form element:', messageForm);
            console.log('Message input element:', messageInput);
            console.log('Send button element:', sendButton);
            console.log('Char count element:', charCount);
            console.log('Chat messages element:', chatMessages);
            
            if (!messageForm) {
                console.error('Message form not found!');
                return;
            }
            
            // Auto-resize textarea
            if (messageInput) {
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                    
                    // Update character count
                    if (charCount) {
                        const count = this.value.length;
                        charCount.textContent = `${count}/1000`;
                        
                        if (count > 950) {
                            charCount.classList.add('text-danger');
                        } else {
                            charCount.classList.remove('text-danger');
                        }
                    }
                });

                // Send message on Enter (not Shift+Enter)
                messageInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        messageForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                    }
                });
            }
            
            // Handle form submission
            if (messageForm) {
                messageForm.addEventListener('submit', function(e) {
                    console.log('Form submit event triggered');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const message = messageInput.value.trim();
                    console.log('Message value:', message);
                    if (!message) {
                        console.log('Empty message, returning');
                        return false;
                    }
                    
                    // Debug: Log form data
                    const formData = new FormData(this);
                    console.log('Form data being sent:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }
                    
                    // Disable form while sending
                    sendButton.disabled = true;
                    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    messageInput.disabled = true;
                    
                    // Send message via AJAX
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        console.log('Message content:', data.message.message);
                        console.log('Message type:', typeof data.message.message);
                        console.log('Message length:', data.message.message.length);
                        if (data.success) {
                            // Add message to chat
                            addMessageToChat(data.message);
                            
                            // Clear input
                            messageInput.value = '';
                            messageInput.style.height = 'auto';
                            if (charCount) {
                                charCount.textContent = '0/1000';
                            }
                            
                            // Scroll to bottom
                            scrollToBottom();
                        } else {
                            console.error('Server error:', data);
                            alert(data.error || 'Failed to send message');
                            if (data.details) {
                                console.error('Validation details:', data.details);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Network/JS Error:', error);
                        alert('Failed to send message. Please try again.');
                    })
                    .finally(() => {
                        // Re-enable form
                        sendButton.disabled = false;
                        sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
                        messageInput.disabled = false;
                        messageInput.focus();
                    });
                    
                    return false;
                });
            }
            
            // Also add click event listener to send button as backup
            if (sendButton) {
                sendButton.addEventListener('click', function(e) {
                    console.log('Send button clicked');
                    e.preventDefault();
                    
                    // Trigger form submission programmatically
                    const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                    messageForm.dispatchEvent(submitEvent);
                    
                    return false;
                });
            }
            
            // File upload handling
            const fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        document.getElementById('fileName').textContent = file.name;
                        document.getElementById('selectedFile').style.display = 'block';
                    }
                });
            }

            // Auto-scroll to bottom
            function scrollToBottom() {
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }

            // Add message to chat UI
            function addMessageToChat(message) {
                if (!chatMessages) return;
                
                console.log('Adding message:', message.message);
                
                // Create proper message structure that matches the Blade template
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message user-message';
                
                // Create message content div
                const contentDiv = document.createElement('div');
                
                // Create message bubble
                const bubbleDiv = document.createElement('div');
                bubbleDiv.className = 'message-bubble';
                bubbleDiv.innerHTML = message.message;
                
                // Create time div
                const timeDiv = document.createElement('div');
                timeDiv.className = 'message-time';
                timeDiv.textContent = message.created_at || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                // Create user avatar
                const avatarDiv = document.createElement('div');
                avatarDiv.className = 'user-avatar';
                avatarDiv.textContent = message.sender_name ? message.sender_name.charAt(0) : 'U';
                
                // Assemble the message structure
                contentDiv.appendChild(bubbleDiv);
                contentDiv.appendChild(timeDiv);
                messageDiv.appendChild(contentDiv);
                messageDiv.appendChild(avatarDiv);
                
                // Add to chat
                chatMessages.appendChild(messageDiv);
                
                console.log('Message added successfully');
            }

            // Transfer modal values
            const endChatModal = document.getElementById('endChatModal');
            if (endChatModal) {
                endChatModal.addEventListener('show.bs.modal', function() {
                    const rating = document.getElementById('rating').value;
                    const feedback = document.getElementById('feedback').value;
                    document.getElementById('modalRating').value = rating;
                    document.getElementById('modalFeedback').value = feedback;
                });

                // Update modal values on change
                const ratingSelect = document.getElementById('rating');
                const feedbackTextarea = document.getElementById('feedback');
                
                if (ratingSelect) {
                    ratingSelect.addEventListener('change', function() {
                        document.getElementById('modalRating').value = this.value;
                    });
                }
                
                if (feedbackTextarea) {
                    feedbackTextarea.addEventListener('input', function() {
                        document.getElementById('modalFeedback').value = this.value;
                    });
                }
            }

            // Scroll to bottom on page load
            scrollToBottom();

            // Simulate real-time chat updates (in a real app, use WebSockets or polling)
            @if($activeChat)
            setInterval(function() {
                // In a real implementation, this would check for new messages
                // and update the chat interface accordingly
            }, 3000);
            @endif
        });

        function clearFile() {
            document.getElementById('fileInput').value = '';
            document.getElementById('selectedFile').style.display = 'none';
        }
    </script>
</body>
</html>
