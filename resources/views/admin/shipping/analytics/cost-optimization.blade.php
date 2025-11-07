@extends('admin.layouts.app')

@section('title', 'Cost Optimization Recommendations')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Cost Optimization</h1>
            <p class="mb-0 text-muted">AI-powered recommendations to reduce shipping costs</p>
        </div>
        <div>
            <a href="{{ route('admin.shipping.analytics.index', ['period' => $period]) }}" 
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Analytics
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Potential Savings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format(array_sum([
                                    $recommendations['carrier_efficiency']['potential_savings'],
                                    $recommendations['zone_optimization']['potential_savings'],
                                    $recommendations['packaging_optimization']['potential_savings'],
                                    $recommendations['volume_discounts']['potential_savings']
                                ])) }}
                            </div>
                            <div class="text-xs text-success">Per month estimate</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Recommendations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4</div>
                            <div class="text-xs text-primary">Active opportunities</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-lightbulb fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Implementation Effort
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Medium</div>
                            <div class="text-xs text-info">Average complexity</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                ROI Timeline
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2-3 Months</div>
                            <div class="text-xs text-warning">Expected payback</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row">
        <!-- Carrier Efficiency -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-truck mr-2"></i>Carrier Efficiency Optimization
                    </h6>
                    <div class="badge badge-success">High Impact</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-success">₹{{ number_format($recommendations['carrier_efficiency']['potential_savings']) }}</h5>
                            <p class="text-muted small">Monthly savings potential</p>
                            
                            <p class="mb-3">{{ $recommendations['carrier_efficiency']['details'] }}</p>
                            
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 85%">
                                    85% Success Rate
                                </div>
                            </div>
                            
                            <p class="small text-muted mb-3">
                                <strong>Implementation:</strong> 
                                Reallocate shipments based on performance metrics and cost analysis.
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <div class="h2 text-success">15%</div>
                                <small class="text-muted">Cost Reduction</small>
                            </div>
                            <div class="mb-3">
                                <div class="h4 text-info">2 weeks</div>
                                <small class="text-muted">Implementation Time</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-play"></i> Implement Now
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fas fa-chart-line"></i> View Analysis
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone Optimization -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marked-alt mr-2"></i>Zone-Based Pricing Optimization
                    </h6>
                    <div class="badge badge-warning">Medium Impact</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-success">₹{{ number_format($recommendations['zone_optimization']['potential_savings']) }}</h5>
                            <p class="text-muted small">Monthly savings potential</p>
                            
                            <p class="mb-3">{{ $recommendations['zone_optimization']['details'] }}</p>
                            
                            <div class="progress mb-3">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 70%">
                                    70% Confidence
                                </div>
                            </div>
                            
                            <p class="small text-muted mb-3">
                                <strong>Implementation:</strong> 
                                Adjust pricing for underperforming zones while maintaining service quality.
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <div class="h2 text-warning">10%</div>
                                <small class="text-muted">Zone Cost Reduction</small>
                            </div>
                            <div class="mb-3">
                                <div class="h4 text-info">3 weeks</div>
                                <small class="text-muted">Implementation Time</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-warning btn-sm btn-block">
                                    <i class="fas fa-clock"></i> Schedule Review
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fas fa-map"></i> Zone Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Packaging Optimization -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-box mr-2"></i>Packaging Right-Sizing
                    </h6>
                    <div class="badge badge-info">High Impact</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-success">₹{{ number_format($recommendations['packaging_optimization']['potential_savings']) }}</h5>
                            <p class="text-muted small">Monthly savings potential</p>
                            
                            <p class="mb-3">{{ $recommendations['packaging_optimization']['details'] }}</p>
                            
                            <div class="progress mb-3">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 90%">
                                    90% Optimization Potential
                                </div>
                            </div>
                            
                            <p class="small text-muted mb-3">
                                <strong>Implementation:</strong> 
                                Implement automated box sizing and package weight optimization.
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <div class="h2 text-info">30%</div>
                                <small class="text-muted">Oversized Packages</small>
                            </div>
                            <div class="mb-3">
                                <div class="h4 text-info">1 month</div>
                                <small class="text-muted">Implementation Time</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-info btn-sm btn-block">
                                    <i class="fas fa-cogs"></i> Configure System
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fas fa-calculator"></i> ROI Calculator
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volume Discounts -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-handshake mr-2"></i>Volume Discount Negotiation
                    </h6>
                    <div class="badge badge-success">High Impact</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-success">₹{{ number_format($recommendations['volume_discounts']['potential_savings']) }}</h5>
                            <p class="text-muted small">Monthly savings potential</p>
                            
                            <p class="mb-3">{{ $recommendations['volume_discounts']['details'] }}</p>
                            
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 95%">
                                    95% Qualification Rate
                                </div>
                            </div>
                            
                            <p class="small text-muted mb-3">
                                <strong>Implementation:</strong> 
                                Leverage current shipping volume for better carrier rates and terms.
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <div class="h2 text-success">5-8%</div>
                                <small class="text-muted">Additional Discount</small>
                            </div>
                            <div class="mb-3">
                                <div class="h4 text-info">6 weeks</div>
                                <small class="text-muted">Negotiation Time</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-success btn-sm btn-block">
                                    <i class="fas fa-phone"></i> Contact Carriers
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fas fa-file-contract"></i> Contract Review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Implementation Timeline -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-check mr-2"></i>Implementation Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Week 1-2: Carrier Efficiency</h6>
                                <p class="timeline-text">Implement carrier reallocation based on performance analysis. Expected savings: ₹15,000/month</p>
                                <div class="timeline-actions">
                                    <button class="btn btn-primary btn-sm">Start Implementation</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Week 3-5: Zone Optimization</h6>
                                <p class="timeline-text">Review and adjust zone-based pricing. Expected savings: ₹8,000/month</p>
                                <div class="timeline-actions">
                                    <button class="btn btn-warning btn-sm">Schedule Review</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Week 4-8: Packaging System</h6>
                                <p class="timeline-text">Implement automated right-sizing system. Expected savings: ₹12,000/month</p>
                                <div class="timeline-actions">
                                    <button class="btn btn-info btn-sm">Configure System</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Week 6-12: Volume Negotiations</h6>
                                <p class="timeline-text">Negotiate better rates with carriers. Expected savings: ₹25,000/month</p>
                                <div class="timeline-actions">
                                    <button class="btn btn-success btn-sm">Initiate Negotiations</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Priority Actions Required
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle mr-2"></i>Quick Wins Available
                        </h5>
                        <p class="mb-3">The following actions can be implemented immediately for quick cost savings:</p>
                        <ul class="mb-3">
                            <li>Reallocate 20% of volume to most efficient carrier (2-week implementation)</li>
                            <li>Enable automated packaging optimization for new orders (1-week setup)</li>
                            <li>Review and update zone pricing for underperforming regions (3-day analysis)</li>
                        </ul>
                        <hr>
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-0">
                                    <strong>Combined Quick Win Potential:</strong> ₹27,000/month savings with minimal effort.
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <button class="btn btn-success">
                                    <i class="fas fa-rocket"></i> Implement Quick Wins
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e3e6f0;
}

.timeline-content {
    background: #f8f9fc;
    padding: 20px;
    border-radius: 8px;
    border-left: 3px solid #4e73df;
}

.timeline-title {
    color: #5a5c69;
    margin-bottom: 10px;
    font-weight: 600;
}

.timeline-text {
    color: #6e707e;
    margin-bottom: 15px;
}

.timeline-actions {
    margin-top: 10px;
}
</style>
@endpush
@endsection