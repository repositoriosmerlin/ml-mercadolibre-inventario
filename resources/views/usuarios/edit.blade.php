{{-- cambio --- 10/09/2026: edición de usuario, reutiliza el formulario de alta. --}}
@extends('layouts.panel')

@section('title', 'Editar usuario')

@section('contenido')
    <div class="aa-eyebrow">USUARIOS / EDITAR</div>
    <h1 class="aa-h1">{{ $usuario->name }}</h1>
    <p class="aa-lead" style="margin-bottom:24px;">
        <span class="mono">{{ $usuario->email }}</span>
        @unless ($usuario->estaActivo())
            — cuenta deshabilitada el {{ $usuario->deshabilitado_at->format('d/m/Y') }}
        @endunless
    </p>

    @include('usuarios._form', [
        'accion' => route('usuarios.update', $usuario),
        'metodo' => 'PUT',
        'textoBoton' => 'Guardar cambios',
    ])
@endsection
