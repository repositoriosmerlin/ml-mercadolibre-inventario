<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// cambio --- 10/09/2026: modelo del catálogo de permisos.
class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = ['clave', 'modulo', 'origen', 'descripcion'];

    /** Declarado por una ruta con el middleware 'can'. */
    public const ORIGEN_RUTA = 'ruta';

    /** Dado de alta a mano desde el panel. */
    public const ORIGEN_MANUAL = 'manual';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'permiso_rol', 'permiso_id', 'rol_id');
    }

    public function esDeRuta(): bool
    {
        return $this->origen === self::ORIGEN_RUTA;
    }
}
