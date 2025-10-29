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
    
    // User Management (for role assignments)
    Route::get('/users', [AdminSupportController::class, 'users'])->name('users');
    Route::post('/users/{user}/role', [AdminSupportController::class, 'updateUserRole'])->name('users.role');
    
});