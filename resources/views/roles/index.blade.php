@extends('layouts.panel')

@section('title', 'Roles')

@section('contenido')
    <div class="aa-head-row">
        <div>
            <div class="aa-eyebrow">USUARIOS / ROLES</div>
            <h1 class="aa-h1">Roles.</h1>
            <p class="aa-lead">Conjuntos de permisos que se asignan a un usuario.</p>
        </div>

        <a href="{{ route('roles.create') }}" class="aa-btn primary">Nuevo rol</a>
    </div>

    @if ($roles->isEmpty())
        <div class="aa-empty">
            <span class="dot"></span>
            <span class="dot" style="background:var(--yellow);"></span>
            <span class="dot" style="background:var(--red);"></span>
            <div class="title">Sin roles definidos</div>
            <div class="sub">Falta el modelo y la migración de roles.</div>
        </div>
    @else
        <div class="aa-table-wrap">
            <table class="aa-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Descripción</th>
                        <th>Permisos</th>
                        <th>Usuarios</th>
                        <th class="col-accion">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $rol)
                        <tr>
                            <td class="mono" style="font-weight:600;">{{ $rol->id }}</td>
                            <td>{{ $rol->nombre }}</td>
                            <td style="font-size:13px; color:var(--ink-soft);">{{ $rol->descripcion }}</td>
                            <td class="mono">{{ $rol->permisos_count }}</td>
                            <td class="mono">{{ $rol->usuarios_count }}</td>
                            <td class="col-accion">
                                <a href="{{ route('roles.edit', $rol) }}" class="aa-btn sm">Editar</a>

                                <form action="{{ route('roles.destroy', $rol) }}" method="POST"
                                      style="display:inline-block;"
                                      data-confirmar="El rol {{ $rol->nombre }} dejará de aplicarse a sus usuarios.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="aa-btn sm danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
