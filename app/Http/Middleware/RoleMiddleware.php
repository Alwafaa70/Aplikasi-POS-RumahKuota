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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!in_array(auth()->user()->role, $roles)) {
            // Jika role tidak sesuai, lemparkan ke dashboard sesuai role mereka masing-masing
            $role = auth()->user()->role;
            if ($role === 'owner') return redirect()->route('owner.dashboard');
            if ($role === 'admin_gudang') return redirect()->route('admin.dashboard');
            if ($role === 'kasir') return redirect()->route('kasir.dashboard');
            
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
