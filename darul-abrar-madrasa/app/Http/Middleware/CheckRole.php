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
     * During migration to Spatie permissions we support both:
     * - Spatie role checks (preferred)
     * - Legacy string-based role column (fallback)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // Preferred: Spatie roles
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Legacy fallback used - user needs Spatie role sync
        // Run: php artisan sync:spatie-roles --repair --role={user_role}
        if (in_array($user->role, $roles, true)) {
            Log::warning('CheckRole using legacy string-based role fallback', [
                'user_id' => $user->id,
                'role' => $user->role,
                'expected_roles' => $roles,
                'has_spatie_roles' => $user->roles->isNotEmpty(),
                'spatie_roles' => $user->roles->pluck('name')->toArray(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'action' => 'User needs Spatie role synchronization',
            ]);
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
