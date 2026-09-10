<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// cambio --- 10/09/2026: expulsa a quien fue deshabilitado con la sesión ya
// abierta.
//
// Sin esto, deshabilitar solo impediría el próximo login: la persona que está
// trabajando en ese momento seguiría dentro hasta que su sesión caducara, que
// es justo el caso en el que uno deshabilita a alguien con urgencia.
class VerificarUsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->estaActivo()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Esta cuenta está deshabilitada. Habla con un administrador.']);
        }

        return $next($request);
    }
}
