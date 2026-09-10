{{-- cambio --- 10/09/2026: alta manual de permisos. Vista operativa: el
     amarillo marca la acción que el usuario debe tomar ahora. --}}
@extends('layouts.panel')

@section('title', 'Nuevo permiso')

@section('contenido')
    <div class="aa-eyebrow">USUARIOS / PERMISOS / NUEVO</div>
    <h1 class="aa-h1">Nuevo permiso.</h1>
    <p class="aa-lead" style="margin-bottom:24px;">
        Queda disponible para marcarlo en cualquier rol.
    </p>

    <form method="POST" action="{{ route('permisos.store') }}">
        @csrf

        <div class="aa-card" style="max-width:560px; margin-bottom:24px;">
            <div class="aa-field">
                <label class="aa-label" for="clave">Clave</label>
                <input id="clave"
                       class="aa-input mono @error('clave') bad @enderror"
                       type="text"
                       name="clave"
                       value="{{ old('clave') }}"
                       placeholder="reportes.ver"
                       maxlength="60"
                       required
                       autofocus>
                @error('clave')
                    <div class="aa-error">{{ $message }}</div>
                @enderror
                <div class="aa-hint">
                    Formato <span class="mono">modulo.accion</span>, en minúsculas. El módulo se
                    toma de lo que va antes del punto y agrupa el permiso en el formulario de rol.
                </div>
            </div>

            <div class="aa-field" style="margin-bottom:0;">
                <label class="aa-label" for="descripcion">Descripción</label>
                <input id="descripcion"
                       class="aa-input @error('descripcion') bad @enderror"
                       type="text"
                       name="descripcion"
                       value="{{ old('descripcion') }}"
                       placeholder="Qué habilita este permiso"
                       maxlength="160">
                @error('descripcion')
                    <div class="aa-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="aa-alert" style="max-width:560px; margin-bottom:24px;">
            Un permiso creado aquí no protege nada por sí solo: alguien tiene que consultarlo
            desde una vista o un servicio. Los que protegen una pantalla completa se declaran
            en la ruta y aparecen en el listado como
            <span class="mono">ruta del sistema</span>.
        </div>

        <div class="aa-actions">
            <button type="submit" class="aa-btn primary lg">Crear permiso</button>
            <a href="{{ route('permisos.index') }}" class="aa-btn lg">Cancelar</a>
        </div>
    </form>
@endsection
