<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center - Laravel Cart</title>
    
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .stat-card .card-body {
            padding: 1.5rem;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .action-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .action-card .card-body {
            padding: 1.5rem;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            color: inherit;
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

        .btn-success {
            background: linear-gradient(45deg, #66BB6A, #81C784);
            border: none;
            border-radius: 50px;
        }

        .btn-info {
            background: linear-gradient(45deg, #29B6F6, #42A5F5);
            border: none;
            border-radius: 50px;
        }

        .ticket-item {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
        }

        .ticket-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-white mb-3">Support Center</h1>
            <p class="lead text-white-75">Get help with your orders, account, and general questions</p>
        </div>

        <!-- Quick Stats -->
        <div class="row g-4 mb-5">
            <!-- Total Tickets -->
            <div class="col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-primary bg-opacity-10 mb-3">
                            <i class="fas fa-ticket-alt text-primary"></i>
                        </div>
                        <h3 class="fw-bold text-primary">{{ $stats['total_tickets'] }}</h3>
                        <p class="text-muted mb-0">Total Tickets</p>
                    </div>
                </div>
            </div>

            <!-- Open Tickets -->
            <div class="col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-warning bg-opacity-10 mb-3">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <h3 class="fw-bold text-warning">{{ $stats['open_tickets'] }}</h3>
                        <p class="text-muted mb-0">Open Tickets</p>
                    </div>
                </div>
            </div>

            <!-- Closed Tickets -->
            <div class="col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-success bg-opacity-10 mb-3">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <h3 class="fw-bold text-success">{{ $stats['closed_tickets'] }}</h3>
                        <p class="text-muted mb-0">Resolved Tickets</p>
                    </div>
                </div>
            </div>

            <!-- Live Chat Status -->
            <div class="col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-circle {{ $stats['active_chat'] ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 mb-3">
                            <i class="fas fa-comments {{ $stats['active_chat'] ? 'text-success' : 'text-secondary' }}"></i>
                        </div>
                        <h6 class="fw-bold {{ $stats['active_chat'] ? 'text-success' : 'text-secondary' }}">
                            {{ $stats['active_chat'] ? 'Active' : 'Available' }}
                        </h6>
                        <p class="text-muted mb-0">Live Chat</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mb-5">
            <!-- Create New Ticket -->
            <div class="col-md-4">
                <a href="{{ route('support.create') }}" class="action-card card h-100 text-decoration-none">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-primary bg-opacity-10 mb-3">
                            <i class="fas fa-plus text-primary"></i>
                        </div>
                        <h5 class="fw-semibold text-dark mb-2">Create New Ticket</h5>
                        <p class="text-muted mb-3">Submit a detailed support request</p>
                        <span class="btn btn-primary">Create Ticket</span>
                    </div>
                </a>
            </div>

            <!-- Start Live Chat -->
            <div class="col-md-4">
                <a href="{{ route('support.chat') }}" class="action-card card h-100 text-decoration-none">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-success bg-opacity-10 mb-3">
                            <i class="fas fa-comments text-success"></i>
                        </div>
                        <h5 class="fw-semibold text-dark mb-2">Live Chat</h5>
                        <p class="text-muted mb-3">Get instant help from our support team</p>
                        <span class="btn btn-success">{{ $stats['active_chat'] ? 'Continue Chat' : 'Start Chat' }}</span>
                    </div>
                </a>
            </div>

            <!-- Browse Help -->
            <div class="col-md-4">
                <a href="{{ route('help') }}" class="action-card card h-100 text-decoration-none">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-info bg-opacity-10 mb-3">
                            <i class="fas fa-question-circle text-info"></i>
                        </div>
                        <h5 class="fw-semibold text-dark mb-2">Help Center</h5>
                        <p class="text-muted mb-3">Find answers in our knowledge base</p>
                        <span class="btn btn-info">Browse Help</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Tickets -->
        @if($recentTickets->count() > 0)
        <div class="glass-card">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold text-white mb-0">Recent Tickets</h4>
                    <a href="{{ route('support.tickets') }}" class="text-white text-decoration-none">
                        View All Tickets <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @foreach($recentTickets as $ticket)
                <div class="ticket-item p-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="fw-semibold mb-0 me-3">
                                    <a href="{{ route('support.show', $ticket) }}" class="text-decoration-none">
                                        #{{ $ticket->ticket_number }} - {{ $ticket->subject }}
                                    </a>
                                </h6>
                                @if($ticket->status === 'open')
                                    <span class="badge bg-primary">{{ ucfirst($ticket->status) }}</span>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="badge bg-warning">In Progress</span>
                                @elseif($ticket->status === 'waiting_customer')
                                    <span class="badge bg-info">Waiting for You</span>
                                @elseif($ticket->status === 'resolved')
                                    <span class="badge bg-success">Resolved</span>
                                @else
                                    <span class="badge bg-secondary">Closed</span>
                                @endif
                                
                                @if($ticket->priority === 'urgent')
                                    <span class="badge bg-danger ms-1">Urgent</span>
                                @elseif($ticket->priority === 'high')
                                    <span class="badge bg-warning ms-1">High</span>
                                @endif
                            </div>
                            <p class="text-muted mb-2">{{ Str::limit($ticket->description, 100) }}</p>
                            <div class="small text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Created: {{ $ticket->created_at->format('M j, Y g:i A') }}
                                @if($ticket->latestReply)
                                    <span class="ms-3">
                                        <i class="fas fa-comment me-1"></i>
                                        Last reply: {{ $ticket->latestReply->created_at->format('M j, Y g:i A') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="ms-3">
                            <a href="{{ route('support.show', $ticket) }}" class="btn btn-outline-primary btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="glass-card text-center py-5">
            <div class="icon-circle bg-secondary bg-opacity-10 mx-auto mb-4">
                <i class="fas fa-ticket-alt fa-lg text-secondary"></i>
            </div>
            <h4 class="fw-bold text-white mb-3">No Support Tickets</h4>
            <p class="text-white-75 mb-4">You haven't created any support tickets yet.</p>
            <a href="{{ route('support.create') }}" class="btn btn-primary btn-lg">
                Create Your First Ticket
            </a>
        </div>
        @endif
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>