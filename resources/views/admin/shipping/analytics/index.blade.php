@extends('admin.layouts.app')

@section('title', 'Shipping Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Shipping Analytics</h1>
            <p class="mb-0 text-muted">Comprehensive shipping performance metrics and insights</p>
        </div>
        <div>
            <div class="btn-group me-2">
                <select class="form-select" onchange="updatePeriod(this.value)">
                    <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last Year</option>
                </select>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    Export Report
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.shipping.analytics.export', ['period' => $period, 'format' => 'csv']) }}">Export as CSV</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.shipping.analytics.export', ['period' => $period, 'format' => 'pdf']) }}">Export as PDF</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Shipments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($analytics['overview']['total_shipments']['current']) }}
                            </div>
                            @php
                                $change = $analytics['overview']['total_shipments']['current'] - $analytics['overview']['total_shipments']['previous'];
                                $percentage = $analytics['overview']['total_shipments']['previous'] > 0 ? 
                                    round(($change / $analytics['overview']['total_shipments']['previous']) * 100, 1) : 0;
                            @endphp
                            <div class="text-xs {{ $change >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="fas {{ $change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                {{ abs($percentage) }}% from previous period
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
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
                                Delivered
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($analytics['overview']['delivered_shipments']['current']) }}
                            </div>
                            @php
                                $deliveryRate = $analytics['overview']['total_shipments']['current'] > 0 ? 
                                    round(($analytics['overview']['delivered_shipments']['current'] / $analytics['overview']['total_shipments']['current']) * 100, 1) : 0;
                            @endphp
                            <div class="text-xs text-success">
                                {{ $deliveryRate }}% delivery rate
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg Delivery Time
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ round($analytics['overview']['average_delivery_time']['current'], 1) }}h
                            </div>
                            @php
                                $timeChange = $analytics['overview']['average_delivery_time']['current'] - $analytics['overview']['average_delivery_time']['previous'];
                            @endphp
                            <div class="text-xs {{ $timeChange <= 0 ? 'text-success' : 'text-warning' }}">
                                <i class="fas {{ $timeChange <= 0 ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                {{ abs(round($timeChange, 1)) }}h from previous
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Total Shipping Cost
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format($analytics['overview']['total_shipping_cost']['current'], 2) }}
                            </div>
                            @php
                                $costChange = $analytics['overview']['total_shipping_cost']['current'] - $analytics['overview']['total_shipping_cost']['previous'];
                                $costPercentage = $analytics['overview']['total_shipping_cost']['previous'] > 0 ? 
                                    round(($costChange / $analytics['overview']['total_shipping_cost']['previous']) * 100, 1) : 0;
                            @endphp
                            <div class="text-xs {{ $costChange <= 0 ? 'text-success' : 'text-warning' }}">
                                <i class="fas {{ $costChange <= 0 ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                {{ abs($costPercentage) }}% from previous
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Trends</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="#" onclick="loadPerformanceTrends()">Refresh Data</a>
                            <a class="dropdown-item" href="#" onclick="exportChart('performance')">Export Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Key Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-gray-500">Delivery Success Rate</div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $analytics['performance']['delivery_success_rate'] }}%"
                                 aria-valuenow="{{ $analytics['performance']['delivery_success_rate'] }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-right">{{ $analytics['performance']['delivery_success_rate'] }}%</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-gray-500">On-Time Delivery Rate</div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $analytics['performance']['on_time_delivery_rate'] }}%"
                                 aria-valuenow="{{ $analytics['performance']['on_time_delivery_rate'] }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-right">{{ $analytics['performance']['on_time_delivery_rate'] }}%</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-gray-500">Customer Satisfaction</div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ $analytics['performance']['customer_satisfaction'] }}%"
                                 aria-valuenow="{{ $analytics['performance']['customer_satisfaction'] }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-right">{{ $analytics['performance']['customer_satisfaction'] }}/100</div>
                    </div>

                    <div class="text-center mt-4">
                        <div class="h5 text-gray-800">₹{{ number_format($analytics['performance']['cost_per_shipment'], 2) }}</div>
                        <div class="small text-gray-500">Average Cost per Shipment</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrier Comparison -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Carrier Performance Comparison</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="carrierTable">
                            <thead>
                                <tr>
                                    <th>Carrier</th>
                                    <th>Total Shipments</th>
                                    <th>Delivered</th>
                                    <th>Exceptions</th>
                                    <th>Delivery Rate</th>
                                    <th>Avg Cost</th>
                                    <th>Avg Delivery Time</th>
                                    <th>Performance Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['carrier_comparison'] as $carrier)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="carrier-logo me-2">
                                                <i class="fas fa-truck text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $carrier['name'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($carrier['total_shipments']) }}</td>
                                    <td>
                                        <span class="badge badge-success">
                                            {{ number_format($carrier['delivered_shipments']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ number_format($carrier['exception_shipments']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $carrier['delivery_rate'] }}%"
                                                 aria-valuenow="{{ $carrier['delivery_rate'] }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ round($carrier['delivery_rate'], 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>₹{{ number_format($carrier['avg_cost'], 2) }}</td>
                                    <td>{{ round($carrier['avg_delivery_time'], 1) }}h</td>
                                    <td>
                                        @php
                                            $score = ($carrier['delivery_rate'] + (100 - ($carrier['exception_shipments'] / max($carrier['total_shipments'], 1) * 100))) / 2;
                                        @endphp
                                        <span class="badge badge-{{ $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger') }}">
                                            {{ round($score, 1) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewCarrierDetails('{{ $carrier['name'] }}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-info" onclick="downloadCarrierReport('{{ $carrier['name'] }}')">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Geographical Analysis -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Geographical Performance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="geographicalTable">
                            <thead>
                                <tr>
                                    <th>State</th>
                                    <th>Total Shipments</th>
                                    <th>Delivered</th>
                                    <th>Delivery Rate</th>
                                    <th>Avg Cost</th>
                                    <th>Avg Delivery Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['geographical_analysis'] as $location)
                                <tr>
                                    <td>{{ $location->state }}</td>
                                    <td>{{ number_format($location->total_shipments) }}</td>
                                    <td>{{ number_format($location->delivered_shipments) }}</td>
                                    <td>
                                        @php
                                            $rate = $location->total_shipments > 0 ? 
                                                ($location->delivered_shipments / $location->total_shipments) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $rate }}%">
                                                {{ round($rate, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>₹{{ number_format($location->avg_cost, 2) }}</td>
                                    <td>{{ round($location->avg_delivery_hours, 1) }}h</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cost Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="costChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Base Cost
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Fuel Surcharge
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Handling
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Other
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exception Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Exception Analysis</h6>
                    <a href="{{ route('admin.shipping.analytics.cost-optimization', ['period' => $period]) }}" 
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-chart-line"></i> Cost Optimization
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-danger">{{ $analytics['overview']['exception_rate']['current'] }}%</div>
                                <div class="small text-gray-500">Exception Rate</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info">24h</div>
                                <div class="small text-gray-500">Avg Resolution Time</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success">92%</div>
                                <div class="small text-gray-500">Resolution Rate</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-warning">₹15,000</div>
                                <div class="small text-gray-500">Exception Cost</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function updatePeriod(period) {
    window.location.href = '{{ route("admin.shipping.analytics.index") }}?period=' + period;
}

function loadPerformanceTrends() {
    fetch('{{ route("admin.shipping.analytics.performance-trends") }}?period={{ $period }}')
        .then(response => response.json())
        .then(data => {
            updatePerformanceChart(data);
        });
}

function updatePerformanceChart(data) {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.date),
            datasets: [{
                label: 'Total Shipments',
                data: data.map(item => item.total_shipments),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Delivered',
                data: data.map(item => item.delivered),
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1
            }, {
                label: 'Exceptions',
                data: data.map(item => item.exceptions),
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Initialize cost breakdown chart
const costCtx = document.getElementById('costChart').getContext('2d');
new Chart(costCtx, {
    type: 'doughnut',
    data: {
        labels: ['Base Cost', 'Fuel Surcharge', 'Handling', 'Other'],
        datasets: [{
            data: [
                {{ $analytics['cost_analysis']['cost_breakdown']['base_cost'] }},
                {{ $analytics['cost_analysis']['cost_breakdown']['fuel_surcharge'] }},
                {{ $analytics['cost_analysis']['cost_breakdown']['handling_fee'] }},
                {{ $analytics['cost_analysis']['cost_breakdown']['other'] }}
            ],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Initialize performance chart
loadPerformanceTrends();

function viewCarrierDetails(carrierName) {
    // Implementation for viewing carrier details
    alert('View details for ' + carrierName);
}

function downloadCarrierReport(carrierName) {
    // Implementation for downloading carrier report
    alert('Download report for ' + carrierName);
}

function exportChart(chartType) {
    // Implementation for exporting chart
    alert('Export ' + chartType + ' chart');
}
</script>
@endpush
@endsection