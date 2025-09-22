<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Support Tickets - Laravel Cart</title>
    
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

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .ticket-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .ticket-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(45deg, #4CAF50, #66BB6A);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #388E3C, #4CAF50);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .btn-outline-primary {
            color: #4CAF50;
            border-color: #4CAF50;
            border-radius: 50px;
        }

        .btn-outline-primary:hover {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
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

        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            border: none;
        }

        .form-select {
            border-radius: 50px;
            border: 1px solid #ddd;
        }

        .priority-urgent { border-left: 4px solid #dc3545; }
        .priority-high { border-left: 4px solid #fd7e14; }
        .priority-normal { border-left: 4px solid #0d6efd; }
        .priority-low { border-left: 4px solid #6c757d; }

        .empty-state {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 60px 20px;
            text-align: center;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
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
                <li class="breadcrumb-item active">My Tickets</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold text-white mb-2">My Support Tickets</h1>
                <p class="text-white-75 mb-0">Track and manage your support requests</p>
            </div>
            <a href="{{ route('support.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> New Ticket
            </a>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card filter-card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('support.tickets') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="waiting_customer" {{ request('status') === 'waiting_customer' ? 'selected' : '' }}>Waiting for Customer</option>
                                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="">All Priorities</option>
                                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <option value="order" {{ request('category') === 'order' ? 'selected' : '' }}>Order Support</option>
                                    <option value="product" {{ request('category') === 'product' ? 'selected' : '' }}>Product Question</option>
                                    <option value="account" {{ request('category') === 'account' ? 'selected' : '' }}>Account Support</option>
                                    <option value="technical" {{ request('category') === 'technical' ? 'selected' : '' }}>Technical Issue</option>
                                    <option value="billing" {{ request('category') === 'billing' ? 'selected' : '' }}>Billing</option>
                                    <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>General</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('support.tickets') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets List -->
        @if($tickets->count() > 0)
            @foreach($tickets as $ticket)
            <div class="ticket-card priority-{{ $ticket->priority }}">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-3">
                                <h5 class="fw-semibold mb-0 me-3">
                                    <a href="{{ route('support.show', $ticket) }}" class="text-decoration-none">
                                        #{{ $ticket->ticket_number }} - {{ $ticket->subject }}
                                    </a>
                                </h5>
                                
                                <!-- Status Badge -->
                                @if($ticket->status === 'open')
                                    <span class="badge bg-primary me-2">Open</span>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="badge bg-warning me-2">In Progress</span>
                                @elseif($ticket->status === 'waiting_customer')
                                    <span class="badge bg-info me-2">Waiting for You</span>
                                @elseif($ticket->status === 'resolved')
                                    <span class="badge bg-success me-2">Resolved</span>
                                @else
                                    <span class="badge bg-secondary me-2">Closed</span>
                                @endif

                                <!-- Priority Badge -->
                                @if($ticket->priority === 'urgent')
                                    <span class="badge bg-danger">Urgent</span>
                                @elseif($ticket->priority === 'high')
                                    <span class="badge bg-warning">High Priority</span>
                                @elseif($ticket->priority === 'normal')
                                    <span class="badge bg-primary">Normal</span>
                                @else
                                    <span class="badge bg-secondary">Low Priority</span>
                                @endif
                            </div>

                            <p class="text-muted mb-3">{{ Str::limit($ticket->description, 150) }}</p>
                            
                            <div class="row text-muted small">
                                <div class="col-md-6">
                                    <i class="fas fa-tag me-1"></i>
                                    Category: <span class="text-capitalize">{{ str_replace('_', ' ', $ticket->category) }}</span>
                                </div>
                                <div class="col-md-6">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Created: {{ $ticket->created_at->format('M j, Y g:i A') }}
                                </div>
                                @if($ticket->order_id)
                                <div class="col-md-6 mt-1">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    Order: #{{ $ticket->order_id }}
                                </div>
                                @endif
                                @if($ticket->latestReply)
                                <div class="col-md-6 mt-1">
                                    <i class="fas fa-comment me-1"></i>
                                    Last reply: {{ $ticket->latestReply->created_at->format('M j, Y g:i A') }}
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="ms-3 d-flex flex-column gap-2">
                            <a href="{{ route('support.show', $ticket) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            @if(in_array($ticket->status, ['waiting_customer', 'resolved']))
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#replyModal{{ $ticket->id }}">
                                <i class="fas fa-reply me-1"></i> Reply
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Pagination -->
            @if($tickets->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $tickets->appends(request()->query())->links() }}
            </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="icon-circle bg-secondary bg-opacity-10">
                    <i class="fas fa-ticket-alt fa-3x text-secondary"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">No Support Tickets Found</h4>
                @if(request()->hasAny(['status', 'priority', 'category']))
                    <p class="text-muted mb-4">No tickets match your current filters. Try adjusting your search criteria.</p>
                    <a href="{{ route('support.tickets') }}" class="btn btn-outline-primary me-3">
                        <i class="fas fa-times me-2"></i> Clear Filters
                    </a>
                @else
                    <p class="text-muted mb-4">You haven't created any support tickets yet.</p>
                @endif
                <a href="{{ route('support.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Create Your First Ticket
                </a>
            </div>
        @endif
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Support Tickets</h1>
            <p class="text-gray-600">Manage your support requests and track their progress</p>
        </div>
        <a href="{{ route('support.create') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
            Create New Ticket
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <form method="GET" action="{{ route('support.tickets') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search tickets..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="waiting_customer" {{ request('status') === 'waiting_customer' ? 'selected' : '' }}>Waiting for You</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select id="category" name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Categories</option>
                    <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>General</option>
                    <option value="order" {{ request('category') === 'order' ? 'selected' : '' }}>Order</option>
                    <option value="product" {{ request('category') === 'product' ? 'selected' : '' }}>Product</option>
                    <option value="billing" {{ request('category') === 'billing' ? 'selected' : '' }}>Billing</option>
                    <option value="technical" {{ request('category') === 'technical' ? 'selected' : '' }}>Technical</option>
                    <option value="complaint" {{ request('category') === 'complaint' ? 'selected' : '' }}>Complaint</option>
                </select>
            </div>

            <!-- Priority Filter -->
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select id="priority" name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            <!-- Filter Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors font-medium">
                    Filter
                </button>
            </div>
        </form>

        @if(request()->hasAny(['search', 'status', 'category', 'priority']))
        <div class="mt-4 flex items-center justify-between">
            <span class="text-sm text-gray-600">
                Showing filtered results
            </span>
            <a href="{{ route('support.tickets') }}" class="text-sm text-blue-600 hover:text-blue-700">
                Clear filters
            </a>
        </div>
        @endif
    </div>

    <!-- Tickets List -->
    @if($tickets->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="divide-y">
            @foreach($tickets as $ticket)
            <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Ticket Header -->
                        <div class="flex items-center space-x-3 mb-2">
                            <h3 class="text-lg font-medium text-gray-900">
                                <a href="{{ route('support.show', $ticket) }}" class="hover:text-blue-600">
                                    #{{ $ticket->ticket_number }}
                                </a>
                            </h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->status_badge_color }}-100 text-{{ $ticket->status_badge_color }}-800">
                                {{ $ticket->formatted_status }}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->priority_badge_color }}-100 text-{{ $ticket->priority_badge_color }}-800">
                                {{ $ticket->formatted_priority }}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst($ticket->category) }}
                            </span>
                        </div>

                        <!-- Ticket Subject -->
                        <h4 class="text-md font-medium text-gray-900 mb-2">
                            <a href="{{ route('support.show', $ticket) }}" class="hover:text-blue-600">
                                {{ $ticket->subject }}
                            </a>
                        </h4>

                        <!-- Ticket Description -->
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($ticket->description, 150) }}</p>

                        <!-- Ticket Meta -->
                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Created: {{ $ticket->created_at->format('M j, Y g:i A') }}
                            </span>
                            @if($ticket->latestReply)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    Last reply: {{ $ticket->latestReply->created_at->format('M j, Y g:i A') }}
                                    @if($ticket->latestReply->is_staff_reply)
                                        <span class="ml-1 text-blue-600">(Staff)</span>
                                    @endif
                                </span>
                            @endif
                            @if($ticket->order)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    Order #{{ $ticket->order->id }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="flex-shrink-0 ml-4">
                        <a href="{{ route('support.show', $ticket) }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $tickets->links() }}
    </div>

    @else
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
        <div class="p-4 bg-gray-100 rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 4rem; height: 4rem;">
            <i class="fas fa-ticket-alt text-muted fa-2x"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            @if(request()->hasAny(['search', 'status', 'category', 'priority']))
                No tickets found
            @else
                No Support Tickets
            @endif
        </h3>
        <p class="text-gray-600 mb-4">
            @if(request()->hasAny(['search', 'status', 'category', 'priority']))
                Try adjusting your filters to find what you're looking for.
            @else
                You haven't created any support tickets yet.
            @endif
        </p>
        @if(request()->hasAny(['search', 'status', 'category', 'priority']))
            <a href="{{ route('support.tickets') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors mr-4">
                Clear Filters
            </a>
        @endif
        <a href="{{ route('support.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Create New Ticket
        </a>
    </div>
    @endif
</div>
