<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $ticket->id }} - Admin</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .admin-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .ticket-info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }
        
        .btn-admin {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .message-card {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }
        
        .internal-note {
            border-left: 4px solid #ffc107;
            background: #fff3cd;
        }
        
        .badge-priority-high { background: linear-gradient(45deg, #ff6b6b, #ff8e8e); }
        .badge-priority-medium { background: linear-gradient(45deg, #ffa726, #ffb74d); }
        .badge-priority-low { background: linear-gradient(45deg, #66bb6a, #81c784); }
        .badge-status-open { background: linear-gradient(45deg, #42a5f5, #64b5f6); }
        .badge-status-in-progress { background: linear-gradient(45deg, #ff9800, #ffb74d); }
        .badge-status-resolved { background: linear-gradient(45deg, #4caf50, #66bb6a); }
        .badge-status-closed { background: linear-gradient(45deg, #9e9e9e, #bdbdbd); }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar navbar-expand-lg admin-header text-white">
        <div class="container-fluid">
            <a class="navbar-brand text-white fw-bold" href="{{ route('admin.support.dashboard') }}">
                <i class="fas fa-shield-alt me-2"></i>Admin Support
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="{{ route('admin.support.tickets.index') }}">
                    <i class="fas fa-list me-1"></i>All Tickets
                </a>
                <a class="nav-link text-white" href="{{ route('admin.support.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Ticket Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="ticket-info-card text-white p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="mb-2">
                                <span class="badge bg-light text-dark me-2">#{{ $ticket->id }}</span>
                                {{ $ticket->subject }}
                            </h3>
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <span class="badge badge-status-{{ str_replace('_', '-', strtolower($ticket->status)) }} px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                                <span class="badge badge-priority-{{ strtolower($ticket->priority) }} px-3 py-2">
                                    {{ ucfirst($ticket->priority) }} Priority
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <small class="opacity-75">Customer:</small>
                                    <div class="fw-medium">{{ $ticket->user->name }}</div>
                                    <div class="small">{{ $ticket->user->email }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="opacity-75">Created:</small>
                                    <div class="fw-medium">{{ $ticket->created_at->format('M d, Y H:i') }}</div>
                                    <div class="small">{{ $ticket->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column gap-2">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-admin dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog me-1"></i>Actions
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li><a class="dropdown-item" href="{{ route('admin.support.tickets.edit', $ticket) }}">
                                            <i class="fas fa-edit me-2"></i>Edit Ticket
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#statusModal">
                                            <i class="fas fa-flag me-2"></i>Change Status
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignModal">
                                            <i class="fas fa-user-plus me-2"></i>Assign Agent
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#priorityModal">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Change Priority
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="fas fa-trash me-2"></i>Delete Ticket
                                        </a></li>
                                    </ul>
                                </div>
                                
                                @if($ticket->assignedAgent)
                                <div class="text-center">
                                    <small class="opacity-75">Assigned to:</small>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="avatar-sm bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                            {{ substr($ticket->assignedAgent->name, 0, 1) }}
                                        </div>
                                        <span class="fw-medium">{{ $ticket->assignedAgent->name }}</span>
                                    </div>
                                </div>
                                @else
                                <div class="text-center">
                                    <span class="badge bg-warning">Unassigned</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Ticket Conversation -->
            <div class="col-lg-8">
                <div class="content-card p-4">
                    <h5 class="mb-4">
                        <i class="fas fa-comments me-2 text-primary"></i>Conversation
                    </h5>
                    
                    <!-- Original Message -->
                    <div class="message-card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    {{ substr($ticket->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $ticket->user->name }}</div>
                                    <small class="text-muted">{{ $ticket->created_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                            <span class="badge bg-primary">Original</span>
                        </div>
                        <div class="ticket-message">
                            {!! nl2br(e($ticket->message)) !!}
                        </div>
                    </div>

                    <!-- Replies -->
                    @foreach($ticket->replies as $reply)
                    <div class="message-card p-4 mb-4 {{ $reply->is_internal ? 'internal-note' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm {{ $reply->user->isAgent() ? 'bg-success' : 'bg-primary' }} rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    {{ substr($reply->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold">
                                        {{ $reply->user->name }}
                                        @if($reply->user->isAgent())
                                        <span class="badge bg-success ms-1">Agent</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $reply->created_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                            @if($reply->is_internal)
                            <span class="badge bg-warning">Internal Note</span>
                            @endif
                        </div>
                        <div class="reply-message">
                            {!! nl2br(e($reply->message)) !!}
                        </div>
                    </div>
                    @endforeach

                    <!-- Reply Form -->
                    <div class="mt-4">
                        <h6 class="mb-3">Add Reply</h6>
                        <form action="{{ route('admin.support.tickets.reply', $ticket) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="4" 
                                          placeholder="Type your reply..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_internal" value="1" class="form-check-input" id="internalNote">
                                    <label class="form-check-label" for="internalNote">
                                        Internal Note (Only visible to agents)
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-admin">
                                    <i class="fas fa-reply me-1"></i>Send Reply
                                </button>
                                <button type="submit" name="status" value="resolved" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i>Reply & Resolve
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Ticket Details -->
                <div class="content-card p-4 mb-4">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Ticket Details
                    </h6>
                    <div class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge badge-status-{{ str_replace('_', '-', strtolower($ticket->status)) }} ms-2">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Priority:</strong>
                        <span class="badge badge-priority-{{ strtolower($ticket->priority) }} ms-2">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Category:</strong>
                        <span class="ms-2">{{ $ticket->category ?? 'General' }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Last Updated:</strong>
                        <span class="ms-2">{{ $ticket->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Replies:</strong>
                        <span class="ms-2">{{ $ticket->replies->count() }}</span>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="content-card p-4 mb-4">
                    <h6 class="mb-3">
                        <i class="fas fa-user me-2 text-primary"></i>Customer Information
                    </h6>
                    <div class="text-center mb-3">
                        <div class="avatar-lg bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px; font-size: 24px;">
                            {{ substr($ticket->user->name, 0, 1) }}
                        </div>
                        <h6 class="mb-1">{{ $ticket->user->name }}</h6>
                        <small class="text-muted">{{ $ticket->user->email }}</small>
                    </div>
                    <div class="small">
                        <div class="mb-1">
                            <strong>Member since:</strong> {{ $ticket->user->created_at->format('M Y') }}
                        </div>
                        <div class="mb-1">
                            <strong>Total tickets:</strong> {{ $ticket->user->supportTickets->count() }}
                        </div>
                        <div class="mb-1">
                            <strong>Orders:</strong> {{ $ticket->user->orders->count() ?? 0 }}
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="#" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-user me-1"></i>View Customer Profile
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-card p-4">
                    <h6 class="mb-3">
                        <i class="fas fa-clock me-2 text-primary"></i>Recent Activity
                    </h6>
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $ticket->created_at->diffForHumans() }}</small>
                                <div>Ticket created by {{ $ticket->user->name }}</div>
                            </div>
                        </div>
                        @foreach($ticket->replies->take(3) as $reply)
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-{{ $reply->user->isAgent() ? 'success' : 'info' }}"></div>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small>
                                <div>{{ $reply->is_internal ? 'Internal note' : 'Reply' }} by {{ $reply->user->name }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.support.tickets.update-status', $ticket) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-admin">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .timeline {
            position: relative;
            padding-left: 20px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        
        .timeline-item {
            position: relative;
        }
        
        .timeline-marker {
            position: absolute;
            left: -16px;
            top: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .timeline-content {
            margin-left: 8px;
        }
    </style>
</body>
</html>