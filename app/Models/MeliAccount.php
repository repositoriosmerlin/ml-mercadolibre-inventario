<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// cambio --- 10/09/2026: cuenta de Mercado Libre conectada. Concentra aquí el estado de
// la conexión para que ningún job tenga que deducirlo leyendo fechas sueltas.
#[Fillable([
    'seller_id',
    'nickname',
    'access_token',
    'refresh_token',
    'expires_at',
    'scopes',
    'estado',
    'ultimo_error',
    'ultima_sincronizacion_at',
    'conectado_por',
])]
#[Hidden(['access_token', 'refresh_token'])]
class MeliAccount extends Model
{
    /** Conexión sana: se puede operar. */
    public const ESTADO_ACTIVA = 'active';

    /** MELI dejó de aceptar las credenciales y hace falta que un humano re-autorice. */
    public const ESTADO_REQUIERE_REAUTORIZACION = 'needs_reauth';

    /** El vendedor retiró el permiso desde su cuenta. */
    public const ESTADO_REVOCADA = 'revoked';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Los tokens son la credencial más sensible de la integración: quien los
            // tenga puede modificar el catálogo de la tienda. Nunca en claro.
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'datetime',
            'ultima_sincronizacion_at' => 'datetime',
        ];
    }

    /**
     * Usuario de Merlin que autorizó la conexión.
     */
    public function conectadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conectado_por');
    }

    /**
     * ¿Hay que refrescar el token?
     *
     * El margen por defecto son 10 minutos: se refresca antes de que expire, no cuando
     * MELI ya respondió 401. Evita reintentos y latencia en mitad de una sincronización.
     */
    public function necesitaRefresco(int $margenSegundos = 600): bool
    {
        return $this->expires_at->subSeconds($margenSegundos)->isPast();
    }

    /**
     * ¿La conexión sirve para operar?
     */
    public function estaActiva(): bool
    {
        return $this->estado === self::ESTADO_ACTIVA;
    }

    /**
     * Marca la cuenta como caída y deja el motivo a la vista del operador.
     *
     * Se llama cuando un 401 sobrevive a un refresco. A partir de aquí los jobs de esta
     * cuenta deben detenerse: reintentar solo quema cuota y llena los logs.
     */
    public function marcarParaReautorizar(string $motivo): void
    {
        $this->forceFill([
            'estado' => self::ESTADO_REQUIERE_REAUTORIZACION,
            'ultimo_error' => $motivo,
        ])->save();
    }
}
