<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminOrRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            // Log::info('No hay usuario autenticado');
            return redirect()->route('login');
        }

        $isAdmin = $user->usu_nivel >= 900;

        // Lista de roles autorizados (puedes agregar más si lo deseas)
        $allowedRoles = ['coordinador_lab', 'coordinador_muestreo', 'facturador'];
        $hasRequiredRole = in_array($user->rol, $allowedRoles);

        if (! $isAdmin && ! $hasRequiredRole) {
            Log::info('No tiene permisos para acceder a esta página');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
