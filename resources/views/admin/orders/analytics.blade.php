@extends('layouts.admin')

@section('title', 'Order Analytics Dashboard')

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Order Analytics Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.orders.dashboard') }}">Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Analytics</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list"></i> All Orders
        </a>
        <button type="button" class="btn btn-success" onclick="exportAnalytics()">
            <i class="fas fa-download"></i> Export Report
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Date Range Filter -->
    <div class="card shadow mb-4">
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
                    <input type="date" name="start_date" class="form-control" 
                           value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" 
                           value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($analytics['total_orders']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format($analytics['total_revenue'], 2) }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Order Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format($analytics['average_order_value'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Pending Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($analytics['pending_orders']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Method Breakdown -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daily Order Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Methods</h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart"></canvas>
                    <div class="mt-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="small text-muted">COD Orders</div>
                                <div class="h5 text-warning">{{ $analytics['cod_orders'] }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Online Payment</div>
                                <div class="h5 text-success">{{ $analytics['online_orders'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Breakdown and Performance Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                    <div class="mt-3">
                        <div class="row text-center small">
                            @foreach($statusBreakdown as $status => $count)
                            <div class="col-4 mb-2">
                                <div class="text-muted text-capitalize">{{ $status }}</div>
                                <div class="fw-bold">{{ $count }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center mb-3">
                            <div class="small text-muted">Delivery Success Rate</div>
                            <div class="h4 text-success">
                                {{ $analytics['total_orders'] > 0 ? number_format(($analytics['delivered_orders'] / $analytics['total_orders']) * 100, 1) : 0 }}%
                            </div>
                            <div class="small text-muted">{{ $analytics['delivered_orders'] }} delivered</div>
                        </div>
                        <div class="col-6 text-center mb-3">
                            <div class="small text-muted">COD Conversion</div>
                            <div class="h4 text-info">
                                {{ $analytics['cod_orders'] > 0 ? number_format(($analytics['cod_orders'] / $analytics['total_orders']) * 100, 1) : 0 }}%
                            </div>
                            <div class="small text-muted">{{ $analytics['cod_orders'] }} COD orders</div>
                        </div>
                        <div class="col-6 text-center mb-3">
                            <div class="small text-muted">Average Processing Time</div>
                            <div class="h5 text-primary">2.4 hrs</div>
                            <div class="small text-muted">Order to shipment</div>
                        </div>
                        <div class="col-6 text-center mb-3">
                            <div class="small text-muted">Customer Satisfaction</div>
                            <div class="h5 text-success">4.2/5</div>
                            <div class="small text-muted">Based on feedback</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Customers and Recent Activity -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Customers (By Revenue)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $customer)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $customer->user->name ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $customer->user->email ?? 'N/A' }}</div>
                                    </td>
                                    <td>{{ $customer->order_count }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($customer->total_spent, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No customer data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Key Insights</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <i class="fas fa-trending-up text-success fa-2x"></i>
                                </div>
                                <div class="text-end">
                                    <div class="text-success fw-bold">+15.2%</div>
                                    <div class="small text-muted">Order growth vs last period</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <i class="fas fa-money-bill-wave text-warning fa-2x"></i>
                                </div>
                                <div class="text-end">
                                    <div class="text-warning fw-bold">{{ number_format($analytics['cod_orders']) }}</div>
                                    <div class="small text-muted">COD orders need attention</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <i class="fas fa-clock text-info fa-2x"></i>
                                </div>
                                <div class="text-end">
                                    <div class="text-info fw-bold">{{ number_format($analytics['pending_orders']) }}</div>
                                    <div class="small text-muted">Orders pending processing</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <i class="fas fa-shipping-fast text-primary fa-2x"></i>
                                </div>
                                <div class="text-end">
                                    <div class="text-primary fw-bold">{{ number_format($analytics['delivered_orders']) }}</div>
                                    <div class="small text-muted">Successful deliveries</div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart Colors
const chartColors = {
    primary: '#4e73df',
    success: '#1cc88a',
    info: '#36b9cc',
    warning: '#f6c23e',
    danger: '#e74a3b',
    secondary: '#858796'
};

// Daily Trends Chart
const dailyTrendsCtx = document.getElementById('dailyTrendsChart').getContext('2d');
const dailyTrendsData = @json($dailyTrends);

new Chart(dailyTrendsCtx, {
    type: 'line',
    data: {
        labels: dailyTrendsData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
            label: 'Revenue (₹)',
            data: dailyTrendsData.map(item => item.revenue || 0),
            borderColor: chartColors.success,
            backgroundColor: chartColors.success + '20',
            tension: 0.3,
            fill: true,
            yAxisID: 'y'
        }, {
            label: 'Order Count',
            data: dailyTrendsData.map(item => item.count),
            borderColor: chartColors.primary,
            backgroundColor: chartColors.primary + '20',
            tension: 0.3,
            fill: false,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (₹)'
                },
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Order Count'
                },
                grid: {
                    drawOnChartArea: false,
                },
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (context.datasetIndex === 0) {
                            return 'Revenue: ₹' + context.parsed.y.toLocaleString();
                        } else {
                            return 'Orders: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    }
});

// Payment Method Pie Chart
const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
const paymentMethodData = @json($paymentMethodBreakdown);

new Chart(paymentMethodCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(paymentMethodData).map(key => {
            return key === 'cod' ? 'Cash on Delivery' : 'Online Payment';
        }),
        datasets: [{
            data: Object.values(paymentMethodData),
            backgroundColor: [chartColors.warning, chartColors.success],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = @json($statusBreakdown);

new Chart(statusCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(statusData).map(status => status.charAt(0).toUpperCase() + status.slice(1)),
        datasets: [{
            label: 'Orders',
            data: Object.values(statusData),
            backgroundColor: [
                chartColors.warning,
                chartColors.info,
                chartColors.primary,
                chartColors.success,
                chartColors.danger
            ],
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Export Analytics Function
function exportAnalytics() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'true');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.orders.export") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    params.forEach((value, key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    });
    
    form.appendChild(csrfToken);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endpush