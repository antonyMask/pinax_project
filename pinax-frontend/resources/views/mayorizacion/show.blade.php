{{-- resources/views/mayorizacion/show.blade.php --}}

@extends('layouts.pinax')

{{-- Título mostrado en la pestaña del navegador. --}}
@section('title', 'Detalle de Cuenta T')

{{-- Reutilizamos la hoja de estilos propia del módulo. --}}
@section('css')
    <link
        rel="stylesheet"
        href="{{ asset('css/mayorizacion.css') }}"
    >
@stop

{{-- La vista utiliza un encabezado propio. --}}
@section('content_header')
@stop

@section('content')
    @php
        /*
         * Normalizamos los datos del resumen para que la plantilla trabaje
         * con valores predecibles y no repita conversiones.
         */
        $codSaldo = (int) data_get($resumen, 'cod_saldo', 0);

        $estadoSaldo = strtolower(
            (string) data_get(
                $resumen,
                'ind_estado',
                'desconocido'
            )
        );

        $estadoPeriodo = strtolower(
            (string) data_get(
                $resumen,
                'estado_periodo',
                'desconocido'
            )
        );

        /*
         * Las clases se seleccionan desde valores conocidos.
         *
         * Esto evita construir nombres CSS arbitrarios usando directamente
         * información recibida desde la API.
         */
        $claseEstado = match ($estadoSaldo) {
            'abierto' =>
                'mayor-status--open',

            'recalculado' =>
                'mayor-status--recalculated',

            'cerrado' =>
                'mayor-status--closed',

            'inactivo' =>
                'mayor-status--inactive',

            default =>
                'mayor-status--neutral',
        };

        $clasePeriodo = match ($estadoPeriodo) {
            'abierto' =>
                'mayor-period--open',

            'cerrado' =>
                'mayor-period--closed',

            default =>
                'mayor-period--neutral',
        };

        /*
         * Normalizamos cada movimiento para construir las dos columnas
         * tradicionales de una Cuenta T.
         *
         * Los nombres alternativos funcionan como defensa frente a pequeñas
         * diferencias de serialización entre MySQL y Node.js.
         */
        $movimientosNormalizados = collect($movimientos)
            ->map(function (mixed $movimiento): array {
                $tipo = strtolower(
                    trim(
                        (string) data_get(
                            $movimiento,
                            'tip_movimiento',
                            data_get(
                                $movimiento,
                                'tipo_movimiento',
                                data_get(
                                    $movimiento,
                                    'ind_tipo_movimiento',
                                    ''
                                )
                            )
                        )
                    )
                );

                /*
                 * Si la API devuelve columnas monetarias independientes,
                 * determinamos el lado según la columna que contiene valor.
                 */
                if (!in_array($tipo, ['debe', 'haber'], true)) {
                    $importeDebe = (float) data_get(
                        $movimiento,
                        'mon_debe',
                        data_get($movimiento, 'debe', 0)
                    );

                    $importeHaber = (float) data_get(
                        $movimiento,
                        'mon_haber',
                        data_get($movimiento, 'haber', 0)
                    );

                    if ($importeDebe > 0) {
                        $tipo = 'debe';
                    } elseif ($importeHaber > 0) {
                        $tipo = 'haber';
                    }
                }

                /*
                 * El monto principal se toma del contrato de la API.
                 * Los valores alternativos solo se utilizan como respaldo.
                 */
                $monto = data_get(
                    $movimiento,
                    'mon_movimiento',
                    data_get($movimiento, 'monto')
                );

                if ($monto === null) {
                    $monto = $tipo === 'debe'
                        ? data_get(
                            $movimiento,
                            'mon_debe',
                            data_get($movimiento, 'debe', 0)
                        )
                        : data_get(
                            $movimiento,
                            'mon_haber',
                            data_get($movimiento, 'haber', 0)
                        );
                }

                return [
                    'tipo' => $tipo,
                    'monto' => (float) $monto,

                    'cod_movimiento' => data_get(
                        $movimiento,
                        'cod_movimiento',
                        data_get($movimiento, 'cod_detalle')
                    ),

                    'cod_asiento' => data_get(
                        $movimiento,
                        'cod_asiento'
                    ),

                    'num_asiento' => data_get(
                        $movimiento,
                        'num_asiento',
                        data_get($movimiento, 'cod_asiento', '—')
                    ),

                    'fecha' => data_get(
                        $movimiento,
                        'fec_asiento',
                        data_get($movimiento, 'fec_movimiento', '—')
                    ),

                    'descripcion' => data_get(
                        $movimiento,
                        'des_asiento',
                        data_get(
                            $movimiento,
                            'des_movimiento',
                            'Movimiento contable'
                        )
                    ),
                ];
            })
            /*
             * Un movimiento desconocido no debe colocarse en una columna
             * incorrecta de la Cuenta T.
             */
            ->filter(
                fn (array $movimiento): bool =>
                    in_array(
                        $movimiento['tipo'],
                        ['debe', 'haber'],
                        true
                    )
            )
            ->values();

        // Separamos los movimientos según su lado contable.
        $movimientosDebe = $movimientosNormalizados
            ->where('tipo', 'debe')
            ->values();

        $movimientosHaber = $movimientosNormalizados
            ->where('tipo', 'haber')
            ->values();

        /*
         * Determina cuántas filas necesita la tabla.
         *
         * Ambos lados pueden contener cantidades diferentes de movimientos.
         */
        $cantidadFilas = max(
            $movimientosDebe->count(),
            $movimientosHaber->count()
        );

        // Los totales provienen del cálculo confiable realizado por la API.
        $totalDebe = (float) data_get(
            $resumen,
            'tot_debe',
            0
        );

        $totalHaber = (float) data_get(
            $resumen,
            'tot_haber',
            0
        );

        $saldoInicial = (float) data_get(
            $resumen,
            'sal_inicial',
            0
        );

        $saldoFinal = (float) data_get(
            $resumen,
            'sal_final',
            0
        );
    @endphp

    {{--
        Navegación de regreso.

        No utiliza JavaScript ni history.back(), por lo que siempre conduce
        de forma predecible al resumen del módulo.
    --}}
    <nav
        class="mayor-detail-nav"
        aria-label="Navegación del detalle"
    >
        <a
            href="{{ route('mayorizacion.index') }}"
            class="mayor-detail-back"
        >
            <i
                class="fas fa-arrow-left"
                aria-hidden="true"
            ></i>

            Volver al libro mayor
        </a>

        <span>
            Saldo #{{ $codSaldo }}
        </span>
    </nav>

    {{-- Encabezado principal de la Cuenta T consultada. --}}
    <section
        class="mayor-detail-hero"
        aria-labelledby="cuenta-t-title"
    >
        <div class="mayor-detail-hero__content">
            <span class="mayor-eyebrow">
                Detalle contable
            </span>

            <h1 id="cuenta-t-title">
                {{ data_get(
                    $resumen,
                    'cod_num_cuenta',
                    'Sin código'
                ) }}

                <span>
                    {{ data_get(
                        $resumen,
                        'nom_cuenta',
                        'Cuenta sin nombre'
                    ) }}
                </span>
            </h1>

            <p>
                Movimientos aprobados incluidos en la mayorización del período
                seleccionado.
            </p>

            <div class="mayor-detail-hero__badges">
                <span class="mayor-status {{ $claseEstado }}">
                    Saldo {{ ucfirst($estadoSaldo) }}
                </span>

                <span class="mayor-period {{ $clasePeriodo }}">
                    Período {{ ucfirst($estadoPeriodo) }}
                </span>

                <span class="mayor-detail-nature">
                    Naturaleza:
                    <strong>
                        {{ ucfirst(
                            (string) data_get(
                                $resumen,
                                'ind_naturaleza',
                                'no definida'
                            )
                        ) }}
                    </strong>
                </span>
            </div>
        </div>

        {{-- Representación decorativa de una Cuenta T. --}}
        <div
            class="mayor-detail-symbol"
            aria-hidden="true"
        >
            <span>Debe</span>
            <span>Haber</span>
        </div>
    </section>

    {{-- Información que identifica el período y el saldo consultado. --}}
    <section
        class="mayor-detail-metadata"
        aria-label="Información de la mayorización"
    >
        <article>
            <i
                class="fas fa-hashtag"
                aria-hidden="true"
            ></i>

            <div>
                <span>Código de saldo</span>
                <strong>#{{ $codSaldo }}</strong>
            </div>
        </article>

        <article>
            <i
                class="fas fa-calendar-alt"
                aria-hidden="true"
            ></i>

            <div>
                <span>Período contable</span>

                <strong>
                    {{ data_get(
                        $resumen,
                        'nom_periodo',
                        'Sin período'
                    ) }}
                </strong>
            </div>
        </article>

        <article>
            <i
                class="fas fa-calendar-day"
                aria-hidden="true"
            ></i>

            <div>
                <span>Rango del período</span>

                <strong>
                    {{ data_get($resumen, 'fec_inicio', '—') }}
                    a
                    {{ data_get($resumen, 'fec_fin', '—') }}
                </strong>
            </div>
        </article>

        <article>
            <i
                class="fas fa-clock"
                aria-hidden="true"
            ></i>

            <div>
                <span>Última actualización</span>

                <strong>
                    {{ data_get(
                        $resumen,
                        'fec_actualizacion',
                        '—'
                    ) }}
                </strong>
            </div>
        </article>
    </section>

    {{--
        Tarjetas monetarias.

        No se recalculan en Blade porque la autoridad contable es la API,
        respaldada por los procedimientos almacenados.
    --}}
    <section
        class="row mayor-detail-totals"
        aria-label="Totales de la Cuenta T"
    >
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="mayor-total-card mayor-total-card--initial">
                <span>Saldo inicial</span>

                <strong>
                    L {{ number_format($saldoInicial, 2) }}
                </strong>

                <small>
                    Importe anterior al período
                </small>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="mayor-total-card mayor-total-card--debit">
                <span>Total Debe</span>

                <strong>
                    L {{ number_format($totalDebe, 2) }}
                </strong>

                <small>
                    {{ $movimientosDebe->count() }}
                    {{ $movimientosDebe->count() === 1
                        ? 'movimiento'
                        : 'movimientos' }}
                </small>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="mayor-total-card mayor-total-card--credit">
                <span>Total Haber</span>

                <strong>
                    L {{ number_format($totalHaber, 2) }}
                </strong>

                <small>
                    {{ $movimientosHaber->count() }}
                    {{ $movimientosHaber->count() === 1
                        ? 'movimiento'
                        : 'movimientos' }}
                </small>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="mayor-total-card mayor-total-card--final">
                <span>Saldo final</span>

                <strong>
                    L {{ number_format($saldoFinal, 2) }}
                </strong>

                <small>
                    Resultado de la mayorización
                </small>
            </article>
        </div>
    </section>

    {{--
        Representación principal de la Cuenta T.

        Cada fila alinea visualmente un movimiento del Debe con uno del Haber.
        Esa alineación es gráfica; no significa que ambos movimientos tengan
        una relación directa entre sí.
    --}}
    <section
        class="mayor-account-sheet"
        aria-labelledby="movimientos-title"
    >
        <header class="mayor-account-sheet__header">
            <div>
                <span class="mayor-panel__kicker">
                    Libro mayor auxiliar
                </span>

                <h2 id="movimientos-title">
                    Movimientos de la Cuenta T
                </h2>
            </div>

            <span class="mayor-account-sheet__count">
                {{ $totalMovimientos }}
                {{ $totalMovimientos === 1
                    ? 'movimiento'
                    : 'movimientos' }}
            </span>
        </header>

        <div class="mayor-account-sheet__title">
            <span>
                Debe
            </span>

            <strong>
                {{ data_get(
                    $resumen,
                    'cod_num_cuenta',
                    'Cuenta'
                ) }}
            </strong>

            <span>
                Haber
            </span>
        </div>

        @if ($cantidadFilas > 0)
            <div class="table-responsive">
                <table class="table mayor-account-table">
                    <caption class="sr-only">
                        Movimientos del Debe y del Haber correspondientes
                        a la Cuenta T.
                    </caption>

                    <thead>
                        <tr>
                            <th>Asiento</th>
                            <th>Fecha y descripción</th>
                            <th class="text-right">Importe Debe</th>

                            <th class="mayor-account-table__divider">
                                Asiento
                            </th>

                            <th>Fecha y descripción</th>
                            <th class="text-right">Importe Haber</th>
                        </tr>
                    </thead>

                    <tbody>
                        @for ($indice = 0; $indice < $cantidadFilas; $indice++)
                            @php
                                /*
                                 * get() devuelve null cuando uno de los lados
                                 * tiene menos movimientos que el otro.
                                 */
                                $movimientoDebe = $movimientosDebe->get(
                                    $indice
                                );

                                $movimientoHaber = $movimientosHaber->get(
                                    $indice
                                );
                            @endphp

                            <tr>
                                {{-- Movimiento ubicado en el Debe. --}}
                                @if ($movimientoDebe)
                                    <td class="text-nowrap">
                                        <strong>
                                            #{{ $movimientoDebe['num_asiento'] }}
                                        </strong>
                                    </td>

                                    <td class="mayor-movement-description">
                                        <span>
                                            {{ $movimientoDebe['fecha'] }}
                                        </span>

                                        <strong>
                                            {{ $movimientoDebe['descripcion'] }}
                                        </strong>
                                    </td>

                                    <td
                                        class="
                                            text-right
                                            text-nowrap
                                            mayor-money
                                            mayor-money--debit
                                        "
                                    >
                                        L
                                        {{ number_format(
                                            $movimientoDebe['monto'],
                                            2
                                        ) }}
                                    </td>
                                @else
                                    <td colspan="3"></td>
                                @endif

                                {{-- Movimiento ubicado en el Haber. --}}
                                @if ($movimientoHaber)
                                    <td
                                        class="
                                            text-nowrap
                                            mayor-account-table__divider
                                        "
                                    >
                                        <strong>
                                            #{{ $movimientoHaber['num_asiento'] }}
                                        </strong>
                                    </td>

                                    <td class="mayor-movement-description">
                                        <span>
                                            {{ $movimientoHaber['fecha'] }}
                                        </span>

                                        <strong>
                                            {{ $movimientoHaber['descripcion'] }}
                                        </strong>
                                    </td>

                                    <td
                                        class="
                                            text-right
                                            text-nowrap
                                            mayor-money
                                            mayor-money--credit
                                        "
                                    >
                                        L
                                        {{ number_format(
                                            $movimientoHaber['monto'],
                                            2
                                        ) }}
                                    </td>
                                @else
                                    <td
                                        colspan="3"
                                        class="mayor-account-table__divider"
                                    ></td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="2">
                                Total Debe
                            </th>

                            <th class="text-right text-nowrap">
                                L {{ number_format($totalDebe, 2) }}
                            </th>

                            <th
                                colspan="2"
                                class="mayor-account-table__divider"
                            >
                                Total Haber
                            </th>

                            <th class="text-right text-nowrap">
                                L {{ number_format($totalHaber, 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            {{-- Estado vacío cuando no existen asientos aprobados asociados. --}}
            <div class="mayor-account-empty">
                <i
                    class="fas fa-receipt"
                    aria-hidden="true"
                ></i>

                <strong>
                    Esta Cuenta T no contiene movimientos
                </strong>

                <span>
                    El saldo puede provenir de un valor inicial o no tener
                    asientos aprobados dentro del período.
                </span>
            </div>
        @endif

        {{-- Ecuación de cierre mostrada como apoyo a la lectura contable. --}}
        <footer class="mayor-balance-result">
            <div>
                <span>
                    Saldo inicial
                </span>

                <strong>
                    L {{ number_format($saldoInicial, 2) }}
                </strong>
            </div>

            <i
                class="fas fa-plus"
                aria-hidden="true"
            ></i>

            <div>
                <span>
                    Movimiento neto
                </span>

                <strong>
                    L
                    {{ number_format(
                        $saldoFinal - $saldoInicial,
                        2
                    ) }}
                </strong>
            </div>

            <i
                class="fas fa-equals"
                aria-hidden="true"
            ></i>

            <div class="mayor-balance-result__final">
                <span>
                    Saldo final
                </span>

                <strong>
                    L {{ number_format($saldoFinal, 2) }}
                </strong>
            </div>
        </footer>
    </section>

    {{-- Nota que explica la procedencia de la información presentada. --}}
    <aside class="mayor-audit-note">
        <i
            class="fas fa-shield-alt"
            aria-hidden="true"
        ></i>

        <div>
            <strong>
                Información protegida
            </strong>

            <span>
                Esta vista es únicamente de consulta. Los importes provienen de
                los asientos aprobados y fueron calculados por la API Pinax.
            </span>
        </div>
    </aside>
@stop