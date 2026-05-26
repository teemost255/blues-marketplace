<?php
use Illuminate\Support\Facades\Route;

// Admin imports
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\Admin\BankTransferController as AdminBankTransferController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ListingsController;
use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\ModeratorsController;
use App\Http\Controllers\Admin\TransactionsController;
use App\Http\Controllers\Admin\TicketsController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\VirtualNumberOrdersController;
use App\Http\Controllers\Admin\AnnouncementsController;
use App\Http\Controllers\Admin\ReferralLeaderboardController;
use App\Http\Controllers\Admin\ReviewsController as AdminReviewsController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\BankTransferController as UserBankTransferController;

// Public imports
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ReferralController;

// User dashboard imports
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\OrdersController;
use App\Http\Controllers\User\WishlistController;
use App\Http\Controllers\User\NotificationsController;
use App\Http\Controllers\User\SupportController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\VirtualNumberController;
use App\Http\Controllers\User\ReferralPageController;

// ── Public ────────────────────────────────────────────────────────────────────
// Paystack webhook (no CSRF)
Route::post('/paystack/webhook', [WalletController::class, 'webhook'])->name('paystack.webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/',          [HomeController::class,        'index'])->name('home');
Route::get('/r/{code}',  [ReferralController::class,    'handle'])->name('referral');
Route::get('/terms',     [PagesController::class,       'terms'])->name('terms');
Route::get('/privacy',   [PagesController::class,       'privacy'])->name('privacy');


// ── User Auth ─────────────────────────────────────────────────────────────────
Route::get('/login',    [LoginController::class,    'show'])->name('login');
Route::post('/login',   [LoginController::class,    'login'])->name('login.post');
Route::post('/logout',  [LoginController::class,    'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register',[RegisterController::class, 'register'])->name('register.post');

// Forgot / Reset Password
Route::get('/forgot-password',  [ForgotPasswordController::class, 'showForgotForm'])->name('forgot-password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendReset'])->name('forgot-password.send');
Route::get('/reset-password',   [ForgotPasswordController::class, 'showResetForm'])->name('reset-password');
Route::post('/reset-password',  [ForgotPasswordController::class, 'resetPassword'])->name('reset-password.update');

// ── Email Verification ────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard.index');
        }
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard.index')->with('success', 'Email verified! Welcome to BluesMarketplace.');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard.index');
        }
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent! Please check your email.');
    })->middleware('throttle:6,1')->name('verification.send');
});

