{{-- cambio --- 10/09/2026: formulario compartido por crear y editar un rol.
     Vista operativa: el amarillo marca la acción que el usuario debe tomar
     ahora (guardar), y el catálogo de permisos se agrupa por módulo porque
     plano son decenas de casillas sin jerarquía. --}}

<form method="POST" action="{{ $accion }}" id="form-rol">
    @csrf
    @isset($metodo)
        @method($metodo)
    @endisset

    <div class="aa-card" style="margin-bottom:24px;">
        <div class="section-title">Identificación</div>

        <div class="aa-field">
            <label class="aa-label" for="nombre">Nombre del rol</label>
            <input id="nombre"
                   class="aa-input @error('nombre') bad @enderror"
                   type="text"
                   name="nombre"
                   value="{{ old('nombre', $rol->nombre) }}"
                   maxlength="60"
                   required
                   autofocus>
            @error('nombre')
                <div class="aa-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="aa-field" style="margin-bottom:0;">
            <label class="aa-label" for="descripcion">Descripción</label>
            <input id="descripcion"
                   class="aa-input @error('descripcion') bad @enderror"
                   type="text"
                   name="descripcion"
                   value="{{ old('descripcion', $rol->descripcion) }}"
                   maxlength="160"
                   placeholder="Para qué sirve este rol">
            @error('descripcion')
                <div class="aa-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if ($permisosPorModulo->isEmpty())
        <div class="aa-empty" style="margin-bottom:24px;">
            <span class="dot"></span>
            <span class="dot" style="background:var(--yellow);"></span>
            <span class="dot" style="background:var(--red);"></span>
            <div class="title">El catálogo de permisos está vacío</div>
            <div class="sub">
                Los permisos se leen de las rutas. Sincroniza el catálogo y vuelve a este formulario.
            </div>
            <div style="margin-top:24px;">
                <a href="{{ route('permisos.index') }}" class="aa-btn">Ir a Permisos</a>
            </div>
        </div>
    @else
        <div class="aa-head-row" style="margin-bottom:12px;">
            <div class="section-title" style="margin:0;">
                Permisos · {{ $permisosPorModulo->flatten()->count() }} disponibles
            </div>
            <button type="button" class="aa-btn sm" data-marcar="todos">Marcar todos</button>
        </div>

        @error('permisos')
            <div class="aa-error" style="margin-bottom:12px;">{{ $message }}</div>
        @enderror
        @error('permisos.*')
            <div class="aa-error" style="margin-bottom:12px;">{{ $message }}</div>
        @enderror

        <div class="aa-grid" style="margin-bottom:24px;">
            @foreach ($permisosPorModulo as $modulo => $permisos)
                <div class="aa-card" data-modulo>
                    <div class="aa-head-row" style="margin-bottom:12px;">
                        <div class="section-title" style="margin:0;">{{ $modulo }}</div>
                        <button type="button" class="aa-btn sm" data-marcar="modulo">Todo</button>
                    </div>

                    @foreach ($permisos as $permiso)
                        <label class="aa-permiso">
                            <input type="checkbox"
                                   name="permisos[]"
                                   value="{{ $permiso->id }}"
                                   @checked($seleccionados->contains($permiso->id))>
                            <span>
                                <span class="mono">{{ $permiso->clave }}</span>
                                <span class="desc">{{ $permiso->descripcion }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    <div class="aa-actions">
        <button type="submit" class="aa-btn primary lg">{{ $textoBoton }}</button>
        <a href="{{ route('roles.index') }}" class="aa-btn lg">Cancelar</a>
    </div>
</form>

<script>
    // Marcar todo: dentro de una card con [data-modulo] alcanza a ese módulo;
    // el botón de arriba alcanza al formulario completo. Alterna, para que el
    // segundo clic desmarque en vez de dejar al usuario destildando a mano.
    document.querySelectorAll('[data-marcar]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var ambito = boton.dataset.marcar === 'modulo'
                ? boton.closest('[data-modulo]')
                : document.getElementById('form-rol');

            var casillas = ambito.querySelectorAll('input[type="checkbox"]');
            var faltaAlguna = Array.from(casillas).some(function (casilla) {
                return ! casilla.checked;
            });

            casillas.forEach(function (casilla) {
                casilla.checked = faltaAlguna;
            });
        });
    });
</script>
