@extends('layouts.admin')

@section('title', 'Sales Analytics Dashboard')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line text-info me-2"></i>Sales Analytics Dashboard
        </h1>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dateRangeDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar-alt"></i> 
                    @switch($dateRange)
                        @case('7') Last 7 Days @break
                        @case('30') Last 30 Days @break
                        @case('90') Last 90 Days @break
                        @default Last 30 Days
                    @endswitch
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?range=7">Last 7 Days</a></li>
                    <li><a class="dropdown-item" href="?range=30">Last 30 Days</a></li>
                    <li><a class="dropdown-item" href="?range=90">Last 90 Days</a></li>
                </ul>
            </div>
            <a href="{{ route('admin.sales.analytics.export', ['type' => 'sales', 'range' => $dateRange]) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Export Data
            </a>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row mb-4">
        <!-- Total Sales -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Sales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($metrics['total_sales']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($metrics['total_revenue'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Savings -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Customer Savings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($metrics['total_savings'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Events -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Events
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['active_events'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-fire fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daily Sales Trend -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Daily Sales Trend
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="dailySalesChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>

        <!-- Conversion Rate -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-percentage"></i> Conversion Rate
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: {{ $metrics['conversion_rate'] }}%">
                                {{ $metrics['conversion_rate'] }}%
                            </div>
                        </div>
                    </div>
                    <h4 class="text-success mb-2">{{ $metrics['conversion_rate'] }}%</h4>
                    <p class="text-muted mb-0">Overall Conversion Rate</p>
                    
                    @if($metrics['conversion_rate'] < 2)
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><i class="fas fa-info-circle"></i> Consider optimizing your sales strategies to improve conversion.</small>
                        </div>
                    @elseif($metrics['conversion_rate'] > 5)
                        <div class="alert alert-success mt-3 mb-0">
                            <small><i class="fas fa-check-circle"></i> Excellent conversion rate! Keep up the good work.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tables Row -->
    <div class="row">
        <!-- Sales by Event -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-fire"></i> Sales by Event
                    </h6>
                </div>
                <div class="card-body">
                    @if($salesByEvent->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th class="text-center">Orders</th>
                                        <th class="text-center">Revenue</th>
                                        <th class="text-center">Savings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesByEvent->take(5) as $event)
                                        <tr>
                                            <td>
                                                <strong>{{ $event['name'] }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $event['orders'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-success font-weight-bold">
                                                    ${{ number_format($event['revenue'], 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-warning font-weight-bold">
                                                    ${{ number_format($event['savings'], 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-fire fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No sale events data available for the selected period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Performing Products -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy"></i> Top Performing Products
                    </h6>
                </div>
                <div class="card-body">
                    @if($topProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Orders</th>
                                        <th class="text-center">Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topProducts->take(5) as $product)
                                        <tr>
                                            <td>
                                                <strong>{{ Str::limit($product->name, 30) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $product->order_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-primary font-weight-bold">{{ $product->total_sold }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No product sales data available for the selected period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Updates -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-broadcast-tower"></i> Real-time Activity
                        <small class="text-muted">(Updates every 30 seconds)</small>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center" id="realTimeStats">
                        <div class="col-md-3">
                            <div class="border-right">
                                <h4 class="text-info" id="activeUsers">-</h4>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-right">
                                <h4 class="text-primary" id="ordersLastHour">-</h4>
                                <small class="text-muted">Orders (Last Hour)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-right">
                                <h4 class="text-success" id="revenueLastHour">-</h4>
                                <small class="text-muted">Revenue (Last Hour)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning" id="activeEventsCount">-</h4>
                            <small class="text-muted">Active Events</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Sales Chart
    const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
    const dailySalesData = @json($dailySales);
    
    const dailySalesChart = new Chart(dailySalesCtx, {
        type: 'line',
        data: {
            labels: dailySalesData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Orders',
                data: dailySalesData.map(item => item.orders),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Revenue ($)',
                data: dailySalesData.map(item => item.revenue),
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
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
                        text: 'Orders'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue ($)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Real-time updates
    function updateRealTimeStats() {
        fetch('{{ route("admin.sales.analytics.api.real-time") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('activeUsers').textContent = data.total_active_users || '-';
                document.getElementById('ordersLastHour').textContent = data.orders_last_hour || '0';
                document.getElementById('revenueLastHour').textContent = '$' + (data.revenue_last_hour || 0).toFixed(2);
                document.getElementById('activeEventsCount').textContent = data.active_events || '0';
            })
            .catch(error => {
                console.error('Error fetching real-time data:', error);
            });
    }

    // Update immediately and then every 30 seconds
    updateRealTimeStats();
    setInterval(updateRealTimeStats, 30000);
});
</script>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-right {
    border-right: 1px solid #e3e6f0;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
</style>
@endpush

@endsection