{{-- resources/views/personas/edit.blade.php --}}

@extends('layouts.pinax')

{{-- Título mostrado en la pestaña del navegador. --}}
@section('title', 'Editar persona')

{{-- Encabezado principal de la página. --}}
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-user-edit"></i>
            Editar persona
        </h1>

        <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver al listado
        </a>
    </div>
@stop

{{-- Contenido principal de AdminLTE. --}}
@section('content')
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title">
                Editando persona #{{ data_get($persona, 'cod_people') }}
            </h3>
        </div>

        {{-- Envía una petición PUT hacia Laravel. --}}
        <form
            method="POST"
            action="{{ route('personas.update', data_get($persona, 'cod_people')) }}"
        >
            {{-- Protección CSRF de Laravel. --}}
            @csrf

            {{-- Convierte el método POST del formulario en PUT. --}}
            @method('PUT')

            <div class="card-body">

                {{-- Error general enviado por la API o Laravel. --}}
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

                            {{-- Se usa texto para no eliminar ceros iniciales. --}}
                            <input
                                type="text"
                                id="dni"
                                name="dni"
                                class="form-control @error('dni') is-invalid @enderror"
                                value="{{ old('dni', data_get($persona, 'dni')) }}"
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
                                value="{{ old('age', data_get($persona, 'age')) }}"
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
                                value="{{ old('firstname', data_get($persona, 'firstname')) }}"
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
                                value="{{ old('middlename', data_get($persona, 'middlename')) }}"
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
                                value="{{ old('lastname', data_get($persona, 'lastname')) }}"
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sex">Sexo</label>

                            <select
                                id="sex"
                                name="sex"
                                class="form-control @error('sex') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="M" @selected(old('sex', data_get($persona, 'sex')) === 'M')>
                                    Masculino
                                </option>
                                <option value="F" @selected(old('sex', data_get($persona, 'sex')) === 'F')>
                                    Femenino
                                </option>
                                <option value="W" @selected(old('sex', data_get($persona, 'sex')) === 'W')>
                                    W
                                </option>
                                <option value="D" @selected(old('sex', data_get($persona, 'sex')) === 'D')>
                                    D
                                </option>
                            </select>

                            @error('sex')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ind_civil">Estado civil</label>

                            <select
                                id="ind_civil"
                                name="ind_civil"
                                class="form-control @error('ind_civil') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="S" @selected(old('ind_civil', data_get($persona, 'ind_civil')) === 'S')>
                                    Soltero
                                </option>
                                <option value="M" @selected(old('ind_civil', data_get($persona, 'ind_civil')) === 'M')>
                                    Casado
                                </option>
                                <option value="W" @selected(old('ind_civil', data_get($persona, 'ind_civil')) === 'W')>
                                    Viudo
                                </option>
                            </select>

                            @error('ind_civil')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tip_person">Tipo de persona</label>

                            <select
                                id="tip_person"
                                name="tip_person"
                                class="form-control @error('tip_person') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="N" @selected(old('tip_person', data_get($persona, 'tip_person')) === 'N')>
                                    Natural
                                </option>
                                <option value="J" @selected(old('tip_person', data_get($persona, 'tip_person')) === 'J')>
                                    Jurídica
                                </option>
                            </select>

                            @error('tip_person')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ind_people">Estado</label>

                            {{-- La API exige este campo en cada actualización. --}}
                            <select
                                id="ind_people"
                                name="ind_people"
                                class="form-control @error('ind_people') is-invalid @enderror"
                                required
                            >
                                <option
                                    value="activo"
                                    @selected(old('ind_people', data_get($persona, 'ind_people')) === 'activo')
                                >
                                    Activo
                                </option>

                                <option
                                    value="inactivo"
                                    @selected(old('ind_people', data_get($persona, 'ind_people')) === 'inactivo')
                                >
                                    Inactivo
                                </option>
                            </select>

                            @error('ind_people')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i>
                    Guardar cambios
                </button>

                <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@stop

{{-- JavaScript exclusivo de esta página. --}}
@section('js')
    <script>
        // Impide letras, símbolos y más de 13 caracteres en el DNI.
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