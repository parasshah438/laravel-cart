@extends('layouts.admin')

@section('title', 'Payment Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Payment Analytics Dashboard</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> All Payments
            </a>
            <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Quick Range</label>
                    <select name="date_range" class="form-select" onchange="this.form.submit()">
                        <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last Year</option>
                        <option value="custom" {{ request('start_date') ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-select" 
                           value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-select" 
                           value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format($analytics['total_amount'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Successful Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['successful_payments'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Failed Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['failed_payments'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Order Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format($analytics['average_amount'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Payment Trends -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daily Payment Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="paymentTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Methods</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($analytics['gateway_breakdown'] as $gateway => $count)
                        <span class="mr-2">
                            <i class="fas fa-circle text-{{ $gateway === 'razorpay' ? 'primary' : 'success' }}"></i>
                            {{ ucfirst($gateway) }}: {{ $count }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments and Failed Payments -->
    <div class="row">
        <!-- Recent Payments -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Payments</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-decoration-none">
                                            {{ $payment->payment_id }}
                                        </a>
                                    </td>
                                    <td>{{ $payment->user->name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->payment_status === 'paid' ? 'success' : ($payment->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($payment->payment_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No recent payments found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed Payments -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Failed Payments (Need Review)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($failedPayments as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-decoration-none">
                                            {{ $payment->payment_id }}
                                        </a>
                                    </td>
                                    <td>{{ $payment->user->name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <small>{{ Str::limit($payment->failure_reason ?? 'Unknown', 30) }}</small>
                                    </td>
                                    <td>{{ $payment->failed_at ? $payment->failed_at->format('M d, Y H:i') : $payment->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No failed payments found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Payment Trends Chart
const ctx1 = document.getElementById('paymentTrendChart').getContext('2d');
const paymentTrendData = @json($dailyTrend);

new Chart(ctx1, {
    type: 'line',
    data: {
        labels: paymentTrendData.map(item => item.date),
        datasets: [{
            label: 'Revenue (₹)',
            data: paymentTrendData.map(item => item.total),
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            yAxisID: 'y'
        }, {
            label: 'Payment Count',
            data: paymentTrendData.map(item => item.count),
            borderColor: 'rgb(28, 200, 138)',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            tension: 0.3,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (₹)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Payment Count'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});

// Payment Methods Pie Chart
const ctx2 = document.getElementById('paymentMethodChart').getContext('2d');
const gatewayData = @json($analytics['gateway_breakdown']);

new Chart(ctx2, {
    type: 'pie',
    data: {
        labels: Object.keys(gatewayData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
        datasets: [{
            data: Object.values(gatewayData),
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
                '#e74a3b'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection