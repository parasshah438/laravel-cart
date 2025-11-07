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
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Shipments Management</h3>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkProcessModal">
                            <i class="fas fa-shipping-fast"></i> Bulk Process
                        </button>
                        <a href="{{ route('admin.shipments.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Create Shipment
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                                    <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="carrier" class="form-control">
                                    <option value="">All Carriers</option>
                                    @foreach($carriers as $carrier)
                                        <option value="{{ $carrier->id }}" {{ request('carrier') == $carrier->id ? 'selected' : '' }}>
                                            {{ $carrier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search tracking number, order ID..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Shipments Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Order ID</th>
                                    <th>Tracking Number</th>
                                    <th>Customer</th>
                                    <th>Carrier</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shipments as $shipment)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="shipment-checkbox" value="{{ $shipment->id }}">
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $shipment->order) }}" class="text-decoration-none">
                                                #{{ $shipment->order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($shipment->tracking_number)
                                                <code>{{ $shipment->tracking_number }}</code>
                                                <a href="{{ route('admin.shipments.track', $shipment) }}" class="btn btn-sm btn-link p-0 ms-2">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td>{{ $shipment->order->user->name ?? 'Guest' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $shipment->carrier->name }}</span>
                                        </td>
                                        <td>{{ $shipment->method->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $shipment->getStatusColor() }}">
                                                {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                            </span>
                                        </td>
                                        <td>₹{{ number_format($shipment->total_amount, 2) }}</td>
                                        <td>{{ $shipment->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($shipment->status === 'pending')
                                                    <a href="{{ route('admin.shipments.edit', $shipment) }}" class="btn btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-success process-shipment" data-id="{{ $shipment->id }}">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                                @if(in_array($shipment->status, ['pending', 'confirmed']))
                                                    <button type="button" class="btn btn-danger cancel-shipment" data-id="{{ $shipment->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No shipments found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            Showing {{ $shipments->firstItem() ?? 0 }} to {{ $shipments->lastItem() ?? 0 }} of {{ $shipments->total() }} shipments
                        </div>
                        <div>
                            {{ $shipments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Process Modal -->
<div class="modal fade" id="bulkProcessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Process Shipments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkProcessForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-control" required>
                            <option value="">Select Action</option>
                            <option value="process">Process Selected</option>
                            <option value="cancel">Cancel Selected</option>
                            <option value="update_status">Update Status</option>
                        </select>
                    </div>
                    <div class="mb-3" id="statusField" style="display: none;">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="picked_up">Picked Up</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <strong>Selected shipments:</strong> <span id="selectedCount">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('.shipment-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    $('.shipment-checkbox').change(function() {
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const count = $('.shipment-checkbox:checked').length;
        $('#selectedCount').text(count);
        $('#selectAll').prop('checked', count > 0 && count === $('.shipment-checkbox').length);
    }

    // Show/hide status field based on action
    $('select[name="action"]').change(function() {
        if ($(this).val() === 'update_status') {
            $('#statusField').show();
        } else {
            $('#statusField').hide();
        }
    });

    // Process single shipment
    $('.process-shipment').click(function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to process this shipment?')) {
            $.post(`/admin/shipments/${id}/process`, {
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            }).fail(function() {
                toastr.error('Failed to process shipment');
            });
        }
    });

    // Cancel single shipment
    $('.cancel-shipment').click(function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to cancel this shipment?')) {
            $.post(`/admin/shipments/${id}/cancel`, {
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            }).fail(function() {
                toastr.error('Failed to cancel shipment');
            });
        }
    });

    // Bulk process form
    $('#bulkProcessForm').submit(function(e) {
        e.preventDefault();
        
        const selectedIds = $('.shipment-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one shipment');
            return;
        }

        const formData = $(this).serialize() + '&ids=' + selectedIds.join(',');
        
        $.post('/admin/shipments/bulk-action', formData + '&_token={{ csrf_token() }}')
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#bulkProcessModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            })
            .fail(function() {
                toastr.error('Failed to process bulk action');
            });
    });
});
</script>



<style>
.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.8em;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.4rem;
}
</style>
