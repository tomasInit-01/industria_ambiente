<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckAuth
{
    public function handle($request, Closure $next)
    {
        // Guarda la URL original antes de redirigir
        if (!Auth::check()) {
            Log::info('No hay usuario autenticado');
            session(['url.intended' => $request->fullUrl()]);
            return redirect('/login')->with('error', 'Debes iniciar sesión.');
        }

        return $next($request);
    }
}
