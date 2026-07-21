@extends('layouts.pinax')

@section('title', 'Editar Reporte')
@section('header', 'Editar Reporte')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-edit"></i> 
            Editar Reporte #{{ $reporte['cod_reporte'] ?? '' }}
        </h3>
    </div>
    <form action="{{ route('reportes.update', $reporte['cod_reporte']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ind_estado">Estado del Reporte</label>
                        <select class="form-control @error('ind_estado') is-invalid @enderror" 
                                id="ind_estado" 
                                name="ind_estado">
                            <option value="generado" {{ ($reporte['ind_estado'] ?? '') == 'generado' ? 'selected' : '' }}>
                                Generado
                            </option>
                            <option value="confirmado" {{ ($reporte['ind_estado'] ?? '') == 'confirmado' ? 'selected' : '' }}>
                                Confirmado
                            </option>
                            <option value="anulado" {{ ($reporte['ind_estado'] ?? '') == 'anulado' ? 'selected' : '' }}>
                                Anulado
                            </option>
                        </select>
                        @error('ind_estado')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tipo de Reporte</label>
                        <p class="form-control-static">
                            <span class="badge 
                                @if(($reporte['tip_reporte'] ?? '') == 'balance_general') bg-info
                                @elseif(($reporte['tip_reporte'] ?? '') == 'estado_resultados') bg-success
                                @else bg-secondary
                                @endif" style="font-size: 14px; padding: 8px 16px;">
                                {{ str_replace('_', ' ', ucfirst($reporte['tip_reporte'] ?? '-')) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tot_activo">Total Activo</label>
                        <input type="number" 
                               step="0.01" 
                               class="form-control @error('tot_activo') is-invalid @enderror" 
                               id="tot_activo" 
                               name="tot_activo" 
                               value="{{ old('tot_activo', $reporte['tot_activo'] ?? 0) }}">
                        @error('tot_activo')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tot_pasivo">Total Pasivo</label>
                        <input type="number" 
                               step="0.01" 
                               class="form-control @error('tot_pasivo') is-invalid @enderror" 
                               id="tot_pasivo" 
                               name="tot_pasivo" 
                               value="{{ old('tot_pasivo', $reporte['tot_pasivo'] ?? 0) }}">
                        @error('tot_pasivo')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tot_patrimonio">Total Patrimonio</label>
                        <input type="number" 
                               step="0.01" 
                               class="form-control @error('tot_patrimonio') is-invalid @enderror" 
                               id="tot_patrimonio" 
                               name="tot_patrimonio" 
                               value="{{ old('tot_patrimonio', $reporte['tot_patrimonio'] ?? 0) }}">
                        @error('tot_patrimonio')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="mon_utilidad_perdida">Utilidad/Pérdida</label>
                        <input type="number" 
                               step="0.01" 
                               class="form-control @error('mon_utilidad_perdida') is-invalid @enderror" 
                               id="mon_utilidad_perdida" 
                               name="mon_utilidad_perdida" 
                               value="{{ old('mon_utilidad_perdida', $reporte['mon_utilidad_perdida'] ?? 0) }}">
                        @error('mon_utilidad_perdida')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i>
                <strong>Información adicional:</strong>
                <ul class="mb-0 mt-1">
                    <li>Período: <strong>{{ $reporte['cod_periodo'] ?? 'N/A' }}</strong></li>
                    <li>Fecha de generación: <strong>{{ \Carbon\Carbon::parse($reporte['fec_generacion'] ?? now())->format('d/m/Y H:i') }}</strong></li>
                    <li>Estado actual: <strong>{{ ucfirst($reporte['ind_estado'] ?? 'generado') }}</strong></li>
                </ul>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Actualizar Reporte
            </button>
            <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection