<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // cambio --- 10/09/2026: fecha en que se deshabilitó la cuenta.
            'deshabilitado_at' => 'datetime',
        ];
    }

    // cambio --- 10/09/2026: estado de la cuenta. Las cuentas no se eliminan,
    // se deshabilitan, y una deshabilitada no puede iniciar sesión.

    public function estaActivo(): bool
    {
        return $this->deshabilitado_at === null;
    }

    public function scopeActivos(Builder $consulta): Builder
    {
        return $consulta->whereNull('deshabilitado_at');
    }

    // cambio --- 10/09/2026: relación con roles y consulta de permisos, que es
    // lo que el Gate registrado en AppServiceProvider usa en cada petición.

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'user_id', 'rol_id');
    }

    /**
     * Los permisos del usuario son la unión de los de todos sus roles.
     * loadMissing evita repetir la consulta: el Gate pregunta muchas veces
     * por pantalla (menú, botones, ruta) y no vale una consulta por pregunta.
     */
    public function permisos(): Collection
    {
        $this->loadMissing('roles.permisos');

        return $this->roles
            ->flatMap(fn (Rol $rol) => $rol->permisos->pluck('clave'))
            ->unique()
            ->values();
    }

    public function tienePermiso(string $clave): bool
    {
        return $this->permisos()->contains($clave);
    }

    /**
     * Acceso total. Se resuelve por slug para que renombrar el rol desde el
     * panel no deje a nadie fuera.
     */
    public function esAdministrador(): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains(fn (Rol $rol) => $rol->esAdministrador());
    }
}
