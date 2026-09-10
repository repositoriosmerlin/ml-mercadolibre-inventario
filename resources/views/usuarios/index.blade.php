{{-- cambio --- 10/09/2026: agrega roles y estado, y cambia Eliminar por
     Deshabilitar. Vista de reporte: tono neutro, sin chips de color. --}}
@extends('layouts.panel')

@section('title', 'Usuarios')

@section('contenido')
    <div class="aa-head-row">
        <div>
            <div class="aa-eyebrow">USUARIOS / LISTADO</div>
            <h1 class="aa-h1">Usuarios.</h1>
            <p class="aa-lead">Cuentas con acceso al sistema.</p>
        </div>

        <a href="{{ route('usuarios.create') }}" class="aa-btn primary">Nuevo usuario</a>
    </div>

    @if ($usuarios->isEmpty())
        <div class="aa-empty">
            <span class="dot"></span>
            <span class="dot" style="background:var(--yellow);"></span>
            <span class="dot" style="background:var(--red);"></span>
            <div class="title">Sin usuarios registrados</div>
            <div class="sub">Crea el primer usuario para empezar a asignar roles.</div>
        </div>
    @else
        <div class="aa-table-wrap">
            <table class="aa-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Roles</th>
                        <th>Estado</th>
                        <th>Alta</th>
                        <th class="col-accion">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td class="mono" style="font-weight:600;">{{ $usuario->id }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td class="mono">{{ $usuario->email }}</td>
                            <td style="font-size:13px; color:var(--ink-soft);">
                                {{ $usuario->roles->pluck('nombre')->join(', ') ?: 'sin rol' }}
                            </td>
                            <td style="font-size:13px; color:var(--ink-soft);">
                                {{ $usuario->estaActivo() ? 'activo' : 'deshabilitado' }}
                            </td>
                            <td style="font-size:12px; color:var(--muted);">
                                {{ $usuario->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="col-accion">
                                <a href="{{ route('usuarios.edit', $usuario) }}" class="aa-btn sm">Editar</a>

                                <form action="{{ route('usuarios.estado', $usuario) }}" method="POST"
                                      style="display:inline-block;"
                                      @if ($usuario->estaActivo())
                                          data-confirmar="{{ $usuario->name }} dejará de poder iniciar sesión. La cuenta se conserva."
                                          data-confirmar-titulo="¿Deshabilitar cuenta?"
                                          data-confirmar-boton="Deshabilitar"
                                      @endif>
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            class="aa-btn sm {{ $usuario->estaActivo() ? 'danger' : '' }}">
                                        {{ $usuario->estaActivo() ? 'Deshabilitar' : 'Habilitar' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
