<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\GuardarPermisoRequest;
use App\Models\Permiso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

// cambio --- 10/09/2026: la pantalla de permisos lista el catálogo y permite
// dar de alta uno nuevo. No edita ni elimina: cambiar la clave de un permiso
// ya asignado lo desconectaría en silencio del middleware que lo exige.
class PermisoController extends Controller
{
    public function index(): View
    {
        return view('permisos.index', [
            'permisos' => Permiso::query()
                ->withCount('roles')
                ->orderBy('modulo')
                ->orderBy('clave')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('permisos.create');
    }

    public function store(GuardarPermisoRequest $request): RedirectResponse
    {
        $clave = $request->validated('clave');

        $permiso = Permiso::create([
            'clave' => $clave,
            // El módulo se deriva de la clave: pedirlo aparte solo abre la
            // puerta a que no coincidan.
            'modulo' => Str::before($clave, '.'),
            'origen' => Permiso::ORIGEN_MANUAL,
            'descripcion' => $request->validated('descripcion'),
        ]);

        return redirect()
            ->route('permisos.index')
            ->with('estado', 'Permiso "'.$permiso->clave.'" creado.');
    }
}
