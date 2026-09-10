<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\GuardarRolRequest;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

// cambio --- 10/09/2026: pasa de placeholder a CRUD real sobre la tabla roles,
// con asignación de permisos desde el mismo formulario.
class RolController extends Controller
{
    public function index(): View
    {
        return view('roles.index', [
            'roles' => Rol::query()
                ->withCount(['permisos', 'usuarios'])
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('roles.create', [
            'rol' => new Rol,
            'permisosPorModulo' => $this->permisosPorModulo(),
            'seleccionados' => collect(old('permisos', [])),
        ]);
    }

    public function store(GuardarRolRequest $request): RedirectResponse
    {
        $rol = Rol::create($request->safe()->only(['nombre', 'descripcion']));
        $rol->permisos()->sync($request->input('permisos', []));

        return redirect()
            ->route('roles.index')
            ->with('estado', 'Rol "'.$rol->nombre.'" creado.');
    }

    public function show(Rol $rol): RedirectResponse
    {
        // El detalle de un rol es su formulario: no hay nada que ver aparte.
        return redirect()->route('roles.edit', $rol);
    }

    public function edit(Rol $rol): View
    {
        return view('roles.edit', [
            'rol' => $rol,
            'permisosPorModulo' => $this->permisosPorModulo(),
            'seleccionados' => collect(old('permisos', $rol->permisos->pluck('id')->all())),
        ]);
    }

    public function update(GuardarRolRequest $request, Rol $rol): RedirectResponse
    {
        $rol->update($request->safe()->only(['nombre', 'descripcion']));
        $rol->permisos()->sync($request->input('permisos', []));

        return redirect()
            ->route('roles.index')
            ->with('estado', 'Rol "'.$rol->nombre.'" actualizado.');
    }

    public function destroy(Rol $rol): RedirectResponse
    {
        // Borrar el rol de administración dejaría el sistema sin nadie que
        // pueda repartir permisos, y no hay forma de recuperarlo desde la UI.
        if ($rol->esAdministrador()) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'El rol de administración no se puede eliminar.');
        }

        $nombre = $rol->nombre;
        $rol->delete();

        return redirect()
            ->route('roles.index')
            ->with('estado', 'Rol "'.$nombre.'" eliminado.');
    }

    /**
     * Asigna el conjunto de permisos de un rol desde su pantalla de edición.
     */
    public function sincronizarPermisos(Request $request, Rol $rol): RedirectResponse
    {
        $permisos = $request->validate([
            'permisos' => ['array'],
            'permisos.*' => ['integer', 'exists:permisos,id'],
        ]);

        $rol->permisos()->sync($permisos['permisos'] ?? []);

        return redirect()
            ->route('roles.edit', $rol)
            ->with('estado', 'Permisos actualizados.');
    }

    /**
     * El catálogo agrupado por módulo, que es como se pinta el formulario.
     *
     * @return Collection<string, Collection<int, Permiso>>
     */
    protected function permisosPorModulo(): Collection
    {
        return Permiso::query()
            ->orderBy('modulo')
            ->orderBy('clave')
            ->get()
            ->groupBy('modulo');
    }
}
