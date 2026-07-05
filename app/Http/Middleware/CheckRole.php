<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Check if the authenticated user has any of the specified roles.
     *
     * Usage in routes: middleware('check.role:admin,owner,manager')
     * The user must have at least ONE of the listed roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Super-admin bypasses all role checks
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have the required role to access this resource.',
                'required_roles' => $roles,
            ], 403);
        }

        return $next($request);
    }
}
