{{-- resources/views/catalogo/index.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Catálogo de cuentas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-sitemap"></i>
            Catálogo de cuentas
        </h1>

        <a href="{{ route('catalogo.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nueva cuenta
        </a>
    </div>
@stop

@section('content')

    {{-- Mensaje mostrado después de registrar o actualizar correctamente. --}}
    @if (session('success'))
        <div class="alert alert-success">
            <i class="icon fas fa-check"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Error general recibido al consumir la API. --}}
    @error('api')
        <div class="alert alert-danger">
            <h5>
                <i class="icon fas fa-exclamation-triangle"></i>
                Error de comunicación
            </h5>
            {{ $message }}
        </div>
    @enderror

    {{-- Error enviado desde el controlador al cargar el listado. --}}
    @if ($errorApi)
        <div class="alert alert-danger">
            <h5>
                <i class="icon fas fa-exclamation-triangle"></i>
                Error de comunicación
            </h5>
            {{ $errorApi }}
        </div>
    @endif

    {{-- Formulario GET para filtrar cuentas mediante la API. --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filtros de búsqueda</h3>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('catalogo.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="cod_cuenta">Código de cuenta</label>

                        <input
                            type="number"
                            id="cod_cuenta"
                            name="cod_cuenta"
                            class="form-control"
                            min="1"
                            value="{{ request('cod_cuenta') }}"
                            placeholder="Ejemplo: 12"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="cod_tipo_cuenta">Código de tipo de cuenta</label>

                        <input
                            type="number"
                            id="cod_tipo_cuenta"
                            name="cod_tipo_cuenta"
                            class="form-control"
                            min="1"
                            value="{{ request('cod_tipo_cuenta') }}"
                            placeholder="Ejemplo: 1"
                        >
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>

                    <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eraser"></i>
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla con la información obtenida exclusivamente desde la API. --}}
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">
                Registros encontrados: {{ count($cuentas) }}
            </h3>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>N.° de cuenta</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Cuenta padre</th>
                            <th>Nivel</th>
                            <th>Naturaleza</th>
                            <th>Acepta mov.</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($cuentas as $cuenta)
                            <tr>
                                <td>{{ data_get($cuenta, 'cod_cuenta') }}</td>

                                <td>{{ data_get($cuenta, 'cod_num_cuenta') }}</td>

                                <td>{{ data_get($cuenta, 'nom_cuenta') }}</td>

                                <td>{{ data_get($cuenta, 'cod_tipo_cuenta') }}</td>

                                <td>{{ data_get($cuenta, 'cod_cuenta_padre') ?? '—' }}</td>

                                <td>{{ data_get($cuenta, 'num_nivel_jerarquia') }}</td>

                                <td class="text-capitalize">
                                    {{ data_get($cuenta, 'ind_naturaleza') }}
                                </td>

                                <td class="text-capitalize">
                                    {{ data_get($cuenta, 'ind_acepta_movimiento') }}
                                </td>

                                <td>
                                    @if (data_get($cuenta, 'ind_estado') === 'activo')
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td class="text-center text-nowrap">
                                    {{-- Abre el formulario de edición de la cuenta. --}}
                                    <a
                                        href="{{ route('catalogo.edit', data_get($cuenta, 'cod_cuenta')) }}"
                                        class="btn btn-sm btn-warning"
                                        title="Editar cuenta"
                                    >
                                        <i class="fas fa-edit"></i>
                                        Editar
                                    </a>

                                    {{-- Formulario para alternar el estado lógico de la cuenta. --}}
                                    <form
                                        method="POST"
                                        action="{{ route('catalogo.toggle-status', data_get($cuenta, 'cod_cuenta')) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Deseas cambiar el estado de esta cuenta?');"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        @if (data_get($cuenta, 'ind_estado') === 'activo')
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Inactivar cuenta"
                                            >
                                                <i class="fas fa-ban"></i>
                                                Inactivar
                                            </button>
                                        @else
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-success"
                                                title="Activar cuenta"
                                            >
                                                <i class="fas fa-check"></i>
                                                Activar
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    No hay cuentas para mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
