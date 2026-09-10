{{--
    Layout del panel.

    AdminLTE pone el esqueleto (sidebar, navbar, wrapper) y este layout
    inyecta el sistema de diseno Auto Animator encima. Las vistas del
    modulo extienden este archivo y definen la seccion 'contenido'.
--}}
@extends('adminlte::page')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('components.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte-merlin.css') }}">
@stop

@section('content')
    <div class="aa-scope">
        @yield('contenido')
    </div>
@stop

@section('adminlte_js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Mensajes del servidor — siempre por SweetAlert2, nunca alert() nativo.
        @if (session('estado'))
            Swal.fire({
                icon: 'success',
                title: @json(session('estado')),
                showConfirmButton: false,
                timer: 1000,
            });
        @endif

        {{-- cambio --- 10/09/2026: los avisos de error sí llevan botón. Un
             mensaje que explica por qué algo no se pudo hacer no puede
             desaparecer solo a los 900 ms. --}}
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'No se pudo completar',
                text: @json(session('error')),
                confirmButtonColor: '#0A0A0A',
            });
        @endif

        // Confirmación destructiva para cualquier formulario marcado.
        document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmado === '1') {
                    return;
                }

                event.preventDefault();

                {{-- cambio --- 10/09/2026: título y botón configurables. No
                     toda acción destructiva es "eliminar": deshabilitar una
                     cuenta la conserva, y decir "Eliminar" ahí asustaría de
                     más a quien la está deshabilitando. --}}
                Swal.fire({
                    title: form.dataset.confirmarTitulo || '¿Eliminar registro?',
                    text: form.dataset.confirmar,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: form.dataset.confirmarBoton || 'Eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#E11D2E',
                    cancelButtonColor: '#8A8A8A',
                }).then(function (resultado) {
                    if (resultado.isConfirmed) {
                        form.dataset.confirmado = '1';
                        form.submit();
                    }
                });
            });
        });
    </script>
@stop
