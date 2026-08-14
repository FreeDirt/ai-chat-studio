<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Super Admin access required.'], 403);
            }
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        return $next($request);
    }
}
