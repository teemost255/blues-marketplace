<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ListingsController;
use App\Http\Controllers\Admin\TransactionsController;
use App\Http\Controllers\Admin\TicketsController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\SettingsController;

Route::get('/', fn() => redirect()->route('admin.login'));

// Admin auth
Route::get('/adminlogin',    [AdminLoginController::class,    'show'])->name('admin.login');
Route::post('/adminlogin',   [AdminLoginController::class,    'login'])->name('admin.login.post');
Route::get('/adminregister', [AdminRegisterController::class, 'show'])->name('admin.register');
Route::post('/adminregister',[AdminRegisterController::class, 'register'])->name('admin.register.post');
Route::post('/admin/logout', [AdminLoginController::class,    'logout'])->name('admin.logout');

// Protected admin routes
Route::middleware(\App\Http\Middleware\AdminAuth::class)->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',             [DashboardController::class,    'index'])->name('dashboard');
    Route::get('/users',        [UsersController::class,        'index'])->name('users');
    Route::delete('/users/{user}', [UsersController::class,     'destroy'])->name('users.destroy');
    Route::get('/listings',     [ListingsController::class,     'index'])->name('listings');
    Route::post('/listings',    [ListingsController::class,     'store'])->name('listings.store');
    Route::delete('/listings/{listing}', [ListingsController::class, 'destroy'])->name('listings.destroy');
    Route::get('/transactions', [TransactionsController::class, 'index'])->name('transactions');
    Route::get('/tickets',      [TicketsController::class,      'index'])->name('tickets');
    Route::post('/tickets/{ticket}/reply', [TicketsController::class, 'reply'])->name('tickets.reply');
    Route::get('/audit',        [AuditController::class,        'index'])->name('audit');
    Route::get('/settings',     [SettingsController::class,     'index'])->name('settings');
});
