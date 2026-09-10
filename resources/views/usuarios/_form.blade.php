{{-- cambio --- 10/09/2026: formulario compartido por crear y editar un
     usuario. Vista operativa: el amarillo marca la acción a tomar ahora. --}}

<form method="POST" action="{{ $accion }}">
    @csrf
    @isset($metodo)
        @method($metodo)
    @endisset

    <div class="aa-card" style="max-width:560px; margin-bottom:24px;">
        <div class="section-title">Cuenta</div>

        <div class="aa-field">
            <label class="aa-label" for="name">Nombre</label>
            <input id="name"
                   class="aa-input @error('name') bad @enderror"
                   type="text"
                   name="name"
                   value="{{ old('name', $usuario->name) }}"
                   maxlength="120"
                   required
                   autofocus>
            @error('name')
                <div class="aa-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="aa-field">
            <label class="aa-label" for="email">Correo</label>
            <input id="email"
                   class="aa-input mono @error('email') bad @enderror"
                   type="email"
                   name="email"
                   value="{{ old('email', $usuario->email) }}"
                   maxlength="180"
                   required
                   autocomplete="off">
            @error('email')
                <div class="aa-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="aa-field">
            <label class="aa-label" for="password">Contraseña</label>
            <input id="password"
                   class="aa-input @error('password') bad @enderror"
                   type="password"
                   name="password"
                   autocomplete="new-password"
                   @unless($usuario->exists) required @endunless>
            @error('password')
                <div class="aa-error">{{ $message }}</div>
            @enderror
            @if ($usuario->exists)
                <div class="aa-hint">Déjala vacía para conservar la actual.</div>
            @else
                <div class="aa-hint">Mínimo 8 caracteres. No hay correo de bienvenida: entrégasela tú.</div>
            @endif
        </div>

        <div class="aa-field" style="margin-bottom:0;">
            <label class="aa-label" for="password_confirmation">Repetir contraseña</label>
            <input id="password_confirmation"
                   class="aa-input"
                   type="password"
                   name="password_confirmation"
                   autocomplete="new-password"
                   @unless($usuario->exists) required @endunless>
        </div>
    </div>

    <div class="section-title">Roles</div>

    @if ($roles->isEmpty())
        <div class="aa-empty" style="max-width:560px; margin-bottom:24px;">
            <span class="dot"></span>
            <span class="dot" style="background:var(--yellow);"></span>
            <span class="dot" style="background:var(--red);"></span>
            <div class="title">No hay roles definidos</div>
            <div class="sub">Crea un rol antes de asignarlo a una cuenta.</div>
            <div style="margin-top:24px;">
                <a href="{{ route('roles.create') }}" class="aa-btn">Crear rol</a>
            </div>
        </div>
    @else
        @error('roles')
            <div class="aa-error" style="margin-bottom:12px;">{{ $message }}</div>
        @enderror
        @error('roles.*')
            <div class="aa-error" style="margin-bottom:12px;">{{ $message }}</div>
        @enderror

        <div class="aa-card" style="max-width:560px; margin-bottom:24px;">
            @foreach ($roles as $rol)
                <label class="aa-permiso">
                    <input type="checkbox"
                           name="roles[]"
                           value="{{ $rol->id }}"
                           @checked($seleccionados->contains($rol->id))>
                    <span>
                        <span class="nombre">{{ $rol->nombre }}</span>
                        <span class="desc">{{ $rol->descripcion }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    @endif

    <div class="aa-actions">
        <button type="submit" class="aa-btn primary lg">{{ $textoBoton }}</button>
        <a href="{{ route('usuarios.index') }}" class="aa-btn lg">Cancelar</a>
    </div>
</form>
