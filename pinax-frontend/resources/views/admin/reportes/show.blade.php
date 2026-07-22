@extends('layouts.pinax')

@section('title', 'Detalle de Reporte')
@section('header', 'Detalle de Reporte')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

@if(!empty($reporte) && isset($reporte['cod_reporte']))
<div class="row">
    <div class="col-md-8">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> 
                    Reporte #{{ $reporte['cod_reporte'] ?? '' }}
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-tag"></i> Tipo:</strong>
                        <p>
                            <span class="badge 
                                @if(($reporte['tip_reporte'] ?? '') == 'balance_general') bg-info
                                @elseif(($reporte['tip_reporte'] ?? '') == 'estado_resultados') bg-success
                                @else bg-secondary
                                @endif" style="font-size: 14px; padding: 8px 16px;">
                                {{ str_replace('_', ' ', ucfirst($reporte['tip_reporte'] ?? '-')) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar"></i> Período:</strong>
                        <p>{{ $reporte['cod_periodo'] ?? '-' }} 
                           @if(isset($reporte['nom_periodo']))
                               <small class="text-muted">({{ $reporte['nom_periodo'] }})</small>
                           @endif
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-clock"></i> Fecha Generación:</strong>
                        <p>{{ isset($reporte['fec_generacion']) ? \Carbon\Carbon::parse($reporte['fec_generacion'])->format('d/m/Y H:i') : '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-user"></i> Generado por:</strong>
                        <p>{{ $reporte['nom_usuario'] ?? $reporte['cod_user'] ?? '-' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-check-circle"></i> Estado:</strong>
                        <p>
                            <span class="badge 
                                @if(($reporte['ind_estado'] ?? '') == 'generado') bg-warning
                                @elseif(($reporte['ind_estado'] ?? '') == 'confirmado') bg-success
                                @elseif(($reporte['ind_estado'] ?? '') == 'anulado') bg-danger
                                @else bg-secondary
                                @endif" style="font-size: 14px; padding: 8px 16px;">
                                {{ ucfirst($reporte['ind_estado'] ?? 'generado') }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-balance-scale"></i> Validación:</strong>
                        <p>
                            @if(isset($reporte['estado_validacion']))
                                @if($reporte['estado_validacion'] == 'balance cuadrado')
                                    <span class="badge bg-success" style="font-size: 14px; padding: 8px 16px;">
                                        <i class="fas fa-check"></i> Balance Cuadrado
                                    </span>
                                @elseif($reporte['estado_validacion'] == 'balance descuadrado')
                                    <span class="badge bg-danger" style="font-size: 14px; padding: 8px 16px;">
                                        <i class="fas fa-times"></i> Balance Descuadrado
                                    </span>
                                @elseif($reporte['estado_validacion'] == 'utilidad')
                                    <span class="badge bg-success" style="font-size: 14px; padding: 8px 16px;">
                                        <i class="fas fa-arrow-up"></i> Utilidad
                                    </span>
                                @else
                                    <span class="badge bg-warning">{{ $reporte['estado_validacion'] }}</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">No verificado</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <h5><i class="fas fa-chart-pie"></i> Totales</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Total Activo</span>
                                <span class="info-box-number">
                                    L {{ number_format($reporte['tot_activo'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Total Pasivo</span>
                                <span class="info-box-number">
                                    L {{ number_format($reporte['tot_pasivo'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Total Patrimonio</span>
                                <span class="info-box-number">
                                    L {{ number_format($reporte['tot_patrimonio'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Utilidad/Pérdida</span>
                                <span class="info-box-number 
                                    @if(($reporte['mon_utilidad_perdida'] ?? 0) >= 0) text-success @else text-danger @endif">
                                    L {{ number_format($reporte['mon_utilidad_perdida'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                @if(($reporte['ind_estado'] ?? '') !== 'anulado')
                <a href="{{ route('reportes.edit', $reporte['cod_reporte']) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                @endif
                <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
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
                <div class="info-box bg-{{ ($reporte['ind_estado'] ?? '') == 'anulado' ? 'danger' : 'success' }}">
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number">
                            {{ ucfirst($reporte['ind_estado'] ?? 'generado') }}
                        </span>
                    </div>
                </div>
                <div class="info-box bg-info">
                    <div class="info-box-content">
                        <span class="info-box-text">Tipo</span>
                        <span class="info-box-number">
                            {{ str_replace('_', ' ', ucfirst($reporte['tip_reporte'] ?? '-')) }}
                        </span>
                    </div>
                </div>
                <div class="info-box bg-warning">
                    <div class="info-box-content">
                        <span class="info-box-text">Período</span>
                        <span class="info-box-number">{{ $reporte['cod_periodo'] ?? '-' }}</span>
                    </div>
                </div>
                @if(isset($detalle['total_lineas']) && $detalle['total_lineas'] > 0)
                <div class="info-box bg-secondary">
                    <div class="info-box-content">
                        <span class="info-box-text">Líneas de Detalle</span>
                        <span class="info-box-number">{{ $detalle['total_lineas'] ?? 0 }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-danger text-center py-4">
    <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
    <p class="mb-0">No se encontró el reporte solicitado.</p>
    <a href="{{ route('reportes.index') }}" class="btn btn-primary btn-sm mt-2">
        <i class="fas fa-arrow-left"></i> Volver a Reportes
    </a>
</div>
@endif
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
    .text-success {
        color: #28a745 !important;
    }
    .text-danger {
        color: #dc3545 !important;
    }
    .badge {
        font-weight: 500;
    }
</style>
@endpush