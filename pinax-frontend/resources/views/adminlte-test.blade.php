{{--
    Esta vista utiliza directamente el layout proporcionado
    por el paquete Laravel-AdminLTE.
--}}
@extends('adminlte::page')

{{-- Titulo mostrado en la pestaña del navegador. --}}
@section('title', 'Prueba de AdminLTE')

{{-- Encabezado de la pagina. --}}
@section('content_header')
    <h1>
        <i class=fas fa-check-circle text-success></i>
        AdminLTE instaldo correctamente
    </h1>
@stop

{{-- Contenido principal renderizado dentro del layout AdminLTE. --}}
@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Frontend Pinax</h3>

            {{--
                Este boton comprueba que el JS de AdminLTE
                se cargo correctamente: contrae y expande la tarjeta.
            --}}
            <div class="card-tools">
                <button
                    type="button"
                    class="btn btn-tool"
                    data-card-widget="collapse"
                    title="Contraer tarjeta"
                >
                <i class="fas fa-minus"></i>
            </button>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-sucess mb-0">
                <h5>
                    <i class="icon fas fa-check"></i>
                    Verificación exitosa
                </h5>

                Laravel esta cargando correctamente AdminLTE,
                Bootstrap, Font Awesome y los recursos publicados
                mediante Composer.
            </div>
        </div>
    </div>
@stop

{{-- JavaScript especifico de esta vista. --}}
@section('js')
    <script>
        // Mensaje de verificacion visible en la consola del navegador.
        console.info('AdminLTE está funcionando correctamente en Pinax. ');
    </script>
@stop