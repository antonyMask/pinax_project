@extends('layouts.pinax')

@section('title', 'Reportes Financieros')
@section('header', 'Reportes Financieros')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reportes</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Lista de Reportes Financieros</h3>
        <a href="{{ route('reportes.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nuevo Reporte
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if(isset($error))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ $error }}
            </div>
        @endif

        @if(isset($reportes['data']) && count($reportes['data']) > 0)
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Código</th>
                        <th>Período</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Validación</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportes['data'] as $reporte)
                    <tr>
                        <td>{{ $reporte['cod_reporte'] ?? '-' }}</td>
                        <td>
                            {{ $reporte['cod_periodo'] ?? '-' }}
                            @if(isset($reporte['nom_periodo']))
                                <small class="text-muted">({{ $reporte['nom_periodo'] }})</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge 
                                @if(($reporte['tip_reporte'] ?? '') == 'balance_general') bg-info
                                @elseif(($reporte['tip_reporte'] ?? '') == 'estado_resultados') bg-success
                                @else bg-secondary
                                @endif">
                                {{ str_replace('_', ' ', ucfirst($reporte['tip_reporte'] ?? '-')) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge 
                                @if(($reporte['ind_estado'] ?? '') == 'generado') bg-warning
                                @elseif(($reporte['ind_estado'] ?? '') == 'confirmado') bg-success
                                @elseif(($reporte['ind_estado'] ?? '') == 'anulado') bg-danger
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($reporte['ind_estado'] ?? 'generado') }}
                            </span>
                        </td>
                        <td>
                            @if(isset($reporte['estado_validacion']))
                                @if($reporte['estado_validacion'] == 'balance cuadrado')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Cuadrado
                                    </span>
                                @elseif($reporte['estado_validacion'] == 'balance descuadrado')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times"></i> Descuadrado
                                    </span>
                                @elseif($reporte['estado_validacion'] == 'utilidad')
                                    <span class="badge bg-success">
                                        <i class="fas fa-arrow-up"></i> Utilidad
                                    </span>
                                @else
                                    <span class="badge bg-warning">{{ $reporte['estado_validacion'] }}</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($reporte['fec_generacion'] ?? now())->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('reportes.show', $reporte['cod_reporte']) }}" 
                                   class="btn btn-sm btn-outline-info" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(($reporte['ind_estado'] ?? '') !== 'anulado')
                                <a href="{{ route('reportes.edit', $reporte['cod_reporte']) }}" 
                                   class="btn btn-sm btn-outline-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('reportes.destroy', $reporte['cod_reporte']) }}" 
                                      method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('¿Estás seguro de anular este reporte?')"
                                            title="Anular">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tarjetas de resumen -->
        <div class="row mt-4">
            <div class="col-md-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $reportes['total_reportes'] ?? 0 }}</h3>
                        <p>Total Reportes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>
                            {{ collect($reportes['data'] ?? [])->where('ind_estado', 'generado')->count() }}
                        </h3>
                        <p>Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>
                            {{ collect($reportes['data'] ?? [])->where('ind_estado', 'confirmado')->count() }}
                        </h3>
                        <p>Confirmados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>
                            {{ collect($reportes['data'] ?? [])->where('ind_estado', 'anulado')->count() }}
                        </h3>
                        <p>Anulados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-info text-center py-4">
            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
            <p class="mb-0">No hay reportes disponibles.</p>
            <a href="{{ route('reportes.create') }}" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-plus"></i> Crear el primer reporte
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .small-box {
        border-radius: 0.5rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    .small-box .inner {
        padding: 10px;
    }
    .small-box .icon {
        font-size: 3rem;
        opacity: 0.5;
    }
    .btn-group .btn {
        margin: 0 1px;
    }
    .badge {
        font-weight: 500;
    }
</style>
@endpush