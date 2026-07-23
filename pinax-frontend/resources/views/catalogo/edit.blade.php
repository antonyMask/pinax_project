{{-- resources/views/catalogo/edit.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Editar cuenta contable')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-edit"></i>
            Editar cuenta contable #{{ data_get($cuenta, 'cod_cuenta') }}
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

        <form
            method="POST"
            action="{{ route('catalogo.update', data_get($cuenta, 'cod_cuenta')) }}"
        >
            @csrf
            @method('PUT')

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
                                value="{{ old('cod_num_cuenta', data_get($cuenta, 'cod_num_cuenta')) }}"
                                maxlength="50"
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
                                value="{{ old('nom_cuenta', data_get($cuenta, 'nom_cuenta')) }}"
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
                                value="{{ old('cod_cuenta_padre', data_get($cuenta, 'cod_cuenta_padre')) }}"
                                min="0"
                            >

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
                                value="{{ old('num_nivel_jerarquia', data_get($cuenta, 'num_nivel_jerarquia')) }}"
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

                            @php
                                $estadoActual = old('ind_estado', data_get($cuenta, 'ind_estado'));
                            @endphp

                            <select
                                id="ind_estado"
                                name="ind_estado"
                                class="form-control @error('ind_estado') is-invalid @enderror"
                                required
                            >
                                <option value="activo" @selected($estadoActual === 'activo')>Activo</option>
                                <option value="inactivo" @selected($estadoActual === 'inactivo')>Inactivo</option>
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

                            @php
                                $naturalezaActual = old('ind_naturaleza_cuenta', data_get($cuenta, 'ind_naturaleza'));
                            @endphp

                            <select
                                id="ind_naturaleza_cuenta"
                                name="ind_naturaleza_cuenta"
                                class="form-control @error('ind_naturaleza_cuenta') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="deudora" @selected($naturalezaActual === 'deudora')>Deudora</option>
                                <option value="acreedora" @selected($naturalezaActual === 'acreedora')>Acreedora</option>
                            </select>

                            @error('ind_naturaleza_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ind_acepta_movimiento">¿Acepta movimiento?</label>

                            @php
                                $movimientoActual = old('ind_acepta_movimiento', data_get($cuenta, 'ind_acepta_movimiento'));
                            @endphp

                            <select
                                id="ind_acepta_movimiento"
                                name="ind_acepta_movimiento"
                                class="form-control @error('ind_acepta_movimiento') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="si" @selected($movimientoActual === 'si')>Sí</option>
                                <option value="no" @selected($movimientoActual === 'no')>No</option>
                            </select>

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
                    >{{ old('des_cuenta', data_get($cuenta, 'des_cuenta')) }}</textarea>

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
                                value="{{ old('cod_tipo_cuenta', data_get($cuenta, 'cod_tipo_cuenta')) }}"
                                min="1"
                                required
                            >

                            @error('cod_tipo_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-8 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input
                                type="checkbox"
                                id="actualizar_tipo"
                                name="actualizar_tipo"
                                class="form-check-input"
                                value="1"
                                {{ old('actualizar_tipo') ? 'checked' : '' }}
                            >

                            <label class="form-check-label" for="actualizar_tipo">
                                También actualizar los datos de este tipo de cuenta
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Solo se necesita si el checkbox "actualizar_tipo" está marcado. --}}
                <div id="bloque-actualizar-tipo" class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nom_tipo_cuenta">Nombre del tipo</label>

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
                            <label for="ind_naturaleza_tipo">Naturaleza del tipo</label>

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
                    Guardar cambios
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
        // Muestra u oculta los campos del tipo segun el checkbox "actualizar_tipo".
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxActualizarTipo = document.getElementById('actualizar_tipo');
            const bloqueActualizarTipo = document.getElementById('bloque-actualizar-tipo');

            const actualizarVisibilidad = () => {
                bloqueActualizarTipo.style.display = checkboxActualizarTipo.checked ? 'flex' : 'none';
            };

            checkboxActualizarTipo?.addEventListener('change', actualizarVisibilidad);
            actualizarVisibilidad();
        });
    </script>
@stop
