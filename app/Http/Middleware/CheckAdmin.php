<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->usu_nivel < 900) {
            return redirect('/mis-tareas')->with('error', 'No tienes permisos para acceder.');
        }

        return $next($request);
    }
}