// ── User Dashboard ────────────────────────────────────────────────────────────
Route::middleware(\App\Http\Middleware\UserAuth::class)->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/',             [DashboardController::class,    'index'])->name('index');
    Route::get('/wallet',             [WalletController::class, 'index'])->name('wallet');
    Route::post('/wallet/initiate',   [WalletController::class, 'initiate'])->name('wallet.initiate');
    Route::get('/wallet/callback',    [WalletController::class, 'callback'])->name('wallet.callback');
    Route::get('/orders',       [OrdersController::class,       'index'])->name('orders');
    Route::get('/wishlist',     [WishlistController::class,     'index'])->name('wishlist');
    Route::post('/wishlist',    [WishlistController::class,     'store'])->name('wishlist.store');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('/notifications',[NotificationsController::class,'index'])->name('notifications');
    Route::post('/notifications/mark-all-read',[NotificationsController::class,'markAllRead'])->name('notifications.mark-all-read');
    Route::get('/support',      [SupportController::class,      'index'])->name('support');
    Route::post('/support',     [SupportController::class,      'store'])->name('support.store');
    Route::get('/profile',      [ProfileController::class,      'index'])->name('profile');
    Route::post('/profile',     [ProfileController::class,      'update'])->name('profile.update');
    Route::post('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
    Route::post('/orders/{purchase}/review', [ReviewController::class, 'store'])->name('orders.review');

    // Bank Transfer
    Route::post('/marketplace/{id}/bank-transfer',   [UserBankTransferController::class, 'marketplace'])->name('marketplace.bank-transfer');
    Route::post('/wallet/bank-transfer',             [UserBankTransferController::class, 'walletTopup'])->name('wallet.bank-transfer');
    Route::get('/bank-transfer/{id}/pending',        [UserBankTransferController::class, 'pending'])->name('bank-transfer.pending');
    Route::post('/bank-transfer/{id}/paid',          [UserBankTransferController::class, 'markPaid'])->name('bank-transfer.paid');
    Route::get('/bank-transfer/{id}/status',         [UserBankTransferController::class, 'status'])->name('bank-transfer.status');
    Route::get('/bank-transfer/{id}/success',        [UserBankTransferController::class, 'success'])->name('bank-transfer.success');

    Route::get('/referrals',        [ReferralPageController::class,  'index'])->name('referrals');

    // Marketplace (dashboard-only)
    Route::get('/marketplace',           [MarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/marketplace/{id}',      [MarketplaceController::class, 'show'])->name('marketplace.show');
    Route::post('/marketplace/{id}/buy', [MarketplaceController::class, 'buy'])->name('marketplace.buy');

    Route::get('/virtual-numbers',                  [VirtualNumberController::class, 'index'])->name('virtual-numbers');
    Route::get('/virtual-numbers/api/countries',    [VirtualNumberController::class, 'getCountries'])->name('virtual-numbers.countries');
    Route::get('/virtual-numbers/api/services',     [VirtualNumberController::class, 'getServices'])->name('virtual-numbers.services');
    Route::post('/virtual-numbers/order',           [VirtualNumberController::class, 'order'])->name('virtual-numbers.order');
    Route::get('/virtual-numbers/{id}/sms',         [VirtualNumberController::class, 'checkSms'])->name('virtual-numbers.sms');
    Route::delete('/virtual-numbers/{id}/cancel',   [VirtualNumberController::class, 'cancel'])->name('virtual-numbers.cancel');
});

// ── Admin Auth ────────────────────────────────────────────────────────────────
Route::get('/adminlogin',      [AdminLoginController::class,    'show'])->name('admin.login');
Route::post('/adminlogin',     [AdminLoginController::class,    'login'])->name('admin.login.post');
Route::post('/admin/logout',   [AdminLoginController::class,    'logout'])->name('admin.logout');
Route::get('/admin/register',  [AdminRegisterController::class, 'show'])->name('admin.register');
Route::post('/admin/register', [AdminRegisterController::class, 'register'])->name('admin.register.post');

// ── Admin Panel ───────────────────────────────────────────────────────────────
Route::middleware(\App\Http\Middleware\AdminAuth::class)->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',              [AdminDashboardController::class,'index'])->name('dashboard');

    // Users
    Route::get('/users',                        [UsersController::class, 'index'])->name('users');
    Route::post('/users',                       [UsersController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/status',         [UsersController::class, 'updateStatus'])->name('users.status');
    Route::post('/users/{user}/password',       [UsersController::class, 'changePassword'])->name('users.password');
    Route::post('/users/{user}/wallet',         [UsersController::class, 'walletAdjust'])->name('users.wallet');
    Route::get('/users/{user}/dashboard',       [UsersController::class, 'impersonateDashboard'])->name('impersonate.dashboard');
    Route::delete('/users/{user}',              [UsersController::class, 'destroy'])->name('users.destroy');

    // Listings
    Route::get('/listings',                     [ListingsController::class, 'index'])->name('listings');
    Route::post('/listings',                    [ListingsController::class, 'store'])->name('listings.store');
    Route::get('/listings/{listing}/edit',      [ListingsController::class, 'edit'])->name('listings.edit');
    Route::post('/listings/{listing}',          [ListingsController::class, 'update'])->name('listings.update');
    Route::delete('/listings/{listing}',        [ListingsController::class, 'destroy'])->name('listings.destroy');

    // Categories
    Route::get('/categories',                   [CategoriesController::class, 'index'])->name('categories');
    Route::post('/categories',                  [CategoriesController::class, 'store'])->name('categories.store');
    Route::post('/categories/{category}',       [CategoriesController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}',     [CategoriesController::class, 'destroy'])->name('categories.destroy');

    // Moderators
    Route::get('/moderators',                   [ModeratorsController::class, 'index'])->name('moderators');
    Route::post('/moderators',                  [ModeratorsController::class, 'store'])->name('moderators.store');
    Route::post('/moderators/{admin}/role',     [ModeratorsController::class, 'assignRole'])->name('moderators.role');
    Route::delete('/moderators/{admin}',        [ModeratorsController::class, 'destroy'])->name('moderators.destroy');

    // Transactions, Tickets, Audit, Settings
    Route::get('/transactions',                 [TransactionsController::class, 'index'])->name('transactions');
    Route::get('/tickets',                      [TicketsController::class,      'index'])->name('tickets');
    Route::post('/tickets/{ticket}/reply',      [TicketsController::class,      'reply'])->name('tickets.reply');
    Route::get('/audit',                        [AuditController::class,        'index'])->name('audit');
    Route::get('/settings',                     [SettingsController::class,          'index'])->name('settings');
    Route::post('/settings',                    [SettingsController::class,          'update'])->name('settings.update');
    Route::post('/settings/test-email',         [SettingsController::class,          'sendTestEmail'])->name('settings.test-email');

    Route::get('/virtual-numbers',                              [VirtualNumberOrdersController::class, 'index'])->name('virtual-numbers');
    Route::get('/virtual-numbers/grizzlysms-balance',          [VirtualNumberOrdersController::class, 'grizzlySmsBalance'])->name('virtual-numbers.grizzlysms-balance');
    Route::get('/virtual-numbers/export',                      [VirtualNumberOrdersController::class, 'exportCsv'])->name('virtual-numbers.export');
    Route::post('/virtual-numbers/{order}/status',             [VirtualNumberOrdersController::class, 'updateStatus'])->name('virtual-numbers.status');
    Route::delete('/virtual-numbers/{order}',                  [VirtualNumberOrdersController::class, 'destroy'])->name('virtual-numbers.destroy');

    Route::get('/announcements',  [AnnouncementsController::class, 'index'])->name('announcements');
    Route::post('/announcements', [AnnouncementsController::class, 'store'])->name('announcements.store');

    Route::get('/referrals',      [ReferralLeaderboardController::class, 'index'])->name('referrals');
    Route::get('/reviews',        [AdminReviewsController::class,        'index'])->name('reviews');
    Route::delete('/reviews/{review}', [AdminReviewsController::class,   'destroy'])->name('reviews.destroy');

    // Bank Transfers
    Route::get('/bank-transfers',                [AdminBankTransferController::class, 'index'])->name('bank-transfers');
    Route::post('/bank-transfers/{id}/confirm',  [AdminBankTransferController::class, 'confirm'])->name('bank-transfers.confirm');
    Route::post('/bank-transfers/{id}/reject',   [AdminBankTransferController::class, 'reject'])->name('bank-transfers.reject');

    // Admin Profile
    Route::get('/profile',           [AdminProfileController::class, 'index'])->name('profile');
    Route::post('/profile',          [AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/api/pending-count', [AdminProfileController::class, 'pendingCount'])->name('api.pending-count');
});
