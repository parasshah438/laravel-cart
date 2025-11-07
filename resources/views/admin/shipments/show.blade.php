@extends('layouts.admin')

@section('title', 'Shipment Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Shipment Details</h3>
                        <p class="text-muted mb-0">Order #{{ $shipment->order->order_number }}</p>
                    </div>
                    <div>
                        @if($shipment->status === 'pending')
                            <button type="button" class="btn btn-success me-2" onclick="processShipment({{ $shipment->id }})">
                                <i class="fas fa-play"></i> Process Shipment
                            </button>
                        @endif
                        @if(in_array($shipment->status, ['pending', 'confirmed']))
                            <button type="button" class="btn btn-danger me-2" onclick="cancelShipment({{ $shipment->id }})">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        @endif
                        @if($shipment->tracking_number)
                            <a href="{{ route('admin.shipments.track', $shipment) }}" class="btn btn-info me-2">
                                <i class="fas fa-route"></i> Track
                            </a>
                        @endif
                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Shipment Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Shipment Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Shipment ID:</strong></td>
                                            <td>{{ $shipment->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tracking Number:</strong></td>
                                            <td>
                                                @if($shipment->tracking_number)
                                                    <code>{{ $shipment->tracking_number }}</code>
                                                @else
                                                    <span class="text-muted">Not assigned</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $shipment->getStatusColor() }} fs-6">
                                                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Carrier:</strong></td>
                                            <td>{{ $shipment->carrier->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Method:</strong></td>
                                            <td>{{ $shipment->method->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Shipping Cost:</strong></td>
                                            <td>₹{{ number_format($shipment->shipping_cost, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Insurance:</strong></td>
                                            <td>₹{{ number_format($shipment->insurance_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>COD Charges:</strong></td>
                                            <td>₹{{ number_format($shipment->cod_charges, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Amount:</strong></td>
                                            <td><strong>₹{{ number_format($shipment->total_amount, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Weight:</strong></td>
                                            <td>{{ $shipment->weight }} kg</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dimensions:</strong></td>
                                            <td>{{ $shipment->length }} × {{ $shipment->width }} × {{ $shipment->height }} cm</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $shipment->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        @if($shipment->shipped_at)
                                            <tr>
                                                <td><strong>Shipped:</strong></td>
                                                <td>{{ $shipment->shipped_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @endif
                                        @if($shipment->delivered_at)
                                            <tr>
                                                <td><strong>Delivered:</strong></td>
                                                <td>{{ $shipment->delivered_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Order & Customer Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Order Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Order Number:</strong></td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $shipment->order) }}">
                                                    #{{ $shipment->order->order_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Customer:</strong></td>
                                            <td>{{ $shipment->order->user->name ?? 'Guest' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $shipment->order->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $shipment->order->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Order Total:</strong></td>
                                            <td>₹{{ number_format($shipment->order->total_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Payment Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $shipment->order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($shipment->order->payment_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Shipping Address -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5>Shipping Address</h5>
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
                    </div>

                    <!-- Order Items -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Order Items</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>SKU</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($shipment->order->items as $item)
                                                    <tr>
                                                        <td>{{ $item->product_name }}</td>
                                                        <td><code>{{ $item->product_sku }}</code></td>
                                                        <td>{{ $item->quantity }}</td>
                                                        <td>₹{{ number_format($item->price, 2) }}</td>
                                                        <td>₹{{ number_format($item->quantity * $item->price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4">Subtotal:</th>
                                                    <th>₹{{ number_format($shipment->order->subtotal, 2) }}</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="4">Shipping:</th>
                                                    <th>₹{{ number_format($shipment->order->shipping_amount, 2) }}</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="4">Tax:</th>
                                                    <th>₹{{ number_format($shipment->order->tax_amount, 2) }}</th>
                                                </tr>
                                                <tr class="table-success">
                                                    <th colspan="4">Total:</th>
                                                    <th>₹{{ number_format($shipment->order->total_amount, 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Events -->
                    @if($shipment->trackingEvents->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Tracking History</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="timeline">
                                            @foreach($shipment->trackingEvents->sortByDesc('event_time') as $event)
                                                <div class="timeline-item">
                                                    <div class="timeline-marker bg-primary"></div>
                                                    <div class="timeline-content">
                                                        <h6 class="timeline-title">{{ $event->event_type }}</h6>
                                                        <p class="timeline-description">{{ $event->description }}</p>
                                                        @if($event->location)
                                                            <small class="text-muted">
                                                                <i class="fas fa-map-marker-alt"></i> {{ $event->location }}
                                                            </small>
                                                        @endif
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-clock"></i> {{ $event->event_time->format('M d, Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function processShipment(id) {
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
}

function cancelShipment(id) {
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
}
</script>
@endpush

@push('styles')
<style>
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
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}

.timeline-title {
    margin: 0 0 10px 0;
    font-size: 1rem;
    font-weight: 600;
}

.timeline-description {
    margin: 0 0 10px 0;
    color: #666;
}
</style>
@endpush