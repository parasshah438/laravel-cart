<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ticket #{{ $ticket->id }} - Admin</title>
    
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
                <a class="nav-link text-white" href="{{ route('admin.support.tickets.show', $ticket) }}">
                    <i class="fas fa-eye me-1"></i>View Ticket
                </a>
                <a class="nav-link text-white" href="{{ route('admin.support.tickets.index') }}">
                    <i class="fas fa-list me-1"></i>All Tickets
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="content-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2 text-primary"></i>Edit Ticket #{{ $ticket->id }}
                        </h4>
                        <div class="d-flex gap-2">
                            <span class="badge badge-status-{{ str_replace('_', '-', strtolower($ticket->status)) }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                            <span class="badge badge-priority-{{ strtolower($ticket->priority) }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form action="{{ route('admin.support.tickets.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer</label>
                                <div class="form-control bg-light">
                                    <i class="fas fa-user me-2"></i>{{ $ticket->user->name }} ({{ $ticket->user->email }})
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign to Agent</label>
                                <select name="assigned_agent_id" class="form-select">
                                    <option value="">Unassigned</option>
                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" 
                                            {{ (old('assigned_agent_id', $ticket->assigned_agent_id) == $agent->id) ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" 
                                   value="{{ old('subject', $ticket->subject) }}" required 
                                   placeholder="Enter ticket subject">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="open" {{ old('status', $ticket->status) == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ old('status', $ticket->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ old('status', $ticket->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ old('status', $ticket->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select" required>
                                    <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">Select Category</option>
                                    <option value="general" {{ old('category', $ticket->category) == 'general' ? 'selected' : '' }}>General</option>
                                    <option value="order" {{ old('category', $ticket->category) == 'order' ? 'selected' : '' }}>Order Issue</option>
                                    <option value="product" {{ old('category', $ticket->category) == 'product' ? 'selected' : '' }}>Product Issue</option>
                                    <option value="payment" {{ old('category', $ticket->category) == 'payment' ? 'selected' : '' }}>Payment Issue</option>
                                    <option value="shipping" {{ old('category', $ticket->category) == 'shipping' ? 'selected' : '' }}>Shipping Issue</option>
                                    <option value="technical" {{ old('category', $ticket->category) == 'technical' ? 'selected' : '' }}>Technical Issue</option>
                                    <option value="account" {{ old('category', $ticket->category) == 'account' ? 'selected' : '' }}>Account Issue</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="6" required 
                                      placeholder="Describe the issue or request in detail...">{{ old('description', $ticket->description) }}</textarea>
                        </div>

                        <!-- Ticket Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="mb-2"><i class="fas fa-info-circle me-2 text-primary"></i>Ticket Information</h6>
                                    <div class="small">
                                        <div class="mb-1"><strong>Created:</strong> {{ $ticket->created_at->format('M d, Y H:i') }}</div>
                                        <div class="mb-1"><strong>Last Updated:</strong> {{ $ticket->updated_at->diffForHumans() }}</div>
                                        <div class="mb-1"><strong>Replies:</strong> {{ $ticket->replies->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-admin">
                                <i class="fas fa-save me-1"></i>Update Ticket
                            </button>
                            <a href="{{ route('admin.support.tickets.show', $ticket) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-eye me-1"></i>View Ticket
                            </a>
                            <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-1"></i>All Tickets
                            </a>
                            <button type="button" class="btn btn-outline-danger ms-auto" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this ticket? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        All replies and conversation history will be permanently lost.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.support.tickets.destroy', $ticket) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete Ticket
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>