<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Comment 1: Use hasAnyEffectiveRole for dual-check behavior during migration.
     * This ensures both Spatie roles and legacy role column are checked.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // Comment 1: Use hasAnyEffectiveRole which checks both Spatie and legacy
        if (method_exists($user, 'hasAnyEffectiveRole') && $user->hasAnyEffectiveRole($roles)) {
            return $next($request);
        }

        // Fallback for safety (should not reach here if User model is correct)
        if (in_array($user->role, $roles, true)) {
            Log::warning('CheckRole fallback to direct role column check', [
                'user_id' => $user->id,
                'role' => $user->role,
                'expected_roles' => $roles,
                'url' => $request->url(),
                'action' => 'hasAnyEffectiveRole method may be missing',
            ]);
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
