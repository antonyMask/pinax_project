<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MayorizacionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\ReporteFinancieroController;
use App\Http\Middleware\EnsurePinaxAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
|
| Estas rutas pueden visitarse sin tener una sesión activa en Pinax.
|
*/

// Muestra el formulario de inicio de sesión.
Route::get(
    '/login',
    [AuthController::class, 'showLogin']
)
    ->name('login');

// Envía las credenciales ingresadas hacia la API de Pinax.
Route::post(
    '/login',
    [AuthController::class, 'login']
)
    ->name('login.authenticate');

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
|
| Todas las rutas agrupadas aquí requieren una sesión válida.
| EnsurePinaxAuthenticated impedirá el acceso a usuarios no autenticados.
|
*/

Route::middleware(EnsurePinaxAuthenticated::class)->group(function () {
    /*
     * La página raíz no posee una vista propia.
     *
     * Cuando un usuario visita "/", Laravel lo redirige al dashboard.
     */
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Muestra el panel principal de Pinax.
    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )
        ->name('dashboard');

    /*
     * Cierra la sesión del usuario.
     *
     * Utilizamos POST porque esta operación modifica el estado
     * de la sesión y no debe ejecutarse mediante una URL GET.
     */
    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Módulo de Personas
    |--------------------------------------------------------------------------
    */

    // Muestra el listado y los filtros de personas.
    Route::get(
        '/personas',
        [PersonaController::class, 'index']
    )
        ->name('personas.index');

    // Muestra el formulario para registrar una persona.
    Route::get(
        '/personas/crear',
        [PersonaController::class, 'create']
    )
        ->name('personas.create');

    // Envía los datos de una nueva persona a la API.
    Route::post(
        '/personas',
        [PersonaController::class, 'store']
    )
        ->name('personas.store');

    /*
     * Muestra el formulario para editar una persona.
     *
     * whereNumber evita que Laravel acepte valores no numéricos
     * como identificadores.
     */
    Route::get(
        '/personas/{codPeople}/editar',
        [PersonaController::class, 'edit']
    )
        ->whereNumber('codPeople')
        ->name('personas.edit');

    // Actualiza los datos principales de una persona.
    Route::put(
        '/personas/{codPeople}',
        [PersonaController::class, 'update']
    )
        ->whereNumber('codPeople')
        ->name('personas.update');

    /*
     * Cambia únicamente el estado lógico de una persona.
     *
     * Esta ruta pertenece al módulo existente y se conserva para
     * no alterar su funcionamiento actual.
     */
    Route::patch(
        '/personas/{codPeople}/estado',
        [PersonaController::class, 'toggleStatus']
    )
        ->whereNumber('codPeople')
        ->name('personas.toggle-status');

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

    /*
    |--------------------------------------------------------------------------
    | Cuentas T y Mayorización
    |--------------------------------------------------------------------------
    */

    // Consulta el resumen y sus filtros.
    Route::get('/mayorizacion', [MayorizacionController::class, 'index'])
        ->name('mayorizacion.index');

    // Genera la primera mayorización de una cuenta en un período.
    Route::post('/mayorizacion', [MayorizacionController::class, 'store'])
        ->name('mayorizacion.store');

    // Muestra el detalle de una Cuenta T.
    Route::get(
        '/mayorizacion/{cod_saldo}',
        [MayorizacionController::class, 'show']
    )
        ->where('cod_saldo', '[1-9][0-9]*')
        ->name('mayorizacion.show');

    // Recalcula, cierra o inactiva una mayorización existente.
    Route::put(
        '/mayorizacion/{cod_saldo}',
        [MayorizacionController::class, 'update']
    )
        ->whereNumber('cod_saldo')
        ->name('mayorizacion.update');

});