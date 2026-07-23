@extends('adminlte::page')

@section('title', 'Detalle de Asiento Contable')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Asiento Contable #{{ $asiento['numero_asiento'] ?? $asiento['id_asiento'] }}</h1>
        <div>
            <a href="{{ route('asientos-contables.index') }}" class="btn btn-secondary">Volver</a>
            <a href="{{ route('asientos-contables.edit', $asiento['id_asiento'] ?? $asiento['id']) }}" class="btn btn-info">Editar</a>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Información del Asiento Contable</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-4">
                    <strong>Fecha:</strong> {{ $asiento['fecha'] }}
                </div>
                <div class="col-sm-4">
                    <strong>Estado:</strong> 
                    <span class="badge badge-{{ ($asiento['estado'] ?? '') == 'Mayorizado' ? 'success' : 'warning' }}">
                        {{ $asiento['estado'] ?? 'Borrador' }}
                    </span>
                </div>
                <div class="col-sm-4">
                    <strong>Glosa:</strong> {{ $asiento['glosa'] ?? $asiento['concepto'] }}
                </div>
            </div>

            <table class="table table-bordered table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th class="text-right">Debe</th>
                        <th class="text-right">Haber</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($asiento['detalles'] ?? [] as $detalle)
                        <tr>
                            <td>{{ $detalle['cuenta']['codigo'] ?? '-' }}</td>
                            <td>{{ $detalle['cuenta']['nombre'] ?? $detalle['nombre_cuenta'] ?? 'Cuenta' }}</td>
                            <td class="text-right">{{ number_format($detalle['debe'], 2) }}</td>
                            <td class="text-right">{{ number_format($detalle['haber'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="font-weight-bold">
                    <tr>
                        <td colspan="2" class="text-right">Total:</td>
                        <td class="text-right">{{ number_format($asiento['total_debe'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($asiento['total_haber'] ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@stop