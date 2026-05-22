<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);

Route::view('/login', 'pages.login');
Route::view('/register', 'pages.register');
Route::view('/privacy', 'pages.privacy');
Route::view('/terms', 'pages.terms');

Route::view('/adminlogin', 'pages.login');

Route::view('/dashboard', 'pages.dashboard', ['section' => 'overview']);
Route::view('/dashboard/notifications', 'pages.dashboard', ['section' => 'notifications']);
Route::view('/dashboard/orders', 'pages.dashboard', ['section' => 'orders']);
Route::view('/dashboard/profile', 'pages.dashboard', ['section' => 'profile']);
Route::view('/dashboard/support', 'pages.dashboard', ['section' => 'support']);
Route::view('/dashboard/wallet', 'pages.dashboard', ['section' => 'wallet']);
Route::view('/dashboard/wishlist', 'pages.dashboard', ['section' => 'wishlist']);

Route::view('/admin', 'pages.admin', ['section' => 'overview']);
Route::view('/admin/audit', 'pages.admin', ['section' => 'audit']);
Route::view('/admin/listings', 'pages.admin', ['section' => 'listings']);
Route::view('/admin/settings', 'pages.admin', ['section' => 'settings']);
Route::view('/admin/tickets', 'pages.admin', ['section' => 'tickets']);
Route::view('/admin/transactions', 'pages.admin', ['section' => 'transactions']);
Route::view('/admin/users', 'pages.admin', ['section' => 'users']);

Route::get('/marketplace', [HomeController::class, 'marketplaceIndex']);
Route::get('/marketplace/{id}', [HomeController::class, 'marketplaceShow']);
