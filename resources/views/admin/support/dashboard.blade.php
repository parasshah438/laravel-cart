<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Dashboard</title>
    
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
        
        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
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
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .badge-priority-high {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
        }
        
        .badge-priority-medium {
            background: linear-gradient(45deg, #ffa726, #ffb74d);
        }
        
        .badge-priority-low {
            background: linear-gradient(45deg, #66bb6a, #81c784);
        }
        
        .badge-status-open {
            background: linear-gradient(45deg, #42a5f5, #64b5f6);
        }
        
        .badge-status-in-progress {
            background: linear-gradient(45deg, #ff9800, #ffb74d);
        }
        
        .badge-status-resolved {
            background: linear-gradient(45deg, #4caf50, #66bb6a);
        }
        
        .badge-status-closed {
            background: linear-gradient(45deg, #9e9e9e, #bdbdbd);
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar navbar-expand-lg admin-header text-white">
        <div class="container-fluid">
            <a class="navbar-brand text-white fw-bold" href="{{ route('admin.support.dashboard') }}">
                <i class="fas fa-shield-alt me-2"></i>Admin Support Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="{{ route('support.index') }}">
                    <i class="fas fa-arrow-left me-1"></i>Back to Customer Support
                </a>
                <a class="nav-link text-white" href="#">
                    <i class="fas fa-user me-1"></i>{{ auth()->user()->name ?? 'Admin' }}
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card text-white p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="mb-0">{{ $totalTickets }}</h3>
                            <p class="mb-0 opacity-75">Total Tickets</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card text-white p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="mb-0">{{ $openTickets }}</h3>
                            <p class="mb-0 opacity-75">Open Tickets</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-folder-open"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card text-white p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="mb-0">{{ $inProgressTickets }}</h3>
                            <p class="mb-0 opacity-75">In Progress</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card text-white p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="mb-0">{{ $todayTickets }}</h3>
                            <p class="mb-0 opacity-75">Today's Tickets</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="content-card p-4">
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-admin">
                            <i class="fas fa-list me-2"></i>Manage All Tickets
                        </a>
                        <a href="{{ route('admin.support.tickets.create') }}" class="btn btn-admin">
                            <i class="fas fa-plus me-2"></i>Create Internal Ticket
                        </a>
                        <a href="{{ route('admin.support.agents.index') }}" class="btn btn-admin">
                            <i class="fas fa-users me-2"></i>Manage Agents
                        </a>
                        <a href="{{ route('admin.support.analytics') }}" class="btn btn-admin">
                            <i class="fas fa-chart-bar me-2"></i>Analytics
                        </a>
                        <a href="{{ route('admin.support.settings') }}" class="btn btn-admin">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tickets -->
        <div class="row">
            <div class="col-12">
                <div class="content-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2 text-primary"></i>Recent Tickets
                        </h5>
                        <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-primary btn-sm">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Customer</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned Agent</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTickets as $ticket)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark">#{{ $ticket->id }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.support.tickets.show', $ticket) }}" 
                                           class="text-decoration-none fw-medium">
                                            {{ Str::limit($ticket->subject, 40) }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                                {{ substr($ticket->user->name, 0, 1) }}
                                            </div>
                                            {{ $ticket->user->name }}
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
                                            {{ $ticket->created_at->diffForHumans() }}
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
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3"></i>
                                            <p class="mb-0">No tickets found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>