@extends('layouts.pinax')

@section('title', 'Detalle de Usuario')
@section('header', 'Detalle de Usuario')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> 
                    Información del Usuario
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-hashtag"></i> Código:</strong>
                        <p>{{ $usuario['cod_user'] ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-user-tag"></i> Usuario:</strong>
                        <p>{{ $usuario['name'] ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-user-shield"></i> Rol:</strong>
                        <p>
                            <span class="badge 
                                @if(($usuario['cod_tipusers'] ?? 0) == 1) bg-danger
                                @elseif(($usuario['cod_tipusers'] ?? 0) == 2) bg-warning
                                @else bg-info
                                @endif" style="font-size: 14px; padding: 8px 16px;">
                                {{ $usuario['role'] ?? 'Sin rol' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-user-check"></i> Estado:</strong>
                        <p>
                            <span class="badge {{ ($usuario['ind_usr'] ?? 0) == 1 ? 'bg-success' : 'bg-danger' }}" style="font-size: 14px; padding: 8px 16px;">
                                {{ ($usuario['ind_usr'] ?? 0) == 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-user"></i> Persona Asociada:</strong>
                        <p>{{ $usuario['firstname'] ?? '' }} {{ $usuario['lastname'] ?? 'No asociada' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-building"></i> Institución:</strong>
                        <p>
                            <span class="badge {{ ($usuario['ind_ins'] ?? 0) == 1 ? 'bg-success' : 'bg-danger' }}">
                                {{ ($usuario['ind_ins'] ?? 0) == 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('usuarios.edit', $usuario['cod_user']) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Resumen
                </h3>
            </div>
            <div class="card-body">
                <div class="info-box bg-{{ ($usuario['ind_usr'] ?? 0) == 1 ? 'success' : 'danger' }}">
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number">
                            {{ ($usuario['ind_usr'] ?? 0) == 1 ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
                <div class="info-box bg-info">
                    <div class="info-box-content">
                        <span class="info-box-text">Rol</span>
                        <span class="info-box-number">
                            {{ $usuario['role'] ?? 'Sin rol' }}
                        </span>
                    </div>
                </div>
                <div class="info-box bg-warning">
                    <div class="info-box-content">
                        <span class="info-box-text">Tipo de Usuario</span>
                        <span class="info-box-number">
                            {{ $usuario['cod_tipusers'] ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        border-radius: 0.5rem;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .info-box .info-box-number {
        font-size: 1.25rem;
        font-weight: 600;
    }
</style>
@endpush