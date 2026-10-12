<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: middleware('role:staff') or middleware('role:supervisor')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!in_array($user->role, $roles)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk melakukan aksi ini.'
                ], 403);
            }

            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        return $next($request);
    }
}
