<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckSetupMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $hasUsers = User::count() > 0;

        if (!$hasUsers) {
            if (!$request->routeIs('setup.*')) {
                return redirect()->route('setup.index');
            }
        } else {
            if ($request->routeIs('setup.*')) {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
