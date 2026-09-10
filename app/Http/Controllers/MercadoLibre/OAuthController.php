<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Models\MeliAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// cambio --- 10/09/2026: flujo OAuth con Mercado Libre. Son dos pasos porque el token
// nunca puede viajar por el navegador: MELI devuelve un code de un solo uso y el canje
// por el token se hace servidor a servidor, con el secret.
class OAuthController extends Controller
{
    /** Clave de sesión donde vive el state mientras el vendedor autoriza en MELI. */
    private const CLAVE_STATE = 'meli.oauth_state';

    /**
     * Manda al vendedor a la pantalla de autorización de Mercado Libre.
     */
    public function conectar(Request $request): RedirectResponse
    {
        if (! $this->credencialesConfiguradas()) {
            return redirect()
                ->route('usuarios.index')
                ->with('meli_error', 'Faltan MELI_APP_ID o MELI_SECRET_KEY en el .env.');
        }

        // El state protege contra CSRF: al volver comprobamos que la respuesta
        // corresponde a una autorización que iniciamos nosotros y no a un enlace que
        // alguien le pasó al usuario.
        $state = Str::random(40);
        $request->session()->put(self::CLAVE_STATE, $state);

        $url = config('services.mercadolibre.auth_url').'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.mercadolibre.app_id'),
            'redirect_uri' => config('services.mercadolibre.redirect_uri'),
            'state' => $state,
        ]);

        return redirect()->away($url);
    }

    /**
     * Recibe el code de Mercado Libre y lo canjea por los tokens.
     */
    public function callback(Request $request): RedirectResponse
    {
        // El vendedor pudo apretar "Cancelar" en la pantalla de MELI.
        if ($request->filled('error')) {
            return $this->volverConError(
                'Mercado Libre rechazó la autorización: '.$request->string('error_description')->value()
            );
        }

        $stateEsperado = $request->session()->pull(self::CLAVE_STATE);

        if (! $stateEsperado || ! hash_equals($stateEsperado, (string) $request->query('state'))) {
            return $this->volverConError('La autorización no coincide con la que se inició desde Merlin.');
        }

        if (! $request->filled('code')) {
            return $this->volverConError('Mercado Libre no devolvió el código de autorización.');
        }

        $respuesta = Http::asForm()
            ->acceptJson()
            ->post(config('services.mercadolibre.api_url').'/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.mercadolibre.app_id'),
                'client_secret' => config('services.mercadolibre.secret_key'),
                'code' => $request->query('code'),
                // Tiene que ser idéntico al enviado al autorizar, o MELI responde
                // invalid_grant sin más explicación.
                'redirect_uri' => config('services.mercadolibre.redirect_uri'),
            ]);

        if ($respuesta->failed()) {
            // El cuerpo del error trae el motivo (invalid_grant, invalid_client...) pero
            // nunca el token, así que es seguro registrarlo completo.
            Log::error('MELI: falló el canje del code', [
                'status' => $respuesta->status(),
                'body' => $respuesta->json(),
            ]);

            return $this->volverConError('Mercado Libre no aceptó el código de autorización. Revisa el log.');
        }

        $cuenta = $this->guardarCuenta($respuesta->json());

        Log::info('MELI: cuenta conectada', ['seller_id' => $cuenta->seller_id]);

        return redirect()
            ->route('usuarios.index')
            ->with('meli_exito', 'Cuenta de Mercado Libre conectada ('.$cuenta->seller_id.').');
    }

    /**
     * Crea o actualiza la cuenta con los tokens recién emitidos.
     *
     * El refresh_token es de un solo uso: cada refresco devuelve uno nuevo y el anterior
     * queda muerto. Por eso ambos tokens se escriben siempre juntos, en la misma
     * operación — guardar uno y no el otro deja la integración irrecuperable.
     */
    private function guardarCuenta(array $datos): MeliAccount
    {
        return MeliAccount::updateOrCreate(
            ['seller_id' => $datos['user_id']],
            [
                'access_token' => $datos['access_token'],
                'refresh_token' => $datos['refresh_token'],
                'expires_at' => now()->addSeconds($datos['expires_in']),
                'scopes' => $datos['scope'] ?? null,
                'estado' => MeliAccount::ESTADO_ACTIVA,
                'ultimo_error' => null,
                'conectado_por' => Auth::id(),
            ]
        );
    }

    private function credencialesConfiguradas(): bool
    {
        return filled(config('services.mercadolibre.app_id'))
            && filled(config('services.mercadolibre.secret_key'))
            && filled(config('services.mercadolibre.redirect_uri'));
    }

    private function volverConError(string $mensaje): RedirectResponse
    {
        Log::warning('MELI: autorización fallida', ['motivo' => $mensaje]);

        return redirect()->route('usuarios.index')->with('meli_error', $mensaje);
    }
}
