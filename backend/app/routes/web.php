<?php

use Illuminate\Support\Facades\Route;

$frontendViews = [
    '/' => 'pages.index',
    '/login' => 'pages.login',
    '/register' => 'pages.register',
    '/privacy' => 'pages.privacy',
    '/terms' => 'pages.terms',
];

foreach ($frontendViews as $route => $view) {
    Route::view($route, $view);
}

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

Route::view('/marketplace', 'pages.marketplace', ['section' => 'index']);

Route::get('/marketplace/{id}', function ($id) {
    return view('pages.marketplace', ['section' => 'show', 'id' => $id]);
});
