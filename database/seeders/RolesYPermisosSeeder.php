<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use App\Support\CatalogoDePermisos;
use Illuminate\Database\Seeder;

// cambio --- 10/09/2026: siembra el catálogo de permisos desde las rutas y
// deja un rol de administración con todo asignado.
//
// Sin esto el sistema queda cerrado sobre sí mismo: las rutas exigen permisos,
// nadie los tiene todavía, y la pantalla para repartirlos también exige uno.
class RolesYPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = new CatalogoDePermisos;
        $resultado = $catalogo->sincronizar();

        $this->command?->info('Permisos nuevos: '.$resultado['creados']->count());

        if ($resultado['obsoletos']->isNotEmpty()) {
            $this->command?->warn(
                'Permisos en la tabla que ya no declara ninguna ruta: '
                .$resultado['obsoletos']->join(', ')
            );
        }

        $rol = Rol::firstOrCreate(
            ['slug' => Rol::ADMINISTRADOR],
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso total al sistema.',
            ]
        );

        $rol->permisos()->sync(Permiso::query()->pluck('id'));

        // El rol se le da al primer usuario, que es la cuenta con la que se
        // entra por primera vez. Si ya hay varios, no adivinamos a cuál.
        $usuario = User::query()->oldest('id')->first();

        if ($usuario && $usuario->roles()->where('roles.id', $rol->id)->doesntExist()) {
            $usuario->roles()->attach($rol);
            $this->command?->info('Rol Administrador asignado a '.$usuario->email);
        }
    }
}
