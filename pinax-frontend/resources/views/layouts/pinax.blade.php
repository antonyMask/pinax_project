{{-- 
    Layout privado general de Pinax.

    Todas las pantallas que requieren autenticación deben extender este
    archivo para compartir la barra del usuario y el botón de cerrar sesión.
--}}
@extends('adminlte::page')

@php
    /*
     * Recuperamos únicamente la información pública del usuario.
     * El token nunca se imprime en la página.
     */
    $usuarioPinax = session('pinax_user', []);

    /*
     * Usamos diferentes alternativas porque el nombre exacto puede
     * depender de cómo esté construida la respuesta de la API.
     */
    $nombreUsuario = data_get($usuarioPinax, 'firstname')
        ?? data_get($usuarioPinax, 'name')
        ?? data_get($usuarioPinax, 'NAME')
        ?? 'Usuario';

    $rolUsuario = data_get($usuarioPinax, 'role')
        ?? data_get($usuarioPinax, 'tipo_usuario')
        ?? data_get($usuarioPinax, 'TIP_USER')
        ?? 'Usuario';
@endphp

{{-- Elementos del lado derecho de la barra superior de AdminLTE. --}}
@section('content_top_nav_right')

    {{-- Información del usuario autenticado. --}}
    <li class="nav-item d-none d-md-flex align-items-center">
        <div class="pinax-navbar-user">
            <span class="pinax-navbar-avatar">
                <i class="fas fa-user"></i>
            </span>

            <span class="pinax-navbar-user-info">
                <strong>{{ $nombreUsuario }}</strong>
                <small>{{ $rolUsuario }}</small>
            </span>
        </div>
    </li>

    {{-- Formulario seguro para cerrar sesión. --}}
    <li class="nav-item d-flex align-items-center">
        <form
            action="{{ route('logout') }}"
            method="POST"
            class="pinax-logout-form"
        >
            @csrf

            <button
                type="submit"
                class="pinax-logout-button"
                title="Cerrar sesión"
                aria-label="Cerrar sesión"
            >
                <i class="fas fa-power-off"></i>
                <span class="d-none d-sm-inline">Salir</span>
            </button>
        </form>
    </li>

@stop