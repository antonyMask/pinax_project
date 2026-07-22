@extends('layouts.pinax')

@section('title', 'Crear Usuario')
@section('header', 'Crear Nuevo Usuario')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-user-plus"></i> Registrar Nuevo Usuario
        </h3>
    </div>
    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nombre de Usuario *</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               placeholder="Ej: jperez" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cod_tipusers">Rol *</label>
                        <select class="form-control @error('cod_tipusers') is-invalid @enderror" 
                                id="cod_tipusers" 
                                name="cod_tipusers" 
                                required>
                            <option value="">Selecciona un rol...</option>
                            <option value="1" {{ old('cod_tipusers') == 1 ? 'selected' : '' }}>Administrador</option>
                            <option value="2" {{ old('cod_tipusers') == 2 ? 'selected' : '' }}>Contador</option>
                            <option value="3" {{ old('cod_tipusers') == 3 ? 'selected' : '' }}>Usuario</option>
                        </select>
                        @error('cod_tipusers')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="Mínimo 6 caracteres" 
                               required>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña *</label>
                        <input type="password" 
                               class="form-control @error('password_confirmation') is-invalid @enderror" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="Repite la contraseña" 
                               required>
                        @error('password_confirmation')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cod_people">Código de Persona (opcional)</label>
                        <input type="number" 
                               class="form-control @error('cod_people') is-invalid @enderror" 
                               id="cod_people" 
                               name="cod_people" 
                               placeholder="Ej: 1" 
                               value="{{ old('cod_people') }}">
                        @error('cod_people')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Asocia este usuario a una persona existente.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ind_usr">Estado *</label>
                        <select class="form-control @error('ind_usr') is-invalid @enderror" 
                                id="ind_usr" 
                                name="ind_usr" 
                                required>
                            <option value="1" {{ old('ind_usr') == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('ind_usr') == 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('ind_usr')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ind_ins">Institución *</label>
                        <select class="form-control @error('ind_ins') is-invalid @enderror" 
                                id="ind_ins" 
                                name="ind_ins" 
                                required>
                            <option value="1" {{ old('ind_ins') == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('ind_ins') == 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('ind_ins')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Requisitos de contraseña:</strong> Mínimo 6 caracteres. Recomendamos usar mayúsculas, minúsculas y números.
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Usuario
            </button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection