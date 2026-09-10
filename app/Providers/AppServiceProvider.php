<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // URLs de recurso en español: /usuarios/crear, /roles/1/editar
        Route::resourceVerbs([
            'create' => 'crear',
            'edit' => 'editar',
        ]);

        // cambio --- 10/09/2026: resuelve cualquier permiso contra los roles
        // del usuario. Va en Gate::before y no en un Gate::define por permiso
        // porque definirlos exigiría consultar la tabla en cada arranque —
        // incluso al correr migrate, cuando esa tabla todavía no existe.
        //
        // Devolver null (y no false) es deliberado: deja que las policies
        // decidan cuando el permiso no está en el catálogo.
        Gate::before(function (User $usuario, string $permiso) {
            if ($usuario->esAdministrador()) {
                return true;
            }

            return $usuario->tienePermiso($permiso) ? true : null;
        });
    }
}
