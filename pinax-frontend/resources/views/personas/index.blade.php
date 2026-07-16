{{-- resources/views/personas/index.blade.php --}}

@extends('layouts.pinax')

{{-- Título de la pestaña del navegador. --}}
@section('title', 'Personas')

{{-- Encabezado visual de la página. --}}
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-users"></i>
            Personas
        </h1>
        {{-- Dirige al formulario para registrar una nueva persona. --}}
        <a href="{{ route('personas.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            Nueva persona
        </a>
    </div>
@stop

{{-- Contenido principal de AdminLTE. --}}
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

    {{-- Formulario GET para filtrar personas mediante la API. --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filtros de búsqueda</h3>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('personas.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="cod_people">Código</label>

                        <input
                            type="number"
                            id="cod_people"
                            name="cod_people"
                            class="form-control"
                            min="1"
                            value="{{ request('cod_people') }}"
                            placeholder="Ejemplo: 1"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="dni">DNI</label>

                        <input
                            type="text"
                            id="dni"
                            name="dni"
                            class="form-control"
                            value="{{ request('dni') }}"
                            inputmode="numeric"
                            maxlength="13"
                            placeholder="Ejemplo: 0801199900001"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="ind_people">Estado</label>

                        <select id="ind_people" name="ind_people" class="form-control">
                            <option value="">Todos</option>
                            <option value="activo" @selected(request('ind_people') === 'activo')>
                                Activo
                            </option>
                            <option value="inactivo" @selected(request('ind_people') === 'inactivo')>
                                Inactivo
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>

                    {{-- Quita filtros y recarga el listado completo. --}}
                    <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">
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
                Registros encontrados: {{ count($personas) }}
            </h3>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>DNI</th>
                            <th>Nombre completo</th>
                            <th>Sexo</th>
                            <th>Edad</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($personas as $persona)
                            <tr>
                                <td>{{ data_get($persona, 'cod_people') }}</td>

                                <td>{{ data_get($persona, 'dni') }}</td>

                                <td>
                                    {{ data_get($persona, 'firstname') }}
                                    {{ data_get($persona, 'middlename') }}
                                    {{ data_get($persona, 'lastname') }}
                                </td>

                                <td>{{ data_get($persona, 'sex') }}</td>

                                <td>{{ data_get($persona, 'age') }}</td>

                                <td>{{ data_get($persona, 'tip_person') }}</td>

                                <td>
                                    @if (data_get($persona, 'ind_people') === 'activo')
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td class="text-center text-nowrap">
                                    {{-- Abre el formulario de edición de la persona. --}}
                                    <a
                                        href="{{ route('personas.edit', data_get($persona, 'cod_people')) }}"
                                        class="btn btn-sm btn-warning"
                                        title="Editar persona"
                                    >
                                        <i class="fas fa-edit"></i>
                                        Editar
                                    </a>

                                    {{-- Formulario para alternar el estado lógico de la persona. --}}
                                    <form
                                        method="POST"
                                        action="{{ route('personas.toggle-status', data_get($persona, 'cod_people')) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Deseas cambiar el estado de esta persona?');"
                                    >
                                        {{-- Protección CSRF obligatoria de Laravel. --}}
                                        @csrf

                                        {{-- Laravel interpreta la solicitud como PATCH. --}}
                                        @method('PATCH')

                                        @if (data_get($persona, 'ind_people') === 'activo')
                                            {{-- Una persona activa puede inactivarse. --}}
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Inactivar persona"
                                            >
                                                <i class="fas fa-user-slash"></i>
                                                Inactivar
                                            </button>
                                        @else
                                            {{-- Una persona inactiva puede reactivarse. --}}
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-success"
                                                title="Activar persona"
                                            >
                                                <i class="fas fa-user-check"></i>
                                                Activar
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No hay personas para mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop