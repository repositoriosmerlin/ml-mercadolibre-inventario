{{-- cambio --- 10/09/2026: edición de rol, reutiliza el formulario de alta. --}}
@extends('layouts.panel')

@section('title', 'Editar rol')

@section('contenido')
    <div class="aa-eyebrow">USUARIOS / ROLES / EDITAR</div>
    <h1 class="aa-h1">{{ $rol->nombre }}</h1>
    <p class="aa-lead" style="margin-bottom:24px;">
        {{ $rol->usuarios()->count() }} usuario(s) con este rol.
    </p>

    @include('roles._form', [
        'accion' => route('roles.update', $rol),
        'metodo' => 'PUT',
        'textoBoton' => 'Guardar cambios',
    ])
@endsection
