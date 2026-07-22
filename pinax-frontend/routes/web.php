<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ReporteFinancieroController;
use App\Http\Middleware\EnsurePinaxAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

// Formulario de acceso.
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

// Envía las credenciales a la API.
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.authenticate');

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/

Route::middleware(EnsurePinaxAuthenticated::class)->group(function () {
    // La raíz dirige al dashboard.
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard principal.
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Cierre seguro de sesión.
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Personas
    |--------------------------------------------------------------------------
    */

    Route::get('/personas', [PersonaController::class, 'index'])
        ->name('personas.index');

    Route::get('/personas/crear', [PersonaController::class, 'create'])
        ->name('personas.create');

    Route::post('/personas', [PersonaController::class, 'store'])
        ->name('personas.store');

    Route::get(
        '/personas/{codPeople}/editar',
        [PersonaController::class, 'edit']
    )
        ->whereNumber('codPeople')
        ->name('personas.edit');

    Route::put(
        '/personas/{codPeople}',
        [PersonaController::class, 'update']
    )
        ->whereNumber('codPeople')
        ->name('personas.update');

    Route::patch(
        '/personas/{codPeople}/estado',
        [PersonaController::class, 'toggleStatus']
    )
        ->whereNumber('codPeople')
        ->name('personas.toggle-status');

    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */

    // Listar usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])
        ->name('usuarios.index');

    // Formulario para crear usuario
    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])
        ->name('usuarios.create');

    // Guardar nuevo usuario
    Route::post('/usuarios', [UsuarioController::class, 'store'])
        ->name('usuarios.store');

    // Ver detalle de un usuario
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])
        ->whereNumber('id')
        ->name('usuarios.show');

    // Formulario para editar usuario
    Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])
        ->whereNumber('id')
        ->name('usuarios.edit');

    // Actualizar usuario
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])
        ->whereNumber('id')
        ->name('usuarios.update');

    // Cambiar estado del usuario (activar/desactivar)
    Route::patch('/usuarios/{id}/estado', [UsuarioController::class, 'toggleStatus'])
        ->whereNumber('id')
        ->name('usuarios.toggle-status');

    /*
    |--------------------------------------------------------------------------
    | Reportes Financieros
    |--------------------------------------------------------------------------
    */

    // Listar reportes
    Route::get('/reportes', [ReporteFinancieroController::class, 'index'])
        ->name('reportes.index');

    // Formulario para crear reporte
    Route::get('/reportes/crear', [ReporteFinancieroController::class, 'create'])
        ->name('reportes.create');

    // Guardar nuevo reporte
    Route::post('/reportes', [ReporteFinancieroController::class, 'store'])
        ->name('reportes.store');

    // Ver detalle de un reporte
    Route::get('/reportes/{id}', [ReporteFinancieroController::class, 'show'])
        ->whereNumber('id')
        ->name('reportes.show');

    // Formulario para editar reporte
    Route::get('/reportes/{id}/editar', [ReporteFinancieroController::class, 'edit'])
        ->whereNumber('id')
        ->name('reportes.edit');

    // Actualizar reporte
    Route::put('/reportes/{id}', [ReporteFinancieroController::class, 'update'])
        ->whereNumber('id')
        ->name('reportes.update');

    // Anular reporte (soft delete)
    Route::delete('/reportes/{id}', [ReporteFinancieroController::class, 'destroy'])
        ->whereNumber('id')
        ->name('reportes.destroy');

    
});