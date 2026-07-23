@extends('adminlte::page')

@section('title', 'Asientos Contables')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Asientos Contables</h1>
        <a href="{{ route('asientos-contables.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Asiento
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-body p-0">
            <table class="table table-striped table-hover m-0">
                <thead>
                    <tr>
                        <th>N° Asiento</th>
                        <th>Fecha</th>
                        <th>Glosa / Concepto</th>
                        <th class="text-right">Total Debe</th>
                        <th class="text-right">Total Haber</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asientos as $asiento)
                        <tr>
                            <td><strong>#{{ $asiento['numero_asiento'] ?? $asiento['id_asiento'] ?? $asiento['id'] }}</strong></td>
                            <td>{{ $asiento['fecha'] ?? '-' }}</td>
                            <td>{{ $asiento['glosa'] ?? $asiento['concepto'] ?? '-' }}</td>
                            <td class="text-right">{{ number_format($asiento['total_debe'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($asiento['total_haber'] ?? 0, 2) }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ ($asiento['estado'] ?? '') == 'Mayorizado' ? 'success' : 'warning' }}">
                                    {{ $asiento['estado'] ?? 'Borrador' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('asientos-contables.show', $asiento['id_asiento'] ?? $asiento['id']) }}" 
                                   class="btn btn-sm btn-secondary" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('asientos-contables.edit', $asiento['id_asiento'] ?? $asiento['id']) }}" 
                                   class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('asientos-contables.destroy', $asiento['id_asiento'] ?? $asiento['id']) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Está seguro de anular/eliminar este asiento?')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No hay asientos contables registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop