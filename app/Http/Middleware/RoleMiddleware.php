<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // If logged in but role not allowed, redirect to login or show 403
        if (!in_array($user->acct_roles, $roles)) {
            return redirect()->route('login')->with('error', 'You are not authorized to access this page.');
            // OR use: abort(403, 'Unauthorized access');
        }
        return $next($request);
    }
}
