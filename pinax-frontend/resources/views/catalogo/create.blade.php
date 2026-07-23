{{-- resources/views/catalogo/create.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Registrar cuenta contable')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-plus"></i>
            Registrar cuenta contable
        </h1>

        <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver al listado
        </a>
    </div>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Datos de la cuenta</h3>
        </div>

        {{-- El formulario se envía a Laravel; Laravel consume la API Node.js. --}}
        <form method="POST" action="{{ route('catalogo.store') }}">
            @csrf

            <div class="card-body">

                @error('api')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $message }}
                    </div>
                @enderror

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cod_num_cuenta">N.° de cuenta</label>

                            <input
                                type="text"
                                id="cod_num_cuenta"
                                name="cod_num_cuenta"
                                class="form-control @error('cod_num_cuenta') is-invalid @enderror"
                                value="{{ old('cod_num_cuenta') }}"
                                maxlength="50"
                                placeholder="Ejemplo: 1101"
                                required
                            >

                            @error('cod_num_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nom_cuenta">Nombre de la cuenta</label>

                            <input
                                type="text"
                                id="nom_cuenta"
                                name="nom_cuenta"
                                class="form-control @error('nom_cuenta') is-invalid @enderror"
                                value="{{ old('nom_cuenta') }}"
                                maxlength="255"
                                required
                            >

                            @error('nom_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cod_cuenta_padre">Cuenta padre (opcional)</label>

                            <input
                                type="number"
                                id="cod_cuenta_padre"
                                name="cod_cuenta_padre"
                                class="form-control @error('cod_cuenta_padre') is-invalid @enderror"
                                value="{{ old('cod_cuenta_padre') }}"
                                min="0"
                                placeholder="Dejar vacío si no tiene"
                            >

                            <small class="form-text text-muted">
                                Código de la cuenta superior en la jerarquía.
                            </small>

                            @error('cod_cuenta_padre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="num_nivel_jerarquia">Nivel de jerarquía</label>

                            <input
                                type="number"
                                id="num_nivel_jerarquia"
                                name="num_nivel_jerarquia"
                                class="form-control @error('num_nivel_jerarquia') is-invalid @enderror"
                                value="{{ old('num_nivel_jerarquia', 1) }}"
                                min="1"
                                required
                            >

                            @error('num_nivel_jerarquia')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ind_estado">Estado</label>

                            <select
                                id="ind_estado"
                                name="ind_estado"
                                class="form-control @error('ind_estado') is-invalid @enderror"
                            >
                                <option value="activo" @selected(old('ind_estado', 'activo') === 'activo')>Activo</option>
                                <option value="inactivo" @selected(old('ind_estado') === 'inactivo')>Inactivo</option>
                            </select>

                            @error('ind_estado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ind_naturaleza_cuenta">Naturaleza de la cuenta</label>

                            <select
                                id="ind_naturaleza_cuenta"
                                name="ind_naturaleza_cuenta"
                                class="form-control @error('ind_naturaleza_cuenta') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="deudora" @selected(old('ind_naturaleza_cuenta') === 'deudora')>Deudora</option>
                                <option value="acreedora" @selected(old('ind_naturaleza_cuenta') === 'acreedora')>Acreedora</option>
                            </select>

                            @error('ind_naturaleza_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ind_acepta_movimiento">¿Acepta movimiento?</label>

                            <select
                                id="ind_acepta_movimiento"
                                name="ind_acepta_movimiento"
                                class="form-control @error('ind_acepta_movimiento') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="si" @selected(old('ind_acepta_movimiento') === 'si')>Sí</option>
                                <option value="no" @selected(old('ind_acepta_movimiento') === 'no')>No</option>
                            </select>

                            <small class="form-text text-muted">
                                Las cuentas de mayor jerarquía (mayores) normalmente no aceptan movimiento.
                            </small>

                            @error('ind_acepta_movimiento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="des_cuenta">Descripción (opcional)</label>

                    <textarea
                        id="des_cuenta"
                        name="des_cuenta"
                        class="form-control @error('des_cuenta') is-invalid @enderror"
                        rows="2"
                        maxlength="255"
                    >{{ old('des_cuenta') }}</textarea>

                    @error('des_cuenta')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <hr>

                <h5>
                    <i class="fas fa-tags"></i>
                    Tipo de cuenta
                </h5>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cod_tipo_cuenta">Código de tipo de cuenta</label>

                            <input
                                type="number"
                                id="cod_tipo_cuenta"
                                name="cod_tipo_cuenta"
                                class="form-control @error('cod_tipo_cuenta') is-invalid @enderror"
                                value="{{ old('cod_tipo_cuenta', 0) }}"
                                min="0"
                                required
                            >

                            <small class="form-text text-muted">
                                Ingrese el código de un tipo existente, o <strong>0</strong>
                                para crear un tipo de cuenta nuevo.
                            </small>

                            @error('cod_tipo_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Este bloque solo se necesita si cod_tipo_cuenta = 0. --}}
                <div id="bloque-nuevo-tipo" class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nom_tipo_cuenta">Nombre del nuevo tipo</label>

                            <input
                                type="text"
                                id="nom_tipo_cuenta"
                                name="nom_tipo_cuenta"
                                class="form-control @error('nom_tipo_cuenta') is-invalid @enderror"
                                value="{{ old('nom_tipo_cuenta') }}"
                                maxlength="255"
                            >

                            @error('nom_tipo_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ind_naturaleza_tipo">Naturaleza del nuevo tipo</label>

                            <select
                                id="ind_naturaleza_tipo"
                                name="ind_naturaleza_tipo"
                                class="form-control @error('ind_naturaleza_tipo') is-invalid @enderror"
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="deudora" @selected(old('ind_naturaleza_tipo') === 'deudora')>Deudora</option>
                                <option value="acreedora" @selected(old('ind_naturaleza_tipo') === 'acreedora')>Acreedora</option>
                            </select>

                            @error('ind_naturaleza_tipo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="des_tipo_cuenta">Descripción del tipo (opcional)</label>

                            <input
                                type="text"
                                id="des_tipo_cuenta"
                                name="des_tipo_cuenta"
                                class="form-control @error('des_tipo_cuenta') is-invalid @enderror"
                                value="{{ old('des_tipo_cuenta') }}"
                                maxlength="255"
                            >

                            @error('des_tipo_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Registrar cuenta
                </button>

                <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        // Muestra u oculta los campos de "nuevo tipo" segun cod_tipo_cuenta.
        document.addEventListener('DOMContentLoaded', () => {
            const inputCodTipo = document.getElementById('cod_tipo_cuenta');
            const bloqueNuevoTipo = document.getElementById('bloque-nuevo-tipo');

            const actualizarVisibilidad = () => {
                const esNuevoTipo = Number(inputCodTipo.value) === 0;
                bloqueNuevoTipo.style.display = esNuevoTipo ? 'flex' : 'none';
            };

            inputCodTipo?.addEventListener('input', actualizarVisibilidad);
            actualizarVisibilidad();
        });
    </script>
@stop
