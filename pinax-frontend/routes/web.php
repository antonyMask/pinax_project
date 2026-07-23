<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonaController;
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
});

/*
|--------------------------------------------------------------------------
| ASIENTOS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AsientosContablesController;

Route::middleware(['auth.pinax'])->group(function () {

Route::resource('asientos-contables', AsientosContablesController::class);
});
