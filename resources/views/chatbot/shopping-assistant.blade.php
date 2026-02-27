<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap 5 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootbox@5.5.2/bootbox.min.js"></script>

    
<style>
#chatMessages::-webkit-scrollbar {
    width: 6px;
}

#chatMessages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#chatMessages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#chatMessages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.user-message {
    animation: slideInRight 0.3s ease-out;
}

.bot-message {
    animation: slideInLeft 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

</head>
<body>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">🤖 AI Shopping Assistant</h1>
            <p class="text-lg text-gray-600">Get personalized product recommendations and intelligent shopping guidance</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Chat Interface -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg">
                    <!-- Chat Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 rounded-t-lg">
                        <h2 class="text-xl font-semibold flex items-center">
                            <span class="mr-2">💬</span>
                            Chat with AI Assistant
                        </h2>
                        <p class="text-blue-100 text-sm">Ask me anything about products, get recommendations, or check your orders</p>
                    </div>

                    <!-- Chat Messages -->
                    <div id="chatMessages" class="h-96 overflow-y-auto p-4 border-b">
                        <div class="bot-message mb-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                    AI
                                </div>
                                <div class="bg-gray-100 rounded-lg px-4 py-2 max-w-md">
                                    <p class="text-gray-800">Hello! 👋 I'm your AI shopping assistant. How can I help you today?</p>
                                    <small class="text-gray-500">{{ now()->format('H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="p-4 bg-gray-50 border-b">
                        <p class="text-sm text-gray-600 mb-2">Quick actions:</p>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="sendQuickMessage('Show me trending products')" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-full text-sm transition-colors">
                                📈 Trending Products
                            </button>
                            <button onclick="sendQuickMessage('I need product recommendations')" class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1 rounded-full text-sm transition-colors">
                                🎯 Get Recommendations
                            </button>
                            @auth
                            <button onclick="sendQuickMessage('Check my order status')" class="bg-purple-100 hover:bg-purple-200 text-purple-800 px-3 py-1 rounded-full text-sm transition-colors">
                                📦 Check Orders
                            </button>
                            @endauth
                            <button onclick="sendQuickMessage('Help me find a specific product')" class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-3 py-1 rounded-full text-sm transition-colors">
                                🔍 Product Search
                            </button>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="p-4">
                        <div class="flex space-x-2">
                            <input type="text" 
                                   id="chatInput" 
                                   placeholder="Type your message here..." 
                                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   onkeypress="handleEnterKey(event)">
                            <button onclick="sendMessage()" 
                                    class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-2 rounded-lg transition-all duration-200 font-medium">
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                @auth
                <!-- Recent Chat Sessions -->
                @if($activeSessions && $activeSessions->count() > 0)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Chats</h3>
                    <div class="space-y-3">
                        @foreach($activeSessions as $session)
                        <div class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                             onclick="loadChatSession('{{ $session->id }}')">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium capitalize">{{ str_replace('_', ' ', $session->session_type) }}</span>
                                <span class="text-xs text-gray-500">{{ $session->started_at->diffForHumans() }}</span>
                            </div>
                            @if($session->latest_message)
                            <p class="text-xs text-gray-600 mt-1 truncate">{{ Str::limit($session->latest_message->message, 50) }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endauth

                <!-- Categories -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Browse Categories</h3>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($categories->take(6) as $category)
                        <button onclick="sendQuickMessage('Show me products in {{ $category->name }} category')"
                                class="text-left p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">
                            <span class="text-sm font-medium text-gray-800">{{ $category->name }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Recommendations -->
                @if($recommendations && $recommendations->count() > 0)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        @auth
                        Recommended for You
                        @else
                        Popular Products
                        @endauth
                    </h3>
                    <div class="space-y-3">
                        @foreach($recommendations->take(3) as $product)
                        <div class="flex items-center space-x-3 p-2 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <img src="{{ $product->image_url ?? '/images/placeholder-product.jpg' }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-12 h-12 object-cover rounded">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-800 truncate">{{ $product->name }}</h4>
                                <p class="text-sm text-green-600 font-semibold">₹{{ number_format($product->price) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button onclick="sendQuickMessage('Tell me more about recommended products')"
                            class="w-full mt-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-2 rounded-lg text-sm transition-all duration-200">
                        Ask AI About These
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
        <p class="text-gray-600">AI is thinking...</p>
    </div>
</div>


<script>
let currentSessionId = null;
let isWaitingForResponse = false;

function handleEnterKey(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function sendQuickMessage(message) {
    const input = document.getElementById('chatInput');
    input.value = message;
    sendMessage();
}

async function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message || isWaitingForResponse) return;
    
    // Clear input and show user message
    input.value = '';
    addMessage(message, 'user');
    
    // Show loading
    showLoading(true);
    isWaitingForResponse = true;
    
    try {
        const response = await fetch('{{ route("chatbot.intelligent-chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                session_id: currentSessionId,
                context: {}
            })
        });

        const data = await response.json();
        
        if (data.success) {
            currentSessionId = data.session_id;
            addMessage(data.message, 'bot');
            
            // Add suggestions if available
            if (data.suggestions && data.suggestions.length > 0) {
                addSuggestions(data.suggestions);
            }
            
            // Handle special actions
            if (data.actions && data.actions.includes('show_products')) {
                showProducts(data.metadata.products);
            }
        } else {
            addMessage('Sorry, I encountered an error. Please try again.', 'bot');
        }
    } catch (error) {
        console.error('Chat error:', error);
        addMessage('Sorry, there was a connection error. Please try again.', 'bot');
    } finally {
        showLoading(false);
        isWaitingForResponse = false;
    }
}

function addMessage(message, sender) {
    const messagesContainer = document.getElementById('chatMessages');
    const timestamp = new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `${sender}-message mb-4`;
    
    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="flex items-start space-x-3 justify-end">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg px-4 py-2 max-w-md">
                    <p>${message}</p>
                    <small class="text-blue-100">${timestamp}</small>
                </div>
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 text-sm font-bold">
                    U
                </div>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                    AI
                </div>
                <div class="bg-gray-100 rounded-lg px-4 py-2 max-w-md">
                    <p class="text-gray-800">${message}</p>
                    <small class="text-gray-500">${timestamp}</small>
                </div>
            </div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function addSuggestions(suggestions) {
    const messagesContainer = document.getElementById('chatMessages');
    
    const suggestionDiv = document.createElement('div');
    suggestionDiv.className = 'suggestions mb-4';
    
    let suggestionHtml = `
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-teal-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                💡
            </div>
            <div class="flex flex-wrap gap-2">
    `;
    
    suggestions.forEach(suggestion => {
        suggestionHtml += `
            <button onclick="sendQuickMessage('${suggestion.replace(/'/g, '\\\'')}')" 
                    class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-full text-sm transition-colors">
                ${suggestion}
            </button>
        `;
    });
    
    suggestionHtml += '</div></div>';
    suggestionDiv.innerHTML = suggestionHtml;
    
    messagesContainer.appendChild(suggestionDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showProducts(products) {
    if (!products || products.length === 0) return;
    
    const messagesContainer = document.getElementById('chatMessages');
    
    const productDiv = document.createElement('div');
    productDiv.className = 'products mb-4';
    
    let productHtml = `
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                🛍️
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-w-lg">
    `;
    
    products.forEach(product => {
        productHtml += `
            <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                <img src="${product.image_url || '/images/placeholder-product.jpg'}" 
                     alt="${product.name}" 
                     class="w-full h-24 object-cover rounded mb-2">
                <h4 class="text-sm font-medium text-gray-800 truncate">${product.name}</h4>
                <p class="text-sm text-green-600 font-semibold">₹${parseInt(product.price).toLocaleString()}</p>
                <button onclick="window.open('/product/${product.slug}', '_blank')" 
                        class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white py-1 rounded text-xs transition-colors">
                    View Product
                </button>
            </div>
        `;
    });
    
    productHtml += '</div></div>';
    productDiv.innerHTML = productHtml;
    
    messagesContainer.appendChild(productDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (show) {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    } else {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    }
}

async function loadChatSession(sessionId) {
    try {
        const response = await fetch(`/chatbot-ai/history/${sessionId}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            // Clear current chat
            document.getElementById('chatMessages').innerHTML = '';
            
            // Load messages
            data.messages.data.reverse().forEach(msg => {
                addMessage(msg.message, msg.sender_type === 'user' ? 'user' : 'bot');
            });
            
            currentSessionId = sessionId;
        }
    } catch (error) {
        console.error('Error loading chat session:', error);
    }
}

// Initialize chat on page load
document.addEventListener('DOMContentLoaded', function() {
    // Focus on input
    document.getElementById('chatInput').focus();
});
</script>
