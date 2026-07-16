{{-- resources/views/personas/create.blade.php --}}

@extends('layouts.pinax')

{{-- Título mostrado en la pestaña del navegador. --}}
@section('title', 'Registrar persona')

{{-- Encabezado de contenido de AdminLTE. --}}
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-user-plus"></i>
            Registrar persona
        </h1>

        <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver al listado
        </a>
    </div>
@stop

{{-- Todo el contenido visual de la vista debe estar dentro de esta sección. --}}
@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Datos generales</h3>
        </div>

        {{-- El formulario se envía a Laravel; Laravel consume la API Node.js. --}}
        <form method="POST" action="{{ route('personas.store') }}">
            {{-- Protección obligatoria contra solicitudes CSRF. --}}
            @csrf

            <div class="card-body">

                {{-- Errores generales devueltos por la API. --}}
                @error('api')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $message }}
                    </div>
                @enderror

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dni">DNI</label>

                            {{-- Se usa texto para conservar posibles ceros iniciales. --}}
                            <input
                                type="text"
                                id="dni"
                                name="dni"
                                class="form-control @error('dni') is-invalid @enderror"
                                value="{{ old('dni') }}"
                                inputmode="numeric"
                                autocomplete="off"
                                minlength="13"
                                maxlength="13"
                                pattern="[0-9]{13}"
                                required
                            >

                            <small class="form-text text-muted">
                                El DNI debe contener exactamente 13 dígitos.
                            </small>

                            @error('dni')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="age">Edad</label>

                            <input
                                type="number"
                                id="age"
                                name="age"
                                class="form-control @error('age') is-invalid @enderror"
                                value="{{ old('age') }}"
                                min="0"
                                max="120"
                                required
                            >

                            @error('age')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="firstname">Primer nombre</label>

                            <input
                                type="text"
                                id="firstname"
                                name="firstname"
                                class="form-control @error('firstname') is-invalid @enderror"
                                value="{{ old('firstname') }}"
                                maxlength="255"
                                required
                            >

                            @error('firstname')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="middlename">Segundo nombre</label>

                            <input
                                type="text"
                                id="middlename"
                                name="middlename"
                                class="form-control @error('middlename') is-invalid @enderror"
                                value="{{ old('middlename') }}"
                                maxlength="255"
                                required
                            >

                            @error('middlename')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="lastname">Apellido</label>

                            <input
                                type="text"
                                id="lastname"
                                name="lastname"
                                class="form-control @error('lastname') is-invalid @enderror"
                                value="{{ old('lastname') }}"
                                maxlength="255"
                                required
                            >

                            @error('lastname')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sex">Sexo</label>

                            <select
                                id="sex"
                                name="sex"
                                class="form-control @error('sex') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="M" @selected(old('sex') === 'M')>Masculino</option>
                                <option value="F" @selected(old('sex') === 'F')>Femenino</option>
                                <option value="W" @selected(old('sex') === 'W')>W</option>
                                <option value="D" @selected(old('sex') === 'D')>D</option>
                            </select>

                            @error('sex')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ind_civil">Estado civil</label>

                            <select
                                id="ind_civil"
                                name="ind_civil"
                                class="form-control @error('ind_civil') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="S" @selected(old('ind_civil') === 'S')>Soltero</option>
                                <option value="M" @selected(old('ind_civil') === 'M')>Casado</option>
                                <option value="W" @selected(old('ind_civil') === 'W')>Viudo</option>
                            </select>

                            @error('ind_civil')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tip_person">Tipo de persona</label>

                            <select
                                id="tip_person"
                                name="tip_person"
                                class="form-control @error('tip_person') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="N" @selected(old('tip_person') === 'N')>Natural</option>
                                <option value="J" @selected(old('tip_person') === 'J')>Jurídica</option>
                            </select>

                            @error('tip_person')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Registrar persona
                </button>

                <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@stop

{{-- Esta sección va fuera de content. --}}
@section('js')
    <script>
        // Mantiene únicamente dígitos y limita el DNI a 13 caracteres.
        document.addEventListener('DOMContentLoaded', () => {
            const dniInput = document.getElementById('dni');

            dniInput?.addEventListener('input', (event) => {
                event.target.value = event.target.value
                    .replace(/\D/g, '')
                    .slice(0, 13);
            });
        });
    </script>
@stop