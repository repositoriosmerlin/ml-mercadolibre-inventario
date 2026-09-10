{{-- cambio --- 10/09/2026: catálogo de permisos. Vista de reporte: tono
     neutro, sin chips de color, datos técnicos en mono. --}}
@extends('layouts.panel')

@section('title', 'Permisos')

@section('contenido')
    <div class="aa-head-row">
        <div>
            <div class="aa-eyebrow">USUARIOS / PERMISOS</div>
            <h1 class="aa-h1">Permisos.</h1>
            <p class="aa-lead">Acciones que un rol puede habilitar.</p>
        </div>

        <a href="{{ route('permisos.create') }}" class="aa-btn primary">Nuevo permiso</a>
    </div>

    @if ($permisos->isEmpty())
        <div class="aa-empty">
            <span class="dot"></span>
            <span class="dot" style="background:var(--yellow);"></span>
            <span class="dot" style="background:var(--red);"></span>
            <div class="title">Sin permisos en el catálogo</div>
            <div class="sub">Crea el primero para poder asignarlo a un rol.</div>
        </div>
    @else
        <div class="aa-table-wrap">
            <table class="aa-table">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Módulo</th>
                        <th>Descripción</th>
                        <th>Origen</th>
                        <th>Roles</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permisos as $permiso)
                        <tr>
                            <td class="mono" style="font-weight:600;">{{ $permiso->clave }}</td>
                            <td style="font-size:13px; color:var(--ink-soft);">{{ $permiso->modulo }}</td>
                            <td style="font-size:13px; color:var(--ink-soft);">{{ $permiso->descripcion }}</td>
                            <td style="font-size:12px; color:var(--muted);">
                                {{ $permiso->esDeRuta() ? 'ruta del sistema' : 'manual' }}
                            </td>
                            <td class="mono">{{ $permiso->roles_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
