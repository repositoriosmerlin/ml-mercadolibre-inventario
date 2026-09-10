{{-- cambio --- 10/09/2026: alta de rol con asignación de permisos. --}}
@extends('layouts.panel')

@section('title', 'Crear rol')

@section('contenido')
    <div class="aa-eyebrow">USUARIOS / ROLES / NUEVO</div>
    <h1 class="aa-h1">Crear rol.</h1>
    <p class="aa-lead" style="margin-bottom:24px;">
        Marca lo que este rol podrá hacer. Los permisos salen de las rutas del sistema.
    </p>

    @include('roles._form', [
        'accion' => route('roles.store'),
        'textoBoton' => 'Crear rol',
    ])
@endsection
