<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('titulo', 'Acceso') · {{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ asset('tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('components.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <main class="aa-scope aa-auth">
        <div class="aa-auth-card">
            @yield('contenido')
        </div>
    </main>

    <script>
        @if (session('estado'))
            Swal.fire({
                icon: 'success',
                title: @json(session('estado')),
                showConfirmButton: false,
                timer: 1000,
            });
        @endif
    </script>
</body>
</html>
