<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Formulario de acceso.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Valida las credenciales y abre la sesión.
     */
    public function store(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->asegurarQueNoEstaBloqueado($request);

        if (! Auth::attempt($credenciales, $request->boolean('recordarme'))) {
            RateLimiter::hit($this->claveDeIntentos($request));

            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        // cambio --- 10/09/2026: una cuenta deshabilitada no entra. Se valida
        // después del attempt y no dentro de las credenciales para poder decir
        // por qué se rechaza: "usuario o contraseña incorrectos" mandaría a la
        // persona a probar contraseñas que sí eran correctas.
        if (! Auth::user()->estaActivo()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Esta cuenta está deshabilitada. Habla con un administrador.',
            ]);
        }

        RateLimiter::clear($this->claveDeIntentos($request));
        $request->session()->regenerate();

        return redirect()->intended(route('usuarios.index'));
    }

    /**
     * Cierra la sesión activa.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Cinco intentos fallidos por correo + IP antes de bloquear.
     */
    protected function asegurarQueNoEstaBloqueado(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->claveDeIntentos($request), 5)) {
            return;
        }

        $segundos = RateLimiter::availableIn($this->claveDeIntentos($request));

        throw ValidationException::withMessages([
            'email' => 'Demasiados intentos. Vuelve a probar en '.$segundos.' segundos.',
        ]);
    }

    protected function claveDeIntentos(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }
}
