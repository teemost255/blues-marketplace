<?php
use Illuminate\Support\Facades\Route;

// Admin imports
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ListingsController;
use App\Http\Controllers\Admin\TransactionsController;
use App\Http\Controllers\Admin\TicketsController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\SettingsController;

// Public imports
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\PagesController;

// User dashboard imports
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\OrdersController;
use App\Http\Controllers\User\WishlistController;
use App\Http\Controllers\User\NotificationsController;
use App\Http\Controllers\User\SupportController;
use App\Http\Controllers\User\ProfileController;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/',          [HomeController::class,        'index'])->name('home');
Route::get('/terms',     [PagesController::class,       'terms'])->name('terms');
Route::get('/privacy',   [PagesController::class,       'privacy'])->name('privacy');

// Marketplace
Route::get('/marketplace',          [MarketplaceController::class, 'index'])->name('marketplace');
Route::get('/marketplace/{id}',     [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::post('/marketplace/{id}/buy',[MarketplaceController::class, 'buy'])->name('marketplace.buy')->middleware(\App\Http\Middleware\UserAuth::class);

// ── User Auth ─────────────────────────────────────────────────────────────────
Route::get('/login',    [LoginController::class,    'show'])->name('login');
Route::post('/login',   [LoginController::class,    'login'])->name('login.post');
Route::post('/logout',  [LoginController::class,    'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register',[RegisterController::class, 'register'])->name('register.post');

// ── User Dashboard ────────────────────────────────────────────────────────────
Route::middleware(\App\Http\Middleware\UserAuth::class)->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/',             [DashboardController::class,    'index'])->name('index');
    Route::get('/wallet',       [WalletController::class,       'index'])->name('wallet');
    Route::post('/wallet',      [WalletController::class,       'deposit'])->name('wallet.deposit');
    Route::get('/orders',       [OrdersController::class,       'index'])->name('orders');
    Route::get('/wishlist',     [WishlistController::class,     'index'])->name('wishlist');
    Route::post('/wishlist',    [WishlistController::class,     'store'])->name('wishlist.store');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('/notifications',[NotificationsController::class,'index'])->name('notifications');
    Route::get('/support',      [SupportController::class,      'index'])->name('support');
    Route::post('/support',     [SupportController::class,      'store'])->name('support.store');
    Route::get('/profile',      [ProfileController::class,      'index'])->name('profile');
    Route::post('/profile',     [ProfileController::class,      'update'])->name('profile.update');
});

// ── Admin Auth ────────────────────────────────────────────────────────────────
Route::get('/adminlogin',    [AdminLoginController::class,    'show'])->name('admin.login');
Route::post('/adminlogin',   [AdminLoginController::class,    'login'])->name('admin.login.post');
Route::get('/adminregister', [AdminRegisterController::class, 'show'])->name('admin.register');
Route::post('/adminregister',[AdminRegisterController::class, 'register'])->name('admin.register.post');
Route::post('/admin/logout', [AdminLoginController::class,    'logout'])->name('admin.logout');

// ── Admin Panel ───────────────────────────────────────────────────────────────
Route::middleware(\App\Http\Middleware\AdminAuth::class)->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',              [AdminDashboardController::class,'index'])->name('dashboard');
    Route::get('/users',         [UsersController::class,        'index'])->name('users');
    Route::delete('/users/{user}',[UsersController::class,       'destroy'])->name('users.destroy');
    Route::get('/listings',      [ListingsController::class,     'index'])->name('listings');
    Route::post('/listings',     [ListingsController::class,     'store'])->name('listings.store');
    Route::delete('/listings/{listing}',[ListingsController::class,'destroy'])->name('listings.destroy');
    Route::get('/transactions',  [TransactionsController::class, 'index'])->name('transactions');
    Route::get('/tickets',       [TicketsController::class,      'index'])->name('tickets');
    Route::post('/tickets/{ticket}/reply',[TicketsController::class,'reply'])->name('tickets.reply');
    Route::get('/audit',         [AuditController::class,        'index'])->name('audit');
    Route::get('/settings',      [SettingsController::class,     'index'])->name('settings');
});
