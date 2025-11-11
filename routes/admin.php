<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\PaymentController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group and admin authentication.
|
*/

// ================================================================================================
// 💳 ADMIN PAYMENT MANAGEMENT SYSTEM
// ================================================================================================
Route::prefix('admin/payments')->name('admin.payments.')->middleware(['auth'])->group(function () {
    
    // Payment Analytics Dashboard
    Route::get('/dashboard', [PaymentController::class, 'dashboard'])->name('dashboard');
    
    // Payment Management
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
    
    // Export & Analytics
    Route::get('/export/csv', [PaymentController::class, 'export'])->name('export');
    Route::get('/analytics/api', [PaymentController::class, 'analyticsApi'])->name('analytics.api');
});

// ================================================================================================
// 🛠️ ADMIN SUPPORT SYSTEM (Staff/Agent Management)
// ================================================================================================
Route::prefix('admin/support')->name('admin.support.')->middleware(['auth'])->group(function () {
    
    // Admin Support Dashboard
    Route::get('/', [AdminSupportController::class, 'dashboard'])->name('dashboard');
    
    // Ticket Management
    Route::get('/tickets', [AdminSupportController::class, 'tickets'])->name('tickets.index');
    Route::get('/tickets/create', [AdminSupportController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [AdminSupportController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [AdminSupportController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [AdminSupportController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [AdminSupportController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [AdminSupportController::class, 'destroy'])->name('tickets.destroy');
    
    // Ticket Actions
    Route::post('/tickets/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('tickets.reply');
    Route::put('/tickets/{ticket}/assign', [AdminSupportController::class, 'assign'])->name('tickets.assign');
    Route::put('/tickets/{ticket}/status', [AdminSupportController::class, 'updateStatus'])->name('tickets.update-status');
    Route::put('/tickets/{ticket}/priority', [AdminSupportController::class, 'updatePriority'])->name('tickets.update-priority');
    Route::post('/tickets/{ticket}/close', [AdminSupportController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/reopen', [AdminSupportController::class, 'reopen'])->name('tickets.reopen');
    Route::post('/tickets/{ticket}/internal-note', [AdminSupportController::class, 'addInternalNote'])->name('tickets.internal-note');
    
    // Bulk Actions
    Route::post('/tickets/bulk-assign', [AdminSupportController::class, 'bulkAssign'])->name('tickets.bulk-assign');
    Route::post('/tickets/bulk-close', [AdminSupportController::class, 'bulkClose'])->name('tickets.bulk-close');
    Route::post('/tickets/bulk-priority', [AdminSupportController::class, 'bulkPriority'])->name('tickets.bulk-priority');
    
    // Agent Management
    Route::get('/agents', [AdminSupportController::class, 'agents'])->name('agents.index');
    Route::get('/agents/{agent}/tickets', [AdminSupportController::class, 'agentTickets'])->name('agents.tickets');
    
    // Live Chat Management
    Route::get('/chats', [AdminSupportController::class, 'chats'])->name('chats');
    Route::get('/chats/{chat}', [AdminSupportController::class, 'showChat'])->name('chats.show');
    Route::post('/chats/{chat}/join', [AdminSupportController::class, 'joinChat'])->name('chats.join');
    Route::post('/chats/{chat}/message', [AdminSupportController::class, 'sendChatMessage'])->name('chats.message');
    Route::post('/chats/{chat}/end', [AdminSupportController::class, 'endChat'])->name('chats.end');
    
    // Analytics & Reports
    Route::get('/analytics', [AdminSupportController::class, 'analytics'])->name('analytics');
    Route::get('/reports', [AdminSupportController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [AdminSupportController::class, 'exportReport'])->name('reports.export');
    
    // Settings
    Route::get('/settings', [AdminSupportController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminSupportController::class, 'updateSettings'])->name('settings.update');
});

    // ================================================================================================
    // 🔐 ADMIN AUTHENTICATION & PERMISSIONS
    // ================================================================================================
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        
        // Admin Dashboard
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        
        // ================================================================================================
        // 📦 ORDER MANAGEMENT SYSTEM
        // ================================================================================================
        Route::prefix('orders')->name('orders.')->group(function () {
            // Order Dashboard & Analytics
            Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminOrderController::class, 'dashboard'])->name('dashboard');
            Route::get('/analytics', [\App\Http\Controllers\Admin\AdminOrderController::class, 'analytics'])->name('analytics');
            
            // Main Order Management
            Route::get('/', [\App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [\App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('show');
            Route::post('/export', [\App\Http\Controllers\Admin\AdminOrderController::class, 'export'])->name('export');
            
            // Order Status Management
            Route::post('/{order}/update-status', [\App\Http\Controllers\CheckoutController::class, 'updateOrderStatus'])->name('update-status');
            Route::post('/{order}/cancel', [\App\Http\Controllers\Admin\AdminOrderController::class, 'cancel'])->name('cancel');
            
            // COD Order Management
            Route::prefix('cod')->name('cod.')->group(function () {
                Route::get('/pending', [\App\Http\Controllers\Admin\AdminOrderController::class, 'pendingCod'])->name('pending');
                Route::post('/{order}/confirm', [\App\Http\Controllers\Admin\AdminOrderController::class, 'confirmCod'])->name('confirm');
                Route::post('/bulk-confirm', [\App\Http\Controllers\Admin\AdminOrderController::class, 'bulkConfirmCod'])->name('bulk-confirm');
            });
            
            // Bulk Actions
            Route::post('/bulk-update-status', [\App\Http\Controllers\Admin\AdminOrderController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
            Route::post('/bulk-cancel', [\App\Http\Controllers\Admin\AdminOrderController::class, 'bulkCancel'])->name('bulk-cancel');
        });
        
        // ================================================================================================
        // 🔄 RETURN MANAGEMENT SYSTEM
        // ================================================================================================
        Route::prefix('returns')->name('returns.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReturnManagementController::class, 'index'])->name('index');
            Route::get('/{order}', [\App\Http\Controllers\ReturnManagementController::class, 'show'])->name('show');
            Route::post('/{order}/update-status', [\App\Http\Controllers\ReturnManagementController::class, 'updateStatus'])->name('update-status');
        });

        // User Management (for role assignments)
        Route::get('/users', [AdminSupportController::class, 'users'])->name('users');
        Route::post('/users/{user}/role', [AdminSupportController::class, 'updateUserRole'])->name('users.role');
    
    // ================================================================================================
    // 📦 PRODUCT MANAGEMENT WITH IMAGE OPTIMIZATION
    // ================================================================================================
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\ProductController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('destroy');
        
        // Image upload AJAX endpoints
        Route::post('/upload-image-preview', [\App\Http\Controllers\ProductController::class, 'uploadImagePreview'])->name('upload.preview');
    });
    
    // ================================================================================================
    // 🏷️ CATEGORY MANAGEMENT WITH IMAGE OPTIMIZATION
    // ================================================================================================
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CategoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\CategoryController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [\App\Http\Controllers\CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('destroy');
        
        // AJAX endpoints
        Route::post('/upload-image-preview', [\App\Http\Controllers\CategoryController::class, 'uploadImagePreview'])->name('upload.preview');
        Route::post('/reorder', [\App\Http\Controllers\CategoryController::class, 'reorder'])->name('reorder');
    });
    
    // ================================================================================================
    // 🚚 SHIPPING MANAGEMENT SYSTEM
    // ================================================================================================
    
    // Shipments Management (Direct routes under admin namespace)
    Route::resource('shipments', \App\Http\Controllers\Admin\ShipmentController::class);
    Route::post('shipments/{shipment}/update-status', [\App\Http\Controllers\Admin\ShipmentController::class, 'updateStatus'])
        ->name('shipments.update-status');
    Route::post('shipments/{shipment}/generate-label', [\App\Http\Controllers\Admin\ShipmentController::class, 'generateLabel'])
        ->name('shipments.generate-label');
    Route::get('shipments/{shipment}/tracking', [\App\Http\Controllers\Admin\ShipmentController::class, 'tracking'])
        ->name('shipments.tracking');
    
    // Orders ready for shipment
    Route::get('shipments/ready-orders', [\App\Http\Controllers\Admin\ShipmentController::class, 'readyOrders'])
        ->name('shipments.ready-orders');
    
    // Bulk Actions
    Route::post('shipments/bulk-update-status', [\App\Http\Controllers\Admin\ShipmentController::class, 'bulkUpdateStatus'])
        ->name('shipments.bulk-update-status');
    Route::post('shipments/bulk-generate-labels', [\App\Http\Controllers\Admin\ShipmentController::class, 'bulkGenerateLabels'])
        ->name('shipments.bulk-generate-labels');
    
    // Shipping Analytics (Enhanced shipping system)
    Route::prefix('shipping')->name('shipping.')->group(function () {
        Route::get('analytics', [\App\Http\Controllers\Admin\ShippingAnalyticsController::class, 'index'])
            ->name('analytics.index');
        Route::get('analytics/carrier-performance', [\App\Http\Controllers\Admin\ShippingAnalyticsController::class, 'carrierPerformance'])
            ->name('analytics.carrier-performance');
        Route::get('analytics/cost-optimization', [\App\Http\Controllers\Admin\ShippingAnalyticsController::class, 'costOptimization'])
            ->name('analytics.cost-optimization');
        Route::get('analytics/shipment-analysis', [\App\Http\Controllers\Admin\ShippingAnalyticsController::class, 'shipmentAnalysis'])
            ->name('analytics.shipment-analysis');
        
        // Shipping Carriers Management (future feature)
        // Route::resource('carriers', \App\Http\Controllers\Admin\ShippingCarrierController::class);
        // Route::resource('methods', \App\Http\Controllers\Admin\ShippingMethodController::class);
    });
    
});