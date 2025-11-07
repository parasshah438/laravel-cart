@extends('layouts.admin')

@section('title', 'Create Shipment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Shipment</h3>
                    <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary float-end">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

                <form action="{{ route('admin.shipments.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Order Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="order_id" class="form-label">Order *</label>
                                    <select name="order_id" id="order_id" class="form-control @error('order_id') is-invalid @enderror" required>
                                        <option value="">Select Order</option>
                                        @foreach($orders as $order)
                                            <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                                                #{{ $order->order_number }} - {{ $order->user->name ?? 'Guest' }} (₹{{ number_format($order->total_amount, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('order_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Carrier Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="shipping_carrier_id" class="form-label">Shipping Carrier *</label>
                                    <select name="shipping_carrier_id" id="shipping_carrier_id" class="form-control @error('shipping_carrier_id') is-invalid @enderror" required>
                                        <option value="">Select Carrier</option>
                                        @foreach($carriers as $carrier)
                                            <option value="{{ $carrier->id }}" {{ old('shipping_carrier_id') == $carrier->id ? 'selected' : '' }}>
                                                {{ $carrier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shipping_carrier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Method Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="shipping_method_id" class="form-label">Shipping Method *</label>
                                    <select name="shipping_method_id" id="shipping_method_id" class="form-control @error('shipping_method_id') is-invalid @enderror" required>
                                        <option value="">Select Method</option>
                                    </select>
                                    @error('shipping_method_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Package Weight -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="weight" class="form-label">Weight (kg) *</label>
                                    <input type="number" name="weight" id="weight" step="0.1" min="0.1" 
                                           class="form-control @error('weight') is-invalid @enderror" 
                                           value="{{ old('weight', '1.0') }}" required>
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Package Dimensions -->
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="length" class="form-label">Length (cm) *</label>
                                    <input type="number" name="length" id="length" step="0.1" min="1" 
                                           class="form-control @error('length') is-invalid @enderror" 
                                           value="{{ old('length', '20') }}" required>
                                    @error('length')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="width" class="form-label">Width (cm) *</label>
                                    <input type="number" name="width" id="width" step="0.1" min="1" 
                                           class="form-control @error('width') is-invalid @enderror" 
                                           value="{{ old('width', '15') }}" required>
                                    @error('width')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="height" class="form-label">Height (cm) *</label>
                                    <input type="number" name="height" id="height" step="0.1" min="1" 
                                           class="form-control @error('height') is-invalid @enderror" 
                                           value="{{ old('height', '10') }}" required>
                                    @error('height')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Shipping Cost -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="shipping_cost" class="form-label">Shipping Cost (₹)</label>
                                    <input type="number" name="shipping_cost" id="shipping_cost" step="0.01" min="0" 
                                           class="form-control @error('shipping_cost') is-invalid @enderror" 
                                           value="{{ old('shipping_cost', '0.00') }}" readonly>
                                    <small class="form-text text-muted">Will be calculated automatically based on method</small>
                                    @error('shipping_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Insurance Amount -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="insurance_amount" class="form-label">Insurance Amount (₹)</label>
                                    <input type="number" name="insurance_amount" id="insurance_amount" step="0.01" min="0" 
                                           class="form-control @error('insurance_amount') is-invalid @enderror" 
                                           value="{{ old('insurance_amount', '0.00') }}">
                                    @error('insurance_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- COD Charges -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="cod_charges" class="form-label">COD Charges (₹)</label>
                                    <input type="number" name="cod_charges" id="cod_charges" step="0.01" min="0" 
                                           class="form-control @error('cod_charges') is-invalid @enderror" 
                                           value="{{ old('cod_charges', '0.00') }}">
                                    @error('cod_charges')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Process Immediately -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="process_immediately" id="process_immediately" 
                                               class="form-check-input" value="1" {{ old('process_immediately') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="process_immediately">
                                            Process shipment immediately with ShipRocket
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              placeholder="Any special instructions or notes">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Order Preview -->
                        <div id="order-preview" class="mt-4" style="display: none;">
                            <h5>Order Details Preview</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div id="order-details">
                                        <!-- Order details will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Shipment
                        </button>
                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load shipping methods when carrier is selected
    $('#shipping_carrier_id').change(function() {
        const carrierId = $(this).val();
        const methodSelect = $('#shipping_method_id');
        
        methodSelect.html('<option value="">Loading...</option>');
        
        if (carrierId) {
            $.get(`/admin/carriers/${carrierId}/methods`)
                .done(function(methods) {
                    methodSelect.html('<option value="">Select Method</option>');
                    methods.forEach(function(method) {
                        methodSelect.append(`
                            <option value="${method.id}" 
                                    data-base-rate="${method.base_rate}" 
                                    data-per-kg-rate="${method.per_kg_rate}"
                                    data-cod-charges="${method.configuration.cod_charges || 0}">
                                ${method.name} - ${method.description}
                            </option>
                        `);
                    });
                })
                .fail(function() {
                    methodSelect.html('<option value="">Error loading methods</option>');
                });
        } else {
            methodSelect.html('<option value="">Select Method</option>');
        }
    });

    // Calculate shipping cost when method or weight changes
    $('#shipping_method_id, #weight').change(function() {
        calculateShippingCost();
    });

    function calculateShippingCost() {
        const methodOption = $('#shipping_method_id option:selected');
        const weight = parseFloat($('#weight').val()) || 0;
        
        if (methodOption.val() && weight > 0) {
            const baseRate = parseFloat(methodOption.data('base-rate')) || 0;
            const perKgRate = parseFloat(methodOption.data('per-kg-rate')) || 0;
            const codCharges = parseFloat(methodOption.data('cod-charges')) || 0;
            
            const shippingCost = baseRate + (weight * perKgRate);
            
            $('#shipping_cost').val(shippingCost.toFixed(2));
            $('#cod_charges').val(codCharges.toFixed(2));
        }
    }

    // Load order details when order is selected
    $('#order_id').change(function() {
        const orderId = $(this).val();
        const preview = $('#order-preview');
        const details = $('#order-details');
        
        if (orderId) {
            $.get(`/admin/orders/${orderId}/details`)
                .done(function(order) {
                    details.html(`
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p><strong>Name:</strong> ${order.user?.name || 'Guest'}<br>
                                <strong>Email:</strong> ${order.email}<br>
                                <strong>Phone:</strong> ${order.phone || 'N/A'}</p>
                                
                                <h6>Order Summary</h6>
                                <p><strong>Items:</strong> ${order.items_count}<br>
                                <strong>Total:</strong> ₹${parseFloat(order.total_amount).toFixed(2)}<br>
                                <strong>Payment:</strong> ${order.payment_status}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Shipping Address</h6>
                                <address>
                                    ${order.shipping_name}<br>
                                    ${order.shipping_address_line_1}<br>
                                    ${order.shipping_address_line_2 ? order.shipping_address_line_2 + '<br>' : ''}
                                    ${order.shipping_city}, ${order.shipping_state} ${order.shipping_postal_code}<br>
                                    ${order.shipping_country}
                                </address>
                            </div>
                        </div>
                    `);
                    preview.show();
                })
                .fail(function() {
                    details.html('<p class="text-danger">Error loading order details</p>');
                    preview.show();
                });
        } else {
            preview.hide();
        }
    });
});
</script>
@endpush