<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        return $next($request);
    }
}
