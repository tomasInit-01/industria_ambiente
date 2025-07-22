<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class EnsureSessionActive
{
    public function handle($request, Closure $next)
    {
        $sessionCookie = config('session.cookie');
        $hasSessionCookie = $request->hasCookie($sessionCookie);
        $hasRememberMeCookie = collect($request->cookies->keys())
            ->contains(fn($key) => str_starts_with($key, 'remember_web_'));

        if (!$hasSessionCookie && $hasRememberMeCookie) {
            logger('Regenerando sesión porque faltaba la cookie de sesión');
            Session::start();
        }

        return $next($request);
    }
}
