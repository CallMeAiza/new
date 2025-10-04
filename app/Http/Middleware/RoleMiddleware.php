<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        \Log::info('RoleMiddleware: Checking access', [
            'url' => $request->url(),
            'required_role' => $role,
            'user_authenticated' => $request->user() ? 'YES' : 'NO'
        ]);

        if (!$request->user()) {
            \Log::warning('RoleMiddleware: No authenticated user');
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            return redirect()->route('login');
        }

        $user = $request->user();
        
        \Log::info('RoleMiddleware: User details', [
            'user_email' => $user->user_email,
            'user_role' => $user->user_role,
            'required_role' => $role
        ]);
        
        // Allow admin users to access any role-protected area
        if ($user->user_role === 'admin') {
            \Log::info('RoleMiddleware: Admin user - allowing access');
            return $next($request);
        }
        
        // Check if user has the required role
        if ($user->user_role !== $role) {
            \Log::warning('RoleMiddleware: Role mismatch - redirecting', [
                'user_role' => $user->user_role,
                'required_role' => $role,
                'dashboard_route' => $user->getDashboardRoute()
            ]);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            return redirect()->route($user->getDashboardRoute())
                ->with('error', 'You do not have permission to access this area.');
        }

        \Log::info('RoleMiddleware: Access granted');
        return $next($request);
    }
}
