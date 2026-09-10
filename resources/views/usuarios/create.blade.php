{{-- cambio --- 10/09/2026: alta de usuario con asignación de roles. --}}
@extends('layouts.panel')

@section('title', 'Crear usuario')

@section('contenido')
    <div class="aa-eyebrow">USUARIOS / NUEVO</div>
    <h1 class="aa-h1">Crear usuario.</h1>
    <p class="aa-lead" style="margin-bottom:24px;">
        Lo que la cuenta podrá hacer sale de los roles que le marques.
    </p>

    @include('usuarios._form', [
        'accion' => route('usuarios.store'),
        'textoBoton' => 'Crear usuario',
    ])
@endsection
