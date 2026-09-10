@extends('layouts.guest')

@section('titulo', 'Acceso')

@section('contenido')
    <div class="aa-eyebrow">MERLIN / ACCESO</div>
    <h1 class="aa-h1" style="font-size:30px; margin:10px 0 24px;">Inicia sesión.</h1>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="aa-field">
            <label class="aa-label" for="email">Correo</label>
            <input id="email"
                   class="aa-input @error('email') bad @enderror"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username">
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
                   required
                   autocomplete="current-password">
            @error('password')
                <div class="aa-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="aa-field">
            <label class="aa-check">
                <input type="checkbox" name="recordarme" value="1">
                Mantener la sesión abierta
            </label>
        </div>

        <button type="submit" class="aa-btn primary lg" style="width:100%;">Entrar</button>
    </form>
@endsection
