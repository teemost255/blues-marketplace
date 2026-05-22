<?php

use Illuminate\Support\Facades\Route;

$frontendRoutes = [
    '/',
    '/login',
    '/register',
    '/privacy',
    '/terms',
    '/adminlogin',
    '/dashboard',
    '/dashboard/notifications',
    '/dashboard/orders',
    '/dashboard/profile',
    '/dashboard/support',
    '/dashboard/wallet',
    '/dashboard/wishlist',
    '/admin',
    '/admin/audit',
    '/admin/listings',
    '/admin/settings',
    '/admin/tickets',
    '/admin/transactions',
    '/admin/users',
    '/marketplace',
    '/marketplace/{id}',
];

foreach ($frontendRoutes as $route) {
    Route::view($route, 'welcome');
} '/login',
    '/register',
    '/privacy',
    '/terms',
    '/adminlogin',
    '/dashboard',
    '/dashboard/notifications',
    '/dashboard/orders',
    '/dashboard/profile',
    '/dashboard/support',
    '/dashboard/wallet',
    '/dashboard/wishlist',
    '/admin',
    '/admin/audit',
    '/admin/listings',
    '/admin/settings',
    '/admin/tickets',
    '/admin/transactions',
    '/admin/users',
    '/marketplace',
    '/marketplace/{id}',
];

foreach ($frontendRoutes as $route) {
    Route::view($route, 'welcome');
}

