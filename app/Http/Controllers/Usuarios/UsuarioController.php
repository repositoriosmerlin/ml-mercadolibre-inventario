<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\GuardarUsuarioRequest;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

// cambio --- 10/09/2026: pasa de placeholder a alta, edición y asignación de
// roles. Las cuentas no se eliminan: se deshabilitan.
class UsuarioController extends Controller
{
    public function index(): View
    {
        return view('usuarios.index', [
            'usuarios' => User::query()
                ->with('roles:id,nombre')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('usuarios.create', [
            'usuario' => new User,
            'roles' => $this->roles(),
            'seleccionados' => collect(old('roles', [])),
        ]);
    }

    public function store(GuardarUsuarioRequest $request): RedirectResponse
    {
        $usuario = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $usuario->roles()->sync($request->input('roles', []));

        return redirect()
            ->route('usuarios.index')
            ->with('estado', 'Usuario "'.$usuario->name.'" creado.');
    }

    public function show(User $usuario): RedirectResponse
    {
        // La ficha del usuario es su formulario: no hay nada más que ver.
        return redirect()->route('usuarios.edit', $usuario);
    }

    public function edit(User $usuario): View
    {
        return view('usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $this->roles(),
            'seleccionados' => collect(old('roles', $usuario->roles->pluck('id')->all())),
        ]);
    }

    public function update(GuardarUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $usuario->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        // Vacío significa "no la cambies", no "bórrala".
        if (filled($request->validated('password'))) {
            $usuario->password = $request->validated('password');
        }

        $usuario->save();

        $roles = $request->input('roles', []);

        if ($error = $this->motivoParaNoAplicarRoles($usuario, $roles)) {
            return redirect()
                ->route('usuarios.edit', $usuario)
                ->with('error', $error);
        }

        $usuario->roles()->sync($roles);

        return redirect()
            ->route('usuarios.index')
            ->with('estado', 'Usuario "'.$usuario->name.'" actualizado.');
    }

    /**
     * Asigna el conjunto de roles de un usuario sin tocar sus datos.
     */
    public function sincronizarRoles(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $roles = $datos['roles'] ?? [];

        if ($error = $this->motivoParaNoAplicarRoles($usuario, $roles)) {
            return redirect()
                ->route('usuarios.edit', $usuario)
                ->with('error', $error);
        }

        $usuario->roles()->sync($roles);

        return redirect()
            ->route('usuarios.edit', $usuario)
            ->with('estado', 'Roles actualizados.');
    }

    /**
     * Deshabilita o vuelve a habilitar una cuenta.
     */
    public function cambiarEstado(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->estaActivo()) {
            if ($error = $this->motivoParaNoDeshabilitar($request, $usuario)) {
                return redirect()
                    ->route('usuarios.index')
                    ->with('error', $error);
            }

            $usuario->deshabilitado_at = now();
            $usuario->save();

            return redirect()
                ->route('usuarios.index')
                ->with('estado', $usuario->name.' quedó deshabilitado.');
        }

        $usuario->deshabilitado_at = null;
        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with('estado', $usuario->name.' quedó habilitado.');
    }

    /**
     * @return Collection<int, Rol>
     */
    protected function roles(): Collection
    {
        return Rol::query()->orderBy('nombre')->get();
    }

    /**
     * Devuelve el motivo por el que no se puede deshabilitar, o null.
     */
    protected function motivoParaNoDeshabilitar(Request $request, User $usuario): ?string
    {
        if ($usuario->is($request->user())) {
            return 'No puedes deshabilitar tu propia cuenta.';
        }

        if ($usuario->esAdministrador() && $this->administradoresActivos($usuario) === 0) {
            return 'Es el único administrador activo. Habilita o nombra otro antes de deshabilitarlo.';
        }

        return null;
    }

    /**
     * Devuelve el motivo por el que no se pueden aplicar esos roles, o null.
     *
     * @param  array<int, int|string>  $roles
     */
    protected function motivoParaNoAplicarRoles(User $usuario, array $roles): ?string
    {
        if (! $usuario->esAdministrador()) {
            return null;
        }

        $conservaAdministracion = Rol::query()
            ->whereIn('id', $roles)
            ->where('slug', Rol::ADMINISTRADOR)
            ->exists();

        if ($conservaAdministracion) {
            return null;
        }

        // Se le está quitando la administración: solo se permite si queda
        // otro administrador activo que pueda seguir repartiendo permisos.
        return $this->administradoresActivos($usuario) > 0
            ? null
            : 'Es el único administrador activo. Nombra otro antes de quitarle el rol.';
    }

    /**
     * Administradores activos, sin contar al usuario que se está tocando.
     */
    protected function administradoresActivos(User $excepto): int
    {
        return User::query()
            ->activos()
            ->whereKeyNot($excepto->getKey())
            ->whereHas('roles', fn ($consulta) => $consulta->where('slug', Rol::ADMINISTRADOR))
            ->count();
    }
}
