@extends('layouts.admin')

@section('title', 'Track Shipment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Track Shipment</h3>
                        <p class="text-muted mb-0">
                            Tracking: <code>{{ $shipment->tracking_number }}</code> | 
                            Order: #{{ $shipment->order->order_number }}
                        </p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary me-2" onclick="refreshTracking()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Current Status -->
                        <div class="col-md-12 mb-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h4 class="mb-3">Current Status</h4>
                                    <span class="badge bg-{{ $shipment->getStatusColor() }} fs-5 px-4 py-2">
                                        {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                    </span>
                                    
                                    @if($shipment->estimated_delivery_date)
                                        <p class="mt-3 mb-0">
                                            <strong>Estimated Delivery:</strong> 
                                            {{ $shipment->estimated_delivery_date->format('M d, Y') }}
                                        </p>
                                    @endif

                                    @if($latestEvent = $shipment->trackingEvents->sortByDesc('event_time')->first())
                                        <p class="mt-2 mb-0 text-muted">
                                            <strong>Last Update:</strong> {{ $latestEvent->event_time->diffForHumans() }}
                                            @if($latestEvent->location)
                                                at {{ $latestEvent->location }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tracking Progress -->
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Shipping Progress</h5>
                                </div>
                                <div class="card-body">
                                    <div class="progress-steps">
                                        <div class="step {{ in_array($shipment->status, ['confirmed', 'picked_up', 'in_transit', 'delivered']) ? 'completed' : ($shipment->status === 'pending' ? 'active' : '') }}">
                                            <div class="step-icon">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="step-content">
                                                <h6>Order Confirmed</h6>
                                                <small>Shipment created and ready for pickup</small>
                                            </div>
                                        </div>

                                        <div class="step {{ in_array($shipment->status, ['picked_up', 'in_transit', 'delivered']) ? 'completed' : ($shipment->status === 'confirmed' ? 'active' : '') }}">
                                            <div class="step-icon">
                                                <i class="fas fa-truck-pickup"></i>
                                            </div>
                                            <div class="step-content">
                                                <h6>Picked Up</h6>
                                                <small>Package collected by carrier</small>
                                            </div>
                                        </div>

                                        <div class="step {{ in_array($shipment->status, ['in_transit', 'delivered']) ? 'completed' : ($shipment->status === 'picked_up' ? 'active' : '') }}">
                                            <div class="step-icon">
                                                <i class="fas fa-shipping-fast"></i>
                                            </div>
                                            <div class="step-content">
                                                <h6>In Transit</h6>
                                                <small>Package on the way to destination</small>
                                            </div>
                                        </div>

                                        <div class="step {{ $shipment->status === 'delivered' ? 'completed' : ($shipment->status === 'out_for_delivery' ? 'active' : '') }}">
                                            <div class="step-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <div class="step-content">
                                                <h6>Delivered</h6>
                                                <small>Package delivered successfully</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipment Details -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Shipment Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Tracking Number:</strong></td>
                                            <td><code>{{ $shipment->tracking_number }}</code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Carrier:</strong></td>
                                            <td>{{ $shipment->carrier->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Service:</strong></td>
                                            <td>{{ $shipment->method->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Weight:</strong></td>
                                            <td>{{ $shipment->weight }} kg</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dimensions:</strong></td>
                                            <td>{{ $shipment->length }} × {{ $shipment->width }} × {{ $shipment->height }} cm</td>
                                        </tr>
                                        @if($shipment->shipped_at)
                                            <tr>
                                                <td><strong>Shipped Date:</strong></td>
                                                <td>{{ $shipment->shipped_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @endif
                                        @if($shipment->estimated_delivery_date)
                                            <tr>
                                                <td><strong>Est. Delivery:</strong></td>
                                                <td>{{ $shipment->estimated_delivery_date->format('M d, Y') }}</td>
                                            </tr>
                                        @endif
                                        @if($shipment->delivered_at)
                                            <tr>
                                                <td><strong>Delivered Date:</strong></td>
                                                <td>{{ $shipment->delivered_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Delivery Address</h5>
                                </div>
                                <div class="card-body">
                                    <address>
                                        <strong>{{ $shipment->order->shipping_name }}</strong><br>
                                        {{ $shipment->order->shipping_address_line_1 }}<br>
                                        @if($shipment->order->shipping_address_line_2)
                                            {{ $shipment->order->shipping_address_line_2 }}<br>
                                        @endif
                                        {{ $shipment->order->shipping_city }}, {{ $shipment->order->shipping_state }} {{ $shipment->order->shipping_postal_code }}<br>
                                        {{ $shipment->order->shipping_country }}<br>
                                        @if($shipment->order->shipping_phone)
                                            <strong>Phone:</strong> {{ $shipment->order->shipping_phone }}
                                        @endif
                                    </address>
                                </div>
                            </div>
                        </div>

                        <!-- Tracking Timeline -->
                        <div class="col-md-12 mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Tracking Timeline</h5>
                                </div>
                                <div class="card-body">
                                    @if($shipment->trackingEvents->count() > 0)
                                        <div class="tracking-timeline">
                                            @foreach($shipment->trackingEvents->sortByDesc('event_time') as $event)
                                                <div class="timeline-item">
                                                    <div class="timeline-marker bg-{{ $event->getStatusColor() }}">
                                                        <i class="fas {{ $event->getStatusIcon() }}"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h6 class="timeline-title">{{ $event->event_type }}</h6>
                                                                <p class="timeline-description mb-1">{{ $event->description }}</p>
                                                                @if($event->location)
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-map-marker-alt"></i> {{ $event->location }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                            <div class="text-end">
                                                                <small class="text-muted">
                                                                    {{ $event->event_time->format('M d, Y') }}<br>
                                                                    {{ $event->event_time->format('H:i') }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-route fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No tracking events found</p>
                                            <button type="button" class="btn btn-primary" onclick="refreshTracking()">
                                                <i class="fas fa-sync-alt"></i> Fetch Tracking Data
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshTracking() {
    const btn = $('button:contains("Refresh")');
    const originalText = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
    
    $.post(`/admin/shipments/{{ $shipment->id }}/refresh-tracking`, {
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            toastr.success('Tracking information updated successfully');
            location.reload();
        } else {
            toastr.error(response.message || 'Failed to update tracking');
        }
    })
    .fail(function() {
        toastr.error('Failed to update tracking information');
    })
    .always(function() {
        btn.html(originalText).prop('disabled', false);
    });
}

// Auto-refresh every 5 minutes
setInterval(function() {
    if (document.visibilityState === 'visible') {
        refreshTracking();
    }
}, 300000); // 5 minutes
</script>
@endpush

@push('styles')
<style>
.progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    margin: 2rem 0;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    height: 2px;
    background: #dee2e6;
    z-index: 1;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.step.active .step-icon {
    background: #007bff;
    transform: scale(1.1);
}

.step.completed .step-icon {
    background: #28a745;
}

.step-content h6 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.step-content small {
    color: #6c757d;
    font-size: 0.8rem;
}

.tracking-timeline {
    position: relative;
    padding-left: 40px;
}

.tracking-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.timeline-title {
    margin: 0 0 8px 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.timeline-description {
    margin: 0;
    color: #6c757d;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .progress-steps {
        flex-direction: column;
        gap: 20px;
    }
    
    .progress-steps::before {
        display: none;
    }
    
    .step {
        flex-direction: row;
        text-align: left;
        width: 100%;
    }
    
    .step-icon {
        margin-right: 15px;
        margin-bottom: 0;
        flex-shrink: 0;
    }
}
</style>
@endpush