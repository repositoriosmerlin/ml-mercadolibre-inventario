<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

// cambio --- 10/09/2026: modelo de rol para el módulo Usuarios.
class Rol extends Model
{
    /**
     * El plural de 'Rol' que infiere Eloquent sería 'rols'.
     */
    protected $table = 'roles';

    protected $fillable = ['nombre', 'slug', 'descripcion'];

    /**
     * Slug del rol con acceso total. Vive aquí y no suelto en el código
     * porque el Gate lo consulta en cada petición.
     */
    public const ADMINISTRADOR = 'administrador';

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permiso_rol', 'rol_id', 'permiso_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rol_usuario', 'rol_id', 'user_id');
    }

    /**
     * El slug se deriva del nombre: quien crea un rol desde el panel no
     * tiene por qué saber que existe.
     */
    protected static function booted(): void
    {
        static::saving(function (Rol $rol) {
            if (blank($rol->slug)) {
                $rol->slug = Str::slug($rol->nombre);
            }
        });
    }

    public function esAdministrador(): bool
    {
        return $this->slug === self::ADMINISTRADOR;
    }
}
