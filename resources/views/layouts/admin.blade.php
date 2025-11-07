<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Admin Styles -->
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-size: 0.9rem;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #5a67d8 10%, #667eea 100%);
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            height: var(--header-height);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .sidebar-brand:hover {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link i {
            min-width: 2rem;
            text-align: center;
            font-size: 0.85rem;
        }
        
        .nav-link-text {
            margin-left: 0.5rem;
        }
        
        .badge-counter {
            position: absolute;
            top: 8px;
            right: 12px;
            font-size: 0.65rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .topbar {
            height: var(--header-height);
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .content-wrapper {
            padding: 1.5rem;
        }
        
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: 1px solid #e3e6f0;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .border-left-danger {
            border-left: 0.25rem solid #e74a3b !important;
        }
        
        .text-xs {
            font-size: 0.7rem;
        }
        
        .font-weight-bold {
            font-weight: 700 !important;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
        
        .shadow {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
        }
        
        .sidebar-toggled .sidebar {
            width: 6.5rem;
        }
        
        .sidebar-toggled .main-content {
            margin-left: 6.5rem;
        }
        
        .sidebar-toggled .sidebar .nav-link-text {
            display: none;
        }
        
        .sidebar-toggled .sidebar-brand {
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-toggled .sidebar {
                width: var(--sidebar-width);
            }
            
            .sidebar-toggled .main-content {
                margin-left: 0;
            }
        }
        
        .dropdown-toggle::after {
            margin-left: auto;
        }
        
        .dropdown-menu {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: 1px solid #e3e6f0;
        }
        
        .table {
            color: #858796;
        }
        
        .table-bordered {
            border: 1px solid #e3e6f0;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #e3e6f0;
        }
        
        .btn {
            font-size: 0.875rem;
            border-radius: 0.35rem;
        }
        
        .btn-sm {
            font-size: 0.8125rem;
        }
        
        .breadcrumb {
            background-color: transparent;
            margin-bottom: 0;
        }
        
        .page-header {
            background: white;
            padding: 1rem 1.5rem;
            margin: -1.5rem -1.5rem 1.5rem -1.5rem;
            border-bottom: 1px solid #e3e6f0;
        }
    </style>
    
    @stack('styles')
</head>
<body id="page-top" class="{{ request()->cookie('sidebar-toggled') ? 'sidebar-toggled' : '' }}">
    
    <!-- Sidebar -->
    <ul class="sidebar" id="sidebar">
        <!-- Sidebar Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="sidebar-brand-text mx-3">Admin Panel</div>
        </a>
        
        <!-- Nav Items -->
        <div class="sidebar-nav">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                   href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider my-0" style="border-color: rgba(255, 255, 255, 0.15);">
            
            <!-- Orders -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" 
                   href="{{ route('admin.orders.index') }}">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span class="nav-link-text">Orders</span>
                    @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                        <span class="badge bg-danger badge-counter">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>
            </li>
            
            <!-- COD Orders -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.orders.cod.*') ? 'active' : '' }}" 
                   href="{{ route('admin.orders.cod.pending') }}">
                    <i class="fas fa-fw fa-money-bill-wave"></i>
                    <span class="nav-link-text">COD Orders</span>
                    @if(isset($pendingCodCount) && $pendingCodCount > 0)
                        <span class="badge bg-warning badge-counter">{{ $pendingCodCount }}</span>
                    @endif
                </a>
            </li>
            
            <!-- Payments -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" 
                   href="{{ route('admin.payments.index') }}">
                    <i class="fas fa-fw fa-credit-card"></i>
                    <span class="nav-link-text">Payments</span>
                </a>
            </li>
            
            <!-- Shipments -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.shipments.*') ? 'active' : '' }}" 
                   href="{{ route('admin.shipments.index') }}">
                    <i class="fas fa-fw fa-shipping-fast"></i>
                    <span class="nav-link-text">Shipments</span>
                </a>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider" style="border-color: rgba(255, 255, 255, 0.15);">
            
            <!-- Products -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" 
                   href="{{ route('admin.products.index') }}">
                    <i class="fas fa-fw fa-box"></i>
                    <span class="nav-link-text">Products</span>
                </a>
            </li>
            
            <!-- Categories -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" 
                   href="{{ route('admin.categories.index') }}">
                    <i class="fas fa-fw fa-tags"></i>
                    <span class="nav-link-text">Categories</span>
                </a>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider" style="border-color: rgba(255, 255, 255, 0.15);">
            
            <!-- Support -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}" 
                   href="{{ route('admin.support.dashboard') }}">
                    <i class="fas fa-fw fa-headset"></i>
                    <span class="nav-link-text">Support</span>
                </a>
            </li>
            
            <!-- Analytics -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}" 
                   href="{{ route('admin.orders.analytics') }}">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span class="nav-link-text">Analytics</span>
                </a>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block" style="border-color: rgba(255, 255, 255, 0.15);">
        </div>
    </ul>
    
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="main-content">
        
        <!-- Main Content -->
        <div id="content">
            
            <!-- Topbar -->
            <nav class="navbar navbar-expand topbar static-top">
                
                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
                    <i class="fa fa-bars"></i>
                </button>
                
                <!-- Topbar Navbar -->
                <ul class="navbar-nav ms-auto">
                    
                    <!-- Nav Item - Alerts Dropdown (Example) -->
                    <li class="nav-item dropdown no-arrow mx-1">
                        <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-fw"></i>
                            <!-- Counter - Alerts -->
                            <span class="badge badge-danger badge-counter">3+</span>
                        </a>
                        <!-- Dropdown - Alerts -->
                        <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in"
                             aria-labelledby="alertsDropdown">
                            <h6 class="dropdown-header">
                                Alerts Center
                            </h6>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="me-3">
                                    <div class="icon-circle bg-primary">
                                        <i class="fas fa-file-alt text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">December 12, 2019</div>
                                    <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                </div>
                            </a>
                            <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                        </div>
                    </li>
                    
                    <div class="topbar-divider d-none d-sm-block"></div>
                    
                    <!-- Nav Item - User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="me-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name ?? 'Admin' }}</span>
                            <i class="fas fa-user-circle fa-fw"></i>
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                             aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                                Profile
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i>
                                Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                    
                </ul>
                
            </nav>
            <!-- End of Topbar -->
            
            <!-- Begin Page Content -->
            <div class="content-wrapper">
                
                <!-- Page Heading -->
                @if(View::hasSection('page-header'))
                    <div class="page-header">
                        @yield('page-header')
                    </div>
                @endif
                
                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <!-- Content -->
                @yield('content')
                
            </div>
            <!-- /.container-fluid -->
            
        </div>
        <!-- End of Main Content -->
        
    </div>
    <!-- End of Content Wrapper -->
    
    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-primary" type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Admin JS -->
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggleTop');
            const body = document.body;
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    body.classList.toggle('sidebar-toggled');
                    
                    // Save state to cookie
                    if (body.classList.contains('sidebar-toggled')) {
                        document.cookie = 'sidebar-toggled=true; path=/; max-age=31536000';
                    } else {
                        document.cookie = 'sidebar-toggled=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                    }
                });
            }
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bootstrapAlert = new bootstrap.Alert(alert);
                    bootstrapAlert.close();
                }, 5000);
            });
        });
        
        // Confirm delete actions
        function confirmDelete(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        }
        
        // Select all checkbox functionality
        function toggleSelectAll(source) {
            const checkboxes = document.getElementsByName('selected_items[]');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
            updateBulkActionsVisibility();
        }
        
        // Update bulk actions visibility based on selected items
        function updateBulkActionsVisibility() {
            const checkboxes = document.querySelectorAll('input[name="selected_items[]"]:checked');
            const bulkActions = document.querySelector('.bulk-actions');
            
            if (bulkActions) {
                if (checkboxes.length > 0) {
                    bulkActions.style.display = 'block';
                    bulkActions.querySelector('.selected-count').textContent = checkboxes.length;
                } else {
                    bulkActions.style.display = 'none';
                }
            }
        }
        
        // Initialize bulk actions visibility
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', updateBulkActionsVisibility);
            });
        });
    </script>
    
    @stack('scripts')

    <!-- Toast Notifications Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <!-- Toasts will be dynamically added here -->
    </div>

    <!-- Global Toast JavaScript -->
    <script>
        // Global toast function for better notifications
        window.showToast = function(message, type = 'success', duration = 4000) {
            const toastContainer = document.querySelector('.toast-container');
            const toastId = 'toast-' + Date.now();
            
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 fade" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { delay: duration });
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
            
            toast.show();
        };

        // Auto-show flash message toasts
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        
        @if(session('error'))
            showToast('{{ session('error') }}', 'danger');
        @endif
        
        @if(session('warning'))
            showToast('{{ session('warning') }}', 'warning');
        @endif
        
        @if(session('info'))
            showToast('{{ session('info') }}', 'info');
        @endif
    </script>
</body>
</html>