<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Support Tickets</title>
    
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
        
        .filter-card {
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
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
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
                <a class="nav-link text-white" href="{{ route('admin.support.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-card text-white p-4">
                    <form method="GET" action="{{ route('admin.support.tickets.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="">All Priorities</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Assigned Agent</label>
                                <select name="agent" class="form-select">
                                    <option value="">All Agents</option>
                                    <option value="unassigned" {{ request('agent') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ request('agent') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-admin">
                                        <i class="fas fa-filter me-1"></i>Filter
                                    </button>
                                    <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-light">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="row">
            <div class="col-12">
                <div class="content-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <i class="fas fa-ticket-alt me-2 text-primary"></i>Support Tickets
                            <span class="badge bg-primary ms-2">{{ $tickets->total() }}</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.support.tickets.create') }}" class="btn btn-admin">
                                <i class="fas fa-plus me-1"></i>Create Ticket
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    @if($tickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Customer</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned Agent</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tickets as $ticket)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input ticket-checkbox" value="{{ $ticket->id }}">
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">#{{ $ticket->id }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.support.tickets.show', $ticket) }}" 
                                           class="text-decoration-none fw-medium">
                                            {{ Str::limit($ticket->subject, 50) }}
                                        </a>
                                        @if($ticket->replies_count > 0)
                                        <span class="badge bg-info ms-1">{{ $ticket->replies_count }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                                {{ substr($ticket->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $ticket->user->name }}</div>
                                                <small class="text-muted">{{ $ticket->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-priority-{{ strtolower($ticket->priority) }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-{{ str_replace('_', '-', strtolower($ticket->status)) }}">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->assignedAgent)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 25px; height: 25px; font-size: 10px;">
                                                    {{ substr($ticket->assignedAgent->name, 0, 1) }}
                                                </div>
                                                {{ $ticket->assignedAgent->name }}
                                            </div>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $ticket->updated_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.support.tickets.show', $ticket) }}" 
                                               class="btn btn-outline-primary btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.support.tickets.edit', $ticket) }}" 
                                               class="btn btn-outline-secondary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    title="Assign Agent" data-bs-toggle="modal" 
                                                    data-bs-target="#assignModal" data-ticket-id="{{ $ticket->id }}">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }} results
                        </div>
                        {{ $tickets->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No tickets found</h5>
                        <p class="text-muted">There are no support tickets matching your criteria.</p>
                        <a href="{{ route('admin.support.tickets.create') }}" class="btn btn-admin">
                            <i class="fas fa-plus me-1"></i>Create First Ticket
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Agent Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Agent</label>
                            <select name="assigned_agent_id" class="form-select" required>
                                <option value="">Unassigned</option>
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Internal Note (Optional)</label>
                            <textarea name="internal_note" class="form-control" rows="3" 
                                      placeholder="Add a note about this assignment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-admin">Assign Agent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Select all checkbox functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.ticket-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Assign modal functionality
        const assignModal = document.getElementById('assignModal');
        assignModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const ticketId = button.getAttribute('data-ticket-id');
            const form = document.getElementById('assignForm');
            form.action = `/admin/support/tickets/${ticketId}/assign`;
        });
    </script>
</body>
</html>