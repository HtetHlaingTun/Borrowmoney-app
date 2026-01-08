<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockLocalhostLoginMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (
            Auth::check() &&
            ($request->getHost() === '127.0.0.1' || $request->getHost() === 'localhost') &&
            $request->getPathInfo() === '/'
        ) {

            // Redirect to dashboard based on user role
            if (Auth::user()->role == 'admin') {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('user.homePage');
            }
        }

        return $next($request);
    }
}
