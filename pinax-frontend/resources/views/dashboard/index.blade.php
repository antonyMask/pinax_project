{{-- resources/views/dashboard/index.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Dashboard')

{{-- No usamos el encabezado tradicional para aprovechar nuestro hero personalizado. --}}
@section('content_header')
@stop

@section('content')
    {{-- Encabezado distintivo inspirado en el logo de Pinax. --}}
    <section class="pinax-hero">
        <div class="pinax-hero-content">
            <span class="pinax-kicker">
                Centro de control contable
            </span>

            <h1>
                Precisión para cada
                <span>decisión financiera.</span>
            </h1>

            <p>
                Supervisa la operación de Pinax desde una vista clara,
                agradable y funcional.
            </p>

            <div class="pinax-hero-status">
                @if (empty($advertencias))
                    <span class="pinax-status-dot"></span>
                    Integridad en Nuestros Datos
                @else
                    <span class="pinax-status-dot pinax-status-warning"></span>
                    Información disponible parcialmente
                @endif

                <span class="pinax-status-divider"></span>

                {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <div class="pinax-hero-logo">
            <img
                src="{{ asset('images/pinax-logo.png') }}"
                alt="Logo de Pinax"
            >
        </div>
    </section>

    {{-- Advertencias de recursos que no pudieron consultarse. --}}
    @if (!empty($advertencias))
        <div class="alert alert-warning pinax-alert">
            <h5>
                <i class="fas fa-exclamation-triangle"></i>
                Algunos datos no están disponibles
            </h5>

            <ul class="mb-0">
                @foreach ($advertencias as $advertencia)
                    <li>{{ $advertencia }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Métricas principales obtenidas de la API. --}}
    <div class="row pinax-metrics">
        <div class="col-lg-3 col-md-6">
            <article class="pinax-metric-card">
                <div class="pinax-metric-icon pinax-icon-blue">
                    <i class="fas fa-users"></i>
                </div>

                <div>
                    <span class="pinax-metric-label">Personas registradas</span>
                    <strong>{{ number_format($metricas['total_personas']) }}</strong>
                    <small>
                        {{ number_format($metricas['personas_activas']) }}
                        activas
                    </small>
                </div>
            </article>
        </div>

        <div class="col-lg-3 col-md-6">
            <article class="pinax-metric-card">
                <div class="pinax-metric-icon pinax-icon-cyan">
                    <i class="fas fa-sitemap"></i>
                </div>

                <div>
                    <span class="pinax-metric-label">Cuentas contables</span>
                    <strong>{{ number_format($metricas['total_cuentas']) }}</strong>
                    <small>
                        {{ number_format($metricas['cuentas_activas']) }}
                        activas
                    </small>
                </div>
            </article>
        </div>

        <div class="col-lg-3 col-md-6">
            <article class="pinax-metric-card">
                <div class="pinax-metric-icon pinax-icon-magenta">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>

                <div>
                    <span class="pinax-metric-label">Asientos registrados</span>
                    <strong>{{ number_format($metricas['total_asientos']) }}</strong>
                    <small>Movimientos contables</small>
                </div>
            </article>
        </div>

        <div class="col-lg-3 col-md-6">
            <article class="pinax-metric-card">
                <div class="pinax-metric-icon pinax-icon-violet">
                    <i class="fas fa-check-double"></i>
                </div>

                <div>
                    <span class="pinax-metric-label">Asientos aprobados</span>
                    <strong>{{ number_format($metricas['asientos_aprobados']) }}</strong>
                    <small>Operaciones confirmadas</small>
                </div>
            </article>
        </div>
    </div>

    <div class="row">
        {{-- Actividad contable reciente. --}}
        <div class="col-lg-8">
            <section class="pinax-panel">
                <div class="pinax-panel-header">
                    <div>
                        <span class="pinax-panel-kicker">Actividad</span>
                        <h2>Asientos recientes</h2>
                    </div>

                    <i class="fas fa-wave-square"></i>
                </div>

                <div class="table-responsive">
                    <table class="table pinax-table">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($asientosRecientes as $asiento)
                                @php
                                    $estado = strtolower(
                                        (string) data_get($asiento, 'ind_estado')
                                    );
                                @endphp

                                <tr>
                                    <td>
                                        <strong>
                                            {{ data_get($asiento, 'num_asiento', 'Sin número') }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ data_get($asiento, 'fec_asiento', 'Sin fecha') }}
                                    </td>

                                    <td>
                                        <span class="text-capitalize">
                                            {{ data_get($asiento, 'tip_asiento', 'No definido') }}
                                        </span>
                                    </td>

                                    <td>
                                        L
                                        {{ number_format(
                                            (float) data_get($asiento, 'tot_debe', 0),
                                            2
                                        ) }}
                                    </td>

                                    <td>
                                        @if ($estado === 'aprobado')
                                            <span class="badge badge-success">
                                                Aprobado
                                            </span>
                                        @elseif ($estado === 'borrador')
                                            <span class="badge badge-warning">
                                                Borrador
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                {{ ucfirst($estado ?: 'No definido') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="pinax-empty-state">
                                        <i class="fas fa-receipt"></i>

                                        <strong>No hay asientos para mostrar</strong>

                                        <span>
                                            Los movimientos contables aparecerán aquí.
                                        </span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            {{-- Indicador de estado del módulo Personas. --}}
            <section class="pinax-panel pinax-health-panel">
                <div class="pinax-panel-header">
                    <div>
                        <span class="pinax-panel-kicker">Estado operativo</span>
                        <h2>Personas activas</h2>
                    </div>
                </div>

                <div class="pinax-health-value">
                    {{ $porcentajePersonasActivas }}%
                </div>

                <div class="pinax-progress">
                    <div
                        class="pinax-progress-value"
                        style="--pinax-progress: {{ $porcentajePersonasActivas }}%;"
                    ></div>
                </div>

                <p>
                    {{ $metricas['personas_activas'] }}
                    de
                    {{ $metricas['total_personas'] }}
                    personas se encuentran activas.
                </p>
            </section>

            {{-- Acciones disponibles en el módulo ya construido. --}}
            <section class="pinax-panel pinax-actions-panel">
                <div class="pinax-panel-header">
                    <div>
                        <span class="pinax-panel-kicker">Accesos rápidos</span>
                        <h2>Personas</h2>
                    </div>
                </div>

                <a
                    href="{{ route('personas.index') }}"
                    class="pinax-action-link"
                >
                    <span>
                        <i class="fas fa-address-book"></i>
                        Gestionar personas
                    </span>

                    <i class="fas fa-arrow-right"></i>
                </a>

                <a
                    href="{{ route('personas.create') }}"
                    class="pinax-action-link"
                >
                    <span>
                        <i class="fas fa-user-plus"></i>
                        Registrar persona
                    </span>

                    <i class="fas fa-arrow-right"></i>
                </a>
            </section>
        </div>
    </div>
@stop

@section('footer')
    <strong>Pinax</strong>
    <span class="text-muted">
        · Precision · Integration · Opportunity
    </span>

    <span class="float-right d-none d-sm-inline">
        © 2026 | TODOS LOS DERECHOS RESERVADOS AL GRUPO PINAX
    </span>
@stop