<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $ticket->ticket_number }} - Laravel Cart</title>
    
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
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .reply-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .reply-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .customer-reply {
            border-left: 4px solid #4CAF50;
        }

        .staff-reply {
            border-left: 4px solid #2196F3;
        }

        .internal-note {
            border-left: 4px solid #FF9800;
            background: rgba(255, 152, 0, 0.05);
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

        .btn-warning {
            background: linear-gradient(45deg, #FFB74D, #FFCC02);
            border: none;
            border-radius: 50px;
            color: #333;
        }

        .btn-danger {
            background: linear-gradient(45deg, #E57373, #EF5350);
            border: none;
            border-radius: 50px;
        }

        .form-control, .form-select {
            border-radius: 15px;
            border: 1px solid #ddd;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
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

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .priority-urgent { border-left-color: #dc3545 !important; }
        .priority-high { border-left-color: #fd7e14 !important; }
        .priority-normal { border-left-color: #0d6efd !important; }
        .priority-low { border-left-color: #6c757d !important; }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #4CAF50, #66BB6A);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .reply-form {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .attachment-item {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.2);
            border-radius: 10px;
            padding: 10px 15px;
            margin: 5px;
            display: inline-block;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 20px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #4CAF50;
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Responsive Design Improvements */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .ticket-card .card-body {
                padding: 1.5rem;
            }
            
            .reply-card .card-body {
                padding: 1rem;
            }
            
            .badge {
                font-size: 0.65rem;
                padding: 4px 8px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 0.875rem;
            }
            
            .timeline {
                padding-left: 20px;
            }
            
            .timeline::before {
                left: 10px;
            }
            
            .timeline-item::before {
                left: -18px;
                width: 10px;
                height: 10px;
            }
            
            .avatar {
                width: 35px;
                height: 35px;
                font-size: 0.8rem;
            }
            
            .d-flex.flex-wrap.gap-2 {
                gap: 0.5rem !important;
            }
            
            .d-flex.flex-wrap.gap-2 .btn {
                flex: 1;
                margin-bottom: 0.5rem;
                min-width: 120px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0.5rem;
            }
            
            .ticket-card .card-body {
                padding: 1rem;
            }
            
            h2 {
                font-size: 1.3rem;
            }
            
            .reply-form {
                padding: 1rem;
            }
            
            .breadcrumb {
                padding: 8px 16px;
                font-size: 0.875rem;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
            
            .text-end {
                text-align: left !important;
            }
        }

        /* Enhanced mobile-friendly features */
        .reply-card {
            position: relative;
            overflow: hidden;
        }

        .reply-card::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: inherit;
        }

        .customer-reply::after {
            background: #4CAF50;
        }

        .staff-reply::after {
            background: #2196F3;
        }

        .internal-note::after {
            background: #FF9800;
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
                <li class="breadcrumb-item">
                    <a href="{{ route('support.tickets') }}">My Tickets</a>
                </li>
                <li class="breadcrumb-item active">Ticket #{{ $ticket->ticket_number }}</li>
            </ol>
        </nav>

        <!-- Ticket Header -->
        <div class="ticket-card priority-{{ $ticket->priority }} mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap">
                    <div class="flex-grow-1 mb-3 mb-md-0">
                        <h2 class="fw-bold text-dark mb-2">{{ $ticket->subject }}</h2>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <!-- Status Badge -->
                            @if($ticket->status === 'open')
                                <span class="badge bg-primary">Open</span>
                            @elseif($ticket->status === 'in_progress')
                                <span class="badge bg-warning">In Progress</span>
                            @elseif($ticket->status === 'waiting_customer')
                                <span class="badge bg-info">Waiting for You</span>
                            @elseif($ticket->status === 'resolved')
                                <span class="badge bg-success">Resolved</span>
                            @else
                                <span class="badge bg-secondary">Closed</span>
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

                            <!-- Category Badge -->
                            <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</span>
                        </div>
                    </div>
                    
                    <div class="text-md-end">
                        <h6 class="fw-bold text-muted mb-1">Ticket #{{ $ticket->ticket_number }}</h6>
                        <p class="text-muted small mb-0">Created: {{ $ticket->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                </div>

                <!-- Ticket Details -->
                <div class="row text-muted">
                    <div class="col-lg-6 col-md-12">
                        @if($ticket->order_id)
                        <p class="mb-2">
                            <i class="fas fa-shopping-cart me-2"></i>
                            <strong>Order:</strong> #{{ $ticket->order_id }}
                        </p>
                        @endif
                        @if($ticket->product_id)
                        <p class="mb-2">
                            <i class="fas fa-box me-2"></i>
                            <strong>Product:</strong> {{ $ticket->product_id }}
                        </p>
                        @endif
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <p class="mb-2">
                            <i class="fas fa-user me-2"></i>
                            <strong>Customer:</strong> {{ $ticket->user->name }}
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Email:</strong> 
                            <span class="d-inline-block text-truncate" style="max-width: 200px;">{{ $ticket->user->email }}</span>
                        </p>
                    </div>
                </div>

                <!-- Original Message -->
                <div class="mt-4 pt-4 border-top">
                    <h6 class="fw-semibold mb-3">
                        <i class="fas fa-comment me-2"></i>Original Message
                    </h6>
                    <div class="bg-light rounded p-3">
                        {{ $ticket->description }}
                    </div>
                </div>

                <!-- Actions -->
                @if(!in_array($ticket->status, ['closed']))
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-2">
                        @if($ticket->status === 'resolved')
                        <form action="{{ route('support.close', $ticket) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm w-100 w-sm-auto">
                                <i class="fas fa-check me-1"></i> Mark as Closed
                            </button>
                        </form>
                        <button type="button" class="btn btn-warning btn-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#reopenModal">
                            <i class="fas fa-undo me-1"></i> Reopen Ticket
                        </button>
                        @endif
                        
                        @if(in_array($ticket->status, ['waiting_customer', 'resolved']))
                        <button type="button" class="btn btn-primary btn-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#replyModal">
                            <i class="fas fa-reply me-1"></i> Add Reply
                        </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Conversation Timeline -->
        @if($ticket->replies->count() > 0)
        <div class="glass-card mb-4">
            <div class="card-header bg-transparent border-0 p-4">
                <h4 class="fw-bold text-white mb-0">
                    <i class="fas fa-comments me-2"></i>Conversation
                </h4>
            </div>
            <div class="card-body p-4">
                <div class="timeline">
                    @foreach($ticket->replies->sortBy('created_at') as $reply)
                    <div class="timeline-item">
                        <div class="reply-card {{ $reply->is_staff ? 'staff-reply' : 'customer-reply' }} {{ $reply->is_internal ? 'internal-note' : '' }}">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar me-3">
                                        {{ substr($reply->is_staff ? 'Staff' : $ticket->user->name, 0, 1) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="fw-semibold mb-1">
                                                {{ $reply->is_staff ? 'Support Staff' : $ticket->user->name }}
                                                @if($reply->is_internal)
                                                    <span class="badge bg-warning ms-2">Internal Note</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                {{ $reply->created_at->format('M j, Y g:i A') }}
                                            </small>
                                        </div>
                                        @if($reply->is_staff)
                                            <small class="text-muted">Support Team</small>
                                        @else
                                            <small class="text-muted">Customer</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="reply-content">
                                    {{ $reply->message }}
                                </div>
                                @if($reply->attachments)
                                <div class="mt-3">
                                    <h6 class="small fw-semibold text-muted mb-2">Attachments:</h6>
                                    @foreach(json_decode($reply->attachments, true) as $attachment)
                                    <div class="attachment-item">
                                        <i class="fas fa-paperclip me-1"></i>
                                        <a href="#" class="text-decoration-none">{{ $attachment }}</a>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Reply Form -->
        @if(in_array($ticket->status, ['waiting_customer', 'resolved']) && !in_array($ticket->status, ['closed']))
        <div class="reply-form p-4">
            <h5 class="fw-bold mb-4">
                <i class="fas fa-reply me-2"></i>Add Your Reply
            </h5>
            <form action="{{ route('support.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="message" class="form-label">Your Message *</label>
                    <textarea 
                        class="form-control @error('message') is-invalid @enderror" 
                        id="message" 
                        name="message" 
                        rows="5" 
                        placeholder="Type your reply here..."
                        required
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="reply_attachments" class="form-label">Attachments (Optional)</label>
                    <input 
                        type="file" 
                        class="form-control" 
                        id="reply_attachments" 
                        name="attachments[]" 
                        multiple 
                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                    >
                    <div class="form-text">Max file size: 10MB. Supported formats: JPG, PNG, PDF, DOC, DOCX</div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Reply
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

    <!-- Reopen Modal -->
    @if($ticket->status === 'resolved')
    <div class="modal fade" id="reopenModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reopen Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('support.reopen', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reopen_reason" class="form-label">Reason for reopening *</label>
                            <textarea 
                                class="form-control" 
                                id="reopen_reason" 
                                name="message" 
                                rows="4" 
                                placeholder="Please explain why you're reopening this ticket..."
                                required
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Reopen Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
