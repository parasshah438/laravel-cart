<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Chat with {{ $chat->user->name }} - Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .chat-container {
            max-width: 900px;
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

        .message.customer-message {
            justify-content: flex-start;
        }

        .message.agent-message {
            justify-content: flex-end;
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
            overflow-wrap: break-word;
            white-space: pre-wrap;
            line-height: 1.4;
        }
        
        /* Ensure proper text display for all message bubbles */
        .message-bubble, .message-bubble * {
            font-family: 'Inter', sans-serif !important;
            letter-spacing: normal !important;
            word-spacing: normal !important;
            white-space: normal !important;
        }
        
        /* Override any inherited display issues */
        .chat-messages .message-bubble {
            display: block !important;
        }

        .customer-message .message-bubble {
            background: #f1f3f4;
            color: #333;
            border-bottom-left-radius: 6px;
        }

        .agent-message .message-bubble {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-bottom-right-radius: 6px;
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

        .customer-message .message-time {
            text-align: left;
        }

        .agent-message .message-time {
            text-align: right;
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

        .customer-avatar {
            background: linear-gradient(45deg, #28a745, #20c997);
        }

        .agent-avatar {
            background: linear-gradient(45deg, #667eea, #764ba2);
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .send-button {
            background: linear-gradient(45deg, #667eea, #764ba2);
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
            background: linear-gradient(45deg, #5a6fd8, #6c42a0);
            transform: scale(1.05);
        }

        .send-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0" style="background: rgba(255, 255, 255, 0.1); border-radius: 50px; padding: 10px 20px;">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.support.chats') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">
                        <i class="fas fa-comments me-1"></i> Live Chats
                    </a>
                </li>
                <li class="breadcrumb-item active" style="color: white;">
                    Chat with {{ $chat->user->name }}
                </li>
            </ol>
        </nav>

        <div class="chat-container">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="position-relative me-3">
                            <div class="customer-avatar">
                                {{ substr($chat->user->name, 0, 1) }}
                            </div>
                            <div style="width: 12px; height: 12px; background: #28a745; border-radius: 50%; border: 2px solid white; position: absolute; bottom: 2px; right: 2px;"></div>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $chat->user->name }}</h5>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-envelope me-1"></i>
                                {{ $chat->user->email }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge status-badge {{ $chat->status === 'active' ? 'bg-success' : ($chat->status === 'waiting' ? 'bg-warning' : 'bg-secondary') }}">
                            {{ ucfirst($chat->status) }}
                        </span>
                        <small class="text-muted">
                            Started: {{ $chat->created_at->format('M j, g:i A') }}
                        </small>
                        @if($chat->status !== 'ended')
                        <form action="{{ route('admin.support.chats.end', $chat) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-times me-1"></i> End Chat
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessages">
                <!-- Welcome Message -->
                <div class="message system-message">
                    <div class="message-bubble">
                        <i class="fas fa-info-circle me-2"></i>
                        Chat session started at {{ $chat->created_at->format('M j, Y g:i A') }}
                    </div>
                </div>

                @foreach($chat->messages->sortBy('created_at') as $message)
                    @if($message->message_type === 'system')
                        <div class="message system-message">
                            <div class="message-bubble">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ $message->message }}
                            </div>
                        </div>
                    @else
                        <div class="message {{ $message->is_from_agent ? 'agent-message' : 'customer-message' }}">
                            @if(!$message->is_from_agent)
                                <div class="customer-avatar">
                                    {{ substr($chat->user->name, 0, 1) }}
                                </div>
                            @endif
                            
                            <div>
                                <div class="message-bubble">
                                    {{ $message->message }}
                                </div>
                                <div class="message-time">
                                    {{ $message->created_at->format('g:i A') }}
                                </div>
                            </div>

                            @if($message->is_from_agent)
                                <div class="agent-avatar">
                                    {{ $message->user ? substr($message->user->name, 0, 1) : 'A' }}
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Chat Input -->
            <div class="chat-input">
                @if($chat->status !== 'ended')
                    <!-- Agent Message Input -->
                    <form action="{{ route('admin.support.chats.message', $chat) }}" method="POST" id="agentMessageForm" onsubmit="return false;">
                        @csrf
                        <div class="chat-input-group">
                            <textarea 
                                class="form-control chat-input-field" 
                                name="message" 
                                id="agentMessageInput" 
                                placeholder="Type your message to help the customer..."
                                rows="1"
                                required
                                maxlength="1000"
                            ></textarea>
                            <button type="submit" class="send-button" id="agentSendButton">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <!-- Character Count -->
                        <div class="text-end mt-2">
                            <span id="agentCharCount" class="small text-muted">0/1000</span>
                        </div>
                    </form>
                @else
                    <div class="text-center py-3">
                        <p class="text-muted mb-0">
                            <i class="fas fa-lock me-2"></i>
                            This chat session has ended.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin Chat JS started');
            
            // Get form elements
            const agentMessageForm = document.getElementById('agentMessageForm');
            const agentMessageInput = document.getElementById('agentMessageInput');
            const agentSendButton = document.getElementById('agentSendButton');
            const agentCharCount = document.getElementById('agentCharCount');
            const chatMessages = document.getElementById('chatMessages');
            
            if (!agentMessageForm) {
                console.log('No message form found - chat may be ended');
                return;
            }
            
            // Auto-resize textarea
            if (agentMessageInput) {
                agentMessageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                    
                    // Update character count
                    if (agentCharCount) {
                        const count = this.value.length;
                        agentCharCount.textContent = `${count}/1000`;
                        
                        if (count > 950) {
                            agentCharCount.classList.add('text-danger');
                        } else {
                            agentCharCount.classList.remove('text-danger');
                        }
                    }
                });

                // Send message on Enter (not Shift+Enter)
                agentMessageInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        agentMessageForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                    }
                });
            }
            
            // Handle form submission
            if (agentMessageForm) {
                agentMessageForm.addEventListener('submit', function(e) {
                    console.log('Agent form submit event triggered');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const message = agentMessageInput.value.trim();
                    console.log('Agent message value:', message);
                    if (!message) {
                        console.log('Empty message, returning');
                        return false;
                    }
                    
                    // Disable form while sending
                    agentSendButton.disabled = true;
                    agentSendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    agentMessageInput.disabled = true;
                    
                    // Send message via AJAX
                    const formData = new FormData(this);
                    
                    // Explicitly add the message value to ensure it's included
                    formData.set('message', agentMessageInput.value.trim());
                    
                    // Debug: Log form data
                    console.log('Form data being sent:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }
                    
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
                        if (data.success) {
                            // Add message to chat
                            addAgentMessageToChat(data.message);
                            
                            // Clear input
                            agentMessageInput.value = '';
                            agentMessageInput.style.height = 'auto';
                            if (agentCharCount) {
                                agentCharCount.textContent = '0/1000';
                            }
                            
                            // Scroll to bottom
                            scrollToBottom();
                        } else {
                            console.error('Server error:', data);
                            alert(data.error || 'Failed to send message');
                        }
                    })
                    .catch(error => {
                        console.error('Network/JS Error:', error);
                        alert('Failed to send message. Please try again.');
                    })
                    .finally(() => {
                        // Re-enable form
                        agentSendButton.disabled = false;
                        agentSendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
                        agentMessageInput.disabled = false;
                        agentMessageInput.focus();
                    });
                    
                    return false;
                });
            }
            
            // Auto-scroll to bottom
            function scrollToBottom() {
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }

            // Add agent message to chat UI
            function addAgentMessageToChat(message) {
                if (!chatMessages) return;
                
                console.log('Adding agent message:', message.message);
                
                // Create message container that matches the existing structure
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message agent-message';
                
                // Create message content div
                const contentDiv = document.createElement('div');
                
                // Create message bubble with explicit text styling
                const bubbleDiv = document.createElement('div');
                bubbleDiv.className = 'message-bubble';
                bubbleDiv.style.cssText = `
                    font-family: 'Inter', sans-serif !important;
                    letter-spacing: normal !important;
                    word-spacing: normal !important;
                    white-space: normal !important;
                    display: block !important;
                `;
                bubbleDiv.textContent = message.message; // Use textContent to avoid any HTML parsing issues
                
                // Create time div
                const timeDiv = document.createElement('div');
                timeDiv.className = 'message-time';
                timeDiv.textContent = message.created_at;
                
                // Create avatar
                const avatarDiv = document.createElement('div');
                avatarDiv.className = 'agent-avatar';
                avatarDiv.textContent = message.sender_name ? message.sender_name.charAt(0) : 'A';
                
                // Assemble the message structure
                contentDiv.appendChild(bubbleDiv);
                contentDiv.appendChild(timeDiv);
                messageDiv.appendChild(contentDiv);
                messageDiv.appendChild(avatarDiv);
                
                // Add to chat
                chatMessages.appendChild(messageDiv);
                
                console.log('Agent message added successfully');
            }

            // Scroll to bottom on page load
            scrollToBottom();

            // Auto-refresh to check for new customer messages
            setInterval(function() {
                // You could implement message polling here
                // For now, we'll keep it simple
            }, 5000);
        });
    </script>
</body>
</html>