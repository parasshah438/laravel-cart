<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Chats Management - Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .chat-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
        }

        .chat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .priority-high {
            border-left: 4px solid #dc3545;
        }

        .priority-medium {
            border-left: 4px solid #ffc107;
        }

        .priority-low {
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Live Chats Management</h1>
                <p class="text-muted">Manage customer live chat sessions</p>
            </div>
            <div>
                <a href="{{ route('admin.support.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Active Chats</h5>
                                <h3 class="mb-0">{{ $chats->where('status', 'active')->count() }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-comments fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Waiting</h5>
                                <h3 class="mb-0">{{ $chats->where('status', 'waiting')->count() }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Resolved</h5>
                                <h3 class="mb-0">{{ $chats->where('status', 'ended')->count() }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Today</h5>
                                <h3 class="mb-0">{{ $chats->where('created_at', '>=', today())->count() }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chats List -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Live Chat Sessions</h5>
            </div>
            <div class="card-body">
                @if($chats->count() > 0)
                    <div class="row">
                        @foreach($chats as $chat)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card chat-card h-100 {{ $chat->status === 'waiting' ? 'priority-high' : ($chat->status === 'active' ? 'priority-medium' : 'priority-low') }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $chat->user->name }}
                                        </h6>
                                        <span class="badge status-badge {{ $chat->status === 'active' ? 'bg-success' : ($chat->status === 'waiting' ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ ucfirst($chat->status) }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-clock me-1"></i>
                                        Started: {{ $chat->created_at->format('M j, g:i A') }}
                                    </p>
                                    
                                    @if($chat->agent)
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-headset me-1"></i>
                                        Agent: {{ $chat->agent->name }}
                                    </p>
                                    @endif
                                    
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-comments me-1"></i>
                                        {{ $chat->messages->count() }} messages
                                    </p>
                                    
                                    @if($chat->last_message)
                                    <p class="small text-truncate mb-3" style="max-height: 40px;">
                                        <strong>Last:</strong> {{ Str::limit($chat->last_message, 50) }}
                                    </p>
                                    @endif
                                    
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.support.chats.show', $chat) }}" class="btn btn-primary btn-sm flex-fill">
                                            <i class="fas fa-eye me-1"></i> View Chat
                                        </a>
                                        
                                        @if($chat->status === 'waiting')
                                        <form action="{{ route('admin.support.chats.join', $chat) }}" method="POST" class="d-inline flex-fill">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                <i class="fas fa-play me-1"></i> Join
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No chat sessions found</h5>
                        <p class="text-muted">Chat sessions will appear here when customers start live chats.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh every 30 seconds to show new chats
        setInterval(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>