<?php

namespace App\Support;

use App\Models\Permiso;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// cambio --- 10/09/2026: siembra inicial del catálogo leyendo el middleware
// 'can' de las rutas registradas.
//
// Se usa SOLO desde el seeder, para que una instalación nueva arranque con
// los permisos que el código ya exige sin teclear la lista a mano — si no,
// el sistema queda cerrado sobre sí mismo: las rutas piden permisos, nadie
// los tiene, y la pantalla para crearlos también pide uno.
//
// El día a día es manual: los permisos se agregan desde /permisos/crear.
// Por eso los sembrados aquí quedan marcados con origen 'ruta' y los del
// panel con 'manual'.
class CatalogoDePermisos
{
    /**
     * Verbos conocidos, para que la descripción del permiso sea legible sin
     * tener que teclearla en cada ruta.
     */
    protected const VERBOS = [
        'ver' => 'Ver',
        'crear' => 'Crear',
        'editar' => 'Editar',
        'eliminar' => 'Eliminar',
        'asignar' => 'Asignar',
        'sincronizar' => 'Sincronizar',
        'conectar' => 'Conectar',
    ];

    /**
     * Permisos declarados hoy en las rutas, sin tocar la base de datos.
     *
     * @return Collection<int, array{clave: string, modulo: string, descripcion: string}>
     */
    public function declarados(): Collection
    {
        return collect(Route::getRoutes()->getRoutes())
            ->flatMap(fn ($ruta) => $this->clavesDe($ruta->gatherMiddleware()))
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $clave) => [
                'clave' => $clave,
                'modulo' => $this->moduloDe($clave),
                'origen' => Permiso::ORIGEN_RUTA,
                'descripcion' => $this->descripcionDe($clave),
            ]);
    }

    /**
     * Deja la tabla 'permisos' igual a lo declarado en las rutas.
     *
     * No borra los permisos que ya no aparecen: eliminarlos los quitaría en
     * cascada de todos los roles, y una ruta puede desaparecer por un
     * refactor a medio camino. Se informan como obsoletos y se decide a mano.
     *
     * @return array{creados: Collection, obsoletos: Collection}
     */
    public function sincronizar(): array
    {
        $declarados = $this->declarados();
        $existentes = Permiso::query()->pluck('clave');

        $creados = $declarados
            ->reject(fn (array $permiso) => $existentes->contains($permiso['clave']))
            ->each(fn (array $permiso) => Permiso::create($permiso))
            ->pluck('clave');

        // La auditoría solo mira los de origen 'ruta'. Un permiso manual no
        // aparece en ninguna ruta por definición, y reportarlo como obsoleto
        // sería ruido en cada siembra.
        $obsoletos = Permiso::query()
            ->where('origen', Permiso::ORIGEN_RUTA)
            ->pluck('clave')
            ->diff($declarados->pluck('clave'))
            ->values();

        return [
            'creados' => $creados->values(),
            'obsoletos' => $obsoletos,
        ];
    }

    /**
     * Extrae las claves del middleware 'can:permiso' de una ruta.
     *
     * El middleware admite 'can:habilidad,modelo'; solo interesa el primer
     * argumento, y se descartan las habilidades con modelo porque esas las
     * resuelve una policy, no el catálogo.
     *
     * @param  array<int, mixed>  $middleware
     * @return array<int, string>
     */
    protected function clavesDe(array $middleware): array
    {
        return collect($middleware)
            ->filter(fn ($capa) => is_string($capa) && Str::startsWith($capa, 'can:'))
            ->map(fn (string $capa) => Str::after($capa, 'can:'))
            ->reject(fn (string $argumentos) => Str::contains($argumentos, ','))
            ->values()
            ->all();
    }

    protected function moduloDe(string $clave): string
    {
        return Str::before($clave, '.');
    }

    protected function descripcionDe(string $clave): string
    {
        $accion = Str::after($clave, '.');
        $modulo = $this->moduloDe($clave);

        $verbo = self::VERBOS[$accion] ?? Str::ucfirst(str_replace('.', ' ', $accion));

        return $verbo.' '.$modulo;
    }
}
