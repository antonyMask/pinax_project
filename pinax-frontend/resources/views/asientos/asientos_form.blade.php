@extends('adminlte::page')

@section('title', isset($asiento) ? 'Editar Asiento Contable' : 'Nuevo Asiento Contable')

@section('content_header')
    <h1>{{ isset($asiento) ? 'Editar Asiento Contable' : 'Nuevo Asiento Contable' }}</h1>
@stop

@section('content')
    <div class="card card-primary">
        <form action="{{ isset($asiento) ? route('asientos-contables.update', $asiento['id_asiento'] ?? $asiento['id']) : route('asientos-contables.store') }}" method="POST" id="asientoForm">
            @csrf
            @if(isset($asiento))
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="fecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" 
                               id="fecha" value="{{ old('fecha', $asiento['fecha'] ?? date('Y-m-d')) }}" required>
                        @error('fecha') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="numero_asiento">Número de Asiento</label>
                        <input type="text" name="numero_asiento" class="form-control" 
                               id="numero_asiento" value="{{ old('numero_asiento', $asiento['numero_asiento'] ?? '') }}" placeholder="Auto-generado">
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="glosa">Glosa / Concepto <span class="text-danger">*</span></label>
                        <input type="text" name="glosa" class="form-control @error('glosa') is-invalid @enderror" 
                               id="glosa" value="{{ old('glosa', $asiento['glosa'] ?? $asiento['concepto'] ?? '') }}" 
                               placeholder="Ej. Pago de servicios públicos del mes" required>
                        @error('glosa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="m-0 font-weight-bold">Detalle del Asiento</h5>
                    <button type="button" class="btn btn-sm btn-success" id="btnAgregarLinea">
                        <i class="fas fa-plus"></i> Agregar Fila
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaDetalles">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40%;">Cuenta Contable</th>
                                <th style="width: 25%;">Debe</th>
                                <th style="width: 25%;">Haber</th>
                                <th style="width: 10%;" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="detallesContainer">
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td class="text-right">Totales:</td>
                                <td><input type="text" class="form-control form-control-plaintext font-weight-bold" id="totalDebe" value="0.00" readonly></td>
                                <td><input type="text" class="form-control form-control-plaintext font-weight-bold" id="totalHaber" value="0.00" readonly></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-center" id="mensajeCuadre"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('asientos-contables.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar Asiento</button>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
    const catalogoCuentas = @json($cuentas ?? []);
    const detallesExistentes = @json($asiento['detalles'] ?? []);

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('detallesContainer');
        const btnAgregar = document.getElementById('btnAgregarLinea');

        function agregarFila(cuentaId = '', debe = 0, haber = 0) {
            const index = container.children.length;
            const tr = document.createElement('tr');

            let options = '<option value="">Seleccione una cuenta...</option>';
            catalogoCuentas.forEach(c => {
                const selected = (c.id == cuentaId || c.id_cuenta == cuentaId) ? 'selected' : '';
                options += `<option value="${c.id || c.id_cuenta}" ${selected}>${c.codigo ? c.codigo + ' - ' : ''}${c.nombre}</option>`;
            });

            tr.innerHTML = `
                <td>
                    <select name="detalles[${index}][id_cuenta]" class="form-control select-cuenta" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="detalles[${index}][debe]" class="form-control input-debe" value="${debe}">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="detalles[${index}][haber]" class="form-control input-haber" value="${haber}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar"><i class="fas fa-trash"></i></button>
                </td>
            `;

            container.appendChild(tr);
            calcularTotales();
        }

        btnAgregar.addEventListener('click', () => agregarFila());

        container.addEventListener('click', function (e) {
            if (e.target.closest('.btn-eliminar')) {
                e.target.closest('tr').remove();
                calcularTotales();
            }
        });

        container.addEventListener('input', function (e) {
            if (e.target.classList.contains('input-debe') || e.target.classList.contains('input-haber')) {
                calcularTotales();
            }
        });

        function calcularTotales() {
            let totalDebe = 0;
            let totalHaber = 0;

            document.querySelectorAll('.input-debe').forEach(i => totalDebe += parseFloat(i.value) || 0);
            document.querySelectorAll('.input-haber').forEach(i => totalHaber += parseFloat(i.value) || 0);

            document.getElementById('totalDebe').value = totalDebe.toFixed(2);
            document.getElementById('totalHaber').value = totalHaber.toFixed(2);

            const mensaje = document.getElementById('mensajeCuadre');
            const btnGuardar = document.getElementById('btnGuardar');

            if (totalDebe > 0 && Math.abs(totalDebe - totalHaber) < 0.001) {
                mensaje.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> El asiento está cuadrado.</span>';
                btnGuardar.disabled = false;
            } else {
                mensaje.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> El asiento descuadra por: ' + Math.abs(totalDebe - totalHaber).toFixed(2) + '</span>';
                btnGuardar.disabled = true;
            }
        }

        if (detallesExistentes.length > 0) {
            detallesExistentes.forEach(d => agregarFila(d.id_cuenta, d.debe, d.haber));
        } else {
            agregarFila();
            agregarFila();
        }
    });
</script>
@stop