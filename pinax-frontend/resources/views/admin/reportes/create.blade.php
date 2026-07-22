@extends('layouts.pinax')

@section('title', 'Nuevo Reporte')
@section('header', 'Generar Nuevo Reporte')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-invoice"></i> Generar Reporte Financiero
        </h3>
    </div>
    <form action="{{ route('reportes.store') }}" method="POST">
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
                        <label for="cod_periodo">Período Contable *</label>
                        <input type="number" 
                               class="form-control @error('cod_periodo') is-invalid @enderror" 
                               id="cod_periodo" 
                               name="cod_periodo" 
                               placeholder="Ej: 202401" 
                               value="{{ old('cod_periodo') }}" 
                               required>
                        @error('cod_periodo')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Código del período contable (ej: 202401 para enero 2024).</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tip_reporte">Tipo de Reporte *</label>
                        <select class="form-control @error('tip_reporte') is-invalid @enderror" 
                                id="tip_reporte" 
                                name="tip_reporte" 
                                required>
                            <option value="">Selecciona un tipo...</option>
                            <option value="balance_general" {{ old('tip_reporte') == 'balance_general' ? 'selected' : '' }}>
                                Balance General
                            </option>
                            <option value="estado_resultados" {{ old('tip_reporte') == 'estado_resultados' ? 'selected' : '' }}>
                                Estado de Resultados
                            </option>
                        </select>
                        @error('tip_reporte')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="calcular_automaticamente">Cálculo Automático</label>
                        <select class="form-control @error('calcular_automaticamente') is-invalid @enderror" 
                                id="calcular_automaticamente" 
                                name="calcular_automaticamente" 
                                required>
                            <option value="1" {{ old('calcular_automaticamente') != '0' ? 'selected' : '' }}>
                                Sí (calcular automáticamente)
                            </option>
                            <option value="0" {{ old('calcular_automaticamente') == '0' ? 'selected' : '' }}>
                                No (ingresar manualmente)
                            </option>
                        </select>
                        @error('calcular_automaticamente')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6" id="camposManuales" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Ingreso manual:</strong> Completa los siguientes campos.
                    </div>
                </div>
            </div>

            <!-- Campos manuales (ocultos por defecto) -->
            <div id="camposManualesContainer" style="display: none;">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tot_activo">Total Activo</label>
                            <input type="number" 
                                   step="0.01" 
                                   class="form-control @error('tot_activo') is-invalid @enderror" 
                                   id="tot_activo" 
                                   name="tot_activo" 
                                   placeholder="0.00" 
                                   value="{{ old('tot_activo') }}">
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
                                   placeholder="0.00" 
                                   value="{{ old('tot_pasivo') }}">
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
                                   placeholder="0.00" 
                                   value="{{ old('tot_patrimonio') }}">
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
                                   placeholder="0.00" 
                                   value="{{ old('mon_utilidad_perdida') }}">
                            @error('mon_utilidad_perdida')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Nota:</strong> Los reportes generados serán verificados automáticamente para garantizar la integridad contable.
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Generar Reporte
            </button>
            <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calcularAuto = document.getElementById('calcular_automaticamente');
        const camposManuales = document.getElementById('camposManualesContainer');

        calcularAuto.addEventListener('change', function() {
            if (this.value === '0') {
                camposManuales.style.display = 'block';
            } else {
                camposManuales.style.display = 'none';
            }
        });

        // Verificar si debe mostrar campos manuales al cargar
        if (calcularAuto.value === '0') {
            camposManuales.style.display = 'block';
        }
    });
</script>
@endpush