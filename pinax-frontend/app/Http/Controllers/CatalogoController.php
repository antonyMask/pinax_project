<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCuentaRequest;
use App\Http\Requests\UpdateCuentaRequest;
use App\Services\PinaxApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CatalogoController extends Controller
{
    /*
    GET /catalogo
    Consulta el catalogo de cuentas exclusivamente por medio de la API Pinax.
    Permite filtrar por cod_tipo_cuenta o por cod_cuenta.
    */
    public function index(Request $request, PinaxApiService $pinaxApi): View
    {
        try {
            // Recibimos solamente los filtros aceptados por la API.
            $filtros = collect($request->only([
                'cod_tipo_cuenta',
                'cod_cuenta',
            ]))
                ->filter(fn ($valor) => filled($valor))
                ->all();

            // Laravel realiza una solicitud HTTP hacia GET /api/catalogo.
            $response = $pinaxApi->get('/catalogo', $filtros);

            if ($response->failed()) {
                Log::warning('La API Pinax devolvió un error al consultar el catálogo.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return view('catalogo.index', [
                    'cuentas' => [],
                    'errorApi' => $response->json('mensaje')
                        ?? 'No fue posible consultar el catálogo de cuentas en la API.',
                ]);
            }

            // La API responde con una estructura como: { estado, total, datos }.
            $respuestaApi = $response->json();

            return view('catalogo.index', [
                'cuentas' => $respuestaApi['datos'] ?? [],
                'errorApi' => null,
            ]);

        } catch (ConnectionException $exception) {
            Log::error('No fue posible conectar con la API Pinax.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return view('catalogo.index', [
                'cuentas' => [],
                'errorApi' => 'No fue posible conectar con la API Pinax. Verifica que Node.js esté ejecutándose.',
            ]);

        } catch (Throwable $exception) {
            Log::error('Error inesperado al consultar el catálogo.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return view('catalogo.index', [
                'cuentas' => [],
                'errorApi' => 'Ocurrió un error inesperado al obtener el catálogo de cuentas.',
            ]);
        }
    }

    // GET /catalogo/crear: muestra el formulario de registro.
    public function create(): View
    {
        return view('catalogo.create');
    }

    /*
    POST /catalogo
    Recibe el formulario, valida su formato y reenvía los datos a
    POST /api/catalogo. Laravel nunca inserta directamente en MySQL.
    */
    public function store(
        StoreCuentaRequest $request,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            $datosCuenta = $request->validated();

            /*
            Este valor no se recibe desde el navegador.
            Más adelante se reemplazará por el usuario autenticado
            que venga desde la sesión de Laravel.
            */
            $datosCuenta['usr_adicion'] = 'laravel_frontend';

            // Enviamos el JSON a la API Node.js.
            $response = $pinaxApi->post('/catalogo', $datosCuenta);

            if ($response->successful()) {
                return to_route('catalogo.index')->with(
                    'success',
                    $response->json('mensaje')
                        ?? 'Cuenta contable registrada correctamente.'
                );
            }

            Log::warning('La API Pinax rechazó el registro de una cuenta.', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => $response->json('mensaje')
                        ?? 'La API no pudo registrar la cuenta contable.',
                ]);

        } catch (ConnectionException $exception) {
            Log::error('No fue posible conectar con la API al crear una cuenta.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'No fue posible conectar con la API Pinax.',
                ]);

        } catch (Throwable $exception) {
            Log::error('Error inesperado al crear una cuenta contable.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'Ocurrió un error inesperado al registrar la cuenta contable.',
                ]);
        }
    }

    // Consulta una cuenta en la API y muestra el formulario de edición.
    public function edit(
        int $codCuenta,
        PinaxApiService $pinaxApi
    ): View|RedirectResponse {
        try {
            // La API consulta una cuenta especifica mediante el filtro cod_cuenta.
            $response = $pinaxApi->get('/catalogo', [
                'cod_cuenta' => $codCuenta,
            ]);

            if ($response->failed()) {
                return redirect()
                    ->route('catalogo.index')
                    ->withErrors([
                        'api' => $response->json(
                            'mensaje',
                            'No fue posible consultar la cuenta contable.'
                        ),
                    ]);
            }

            $cuentas = $response->json('datos', []);

            if (empty($cuentas)) {
                return redirect()
                    ->route('catalogo.index')
                    ->withErrors([
                        'api' => 'La cuenta contable solicitada no existe.',
                    ]);
            }

            return view('catalogo.edit', [
                'cuenta' => $cuentas[0],
            ]);

        } catch (ConnectionException $exception) {
            Log::error('No se pudo conectar con la API al consultar la cuenta.', [
                'cod_cuenta' => $codCuenta,
                'mensaje' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('catalogo.index')
                ->withErrors([
                    'api' => 'No fue posible conectar con la API de Pinax.',
                ]);

        } catch (Throwable $exception) {
            Log::error('Error inesperado al consultar una cuenta contable.', [
                'cod_cuenta' => $codCuenta,
                'mensaje' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('catalogo.index')
                ->withErrors([
                    'api' => 'Ocurrió un error al cargar la cuenta contable.',
                ]);
        }
    }

    /*
    PUT /catalogo/{codCuenta}
    Valida la información y solicita la actualización a la API.
    También permite aplicar soft delete (ind_estado = inactivo).
    */
    public function update(
        UpdateCuentaRequest $request,
        int $codCuenta,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            $datos = $request->validated();
            $datos['usr_modificacion'] = 'laravel_frontend';

            $response = $pinaxApi->put(
                "/catalogo/{$codCuenta}",
                $datos
            );

            if ($response->failed()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'api' => $response->json(
                            'mensaje',
                            'No fue posible actualizar la cuenta contable.'
                        ),
                    ]);
            }

            return redirect()
                ->route('catalogo.index')
                ->with(
                    'success',
                    $response->json(
                        'mensaje',
                        'Cuenta contable actualizada correctamente.'
                    )
                );

        } catch (ConnectionException $exception) {
            Log::error('No se pudo conectar con la API al actualizar la cuenta.', [
                'cod_cuenta' => $codCuenta,
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'No fue posible conectar con la API de Pinax.',
                ]);

        } catch (Throwable $exception) {
            Log::error('Error inesperado al actualizar una cuenta contable.', [
                'cod_cuenta' => $codCuenta,
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'Ocurrió un error inesperado al actualizar la cuenta contable.',
                ]);
        }
    }

    /*
    PATCH /catalogo/{codCuenta}/estado
    Alterna el estado logico de una cuenta entre activo e inactivo,
    reutilizando el mismo endpoint PUT de actualización (soft delete).
    */
    public function toggleStatus(
        int $codCuenta,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            $consulta = $pinaxApi->get('/catalogo', [
                'cod_cuenta' => $codCuenta,
            ]);

            if ($consulta->failed()) {
                return redirect()
                    ->route('catalogo.index')
                    ->withErrors([
                        'api' => $consulta->json(
                            'mensaje',
                            'No fue posible consultar la cuenta contable.'
                        ),
                    ]);
            }

            $cuentas = $consulta->json('datos', []);

            if (empty($cuentas)) {
                return redirect()
                    ->route('catalogo.index')
                    ->withErrors([
                        'api' => 'La cuenta contable seleccionada no existe.',
                    ]);
            }

            $cuenta = $cuentas[0];

            $estadoActual = data_get($cuenta, 'ind_estado');
            $nuevoEstado = $estadoActual === 'activo' ? 'inactivo' : 'activo';

            // La API exige todos los campos de la cuenta para actualizarla.
            $datosActualizados = [
                'cod_num_cuenta' => data_get($cuenta, 'cod_num_cuenta'),
                'nom_cuenta' => data_get($cuenta, 'nom_cuenta'),
                'cod_tipo_cuenta' => data_get($cuenta, 'cod_tipo_cuenta'),
                'cod_cuenta_padre' => data_get($cuenta, 'cod_cuenta_padre'),
                'num_nivel_jerarquia' => data_get($cuenta, 'num_nivel_jerarquia'),
                'ind_naturaleza_cuenta' => data_get($cuenta, 'ind_naturaleza'),
                'ind_acepta_movimiento' => data_get($cuenta, 'ind_acepta_movimiento'),
                'des_cuenta' => data_get($cuenta, 'des_cuenta'),
                'ind_estado' => $nuevoEstado,
                'usr_modificacion' => 'laravel_frontend',
            ];

            $respuesta = $pinaxApi->put(
                "/catalogo/{$codCuenta}",
                $datosActualizados
            );

            if ($respuesta->failed()) {
                return redirect()
                    ->route('catalogo.index')
                    ->withErrors([
                        'api' => $respuesta->json(
                            'mensaje',
                            'No fue posible cambiar el estado de la cuenta.'
                        ),
                    ]);
            }

            $mensaje = $nuevoEstado === 'activo'
                ? 'Cuenta contable activada correctamente.'
                : 'Cuenta contable inactivada correctamente.';

            return redirect()
                ->route('catalogo.index')
                ->with('success', $mensaje);

        } catch (ConnectionException $exception) {
            Log::error('No se pudo conectar con la API al cambiar estado.', [
                'cod_cuenta' => $codCuenta,
                'mensaje' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('catalogo.index')
                ->withErrors([
                    'api' => 'No fue posible conectar con la API de Pinax.',
                ]);

        } catch (Throwable $exception) {
            Log::error('Error inesperado al cambiar estado de la cuenta.', [
                'cod_cuenta' => $codCuenta,
                'mensaje' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('catalogo.index')
                ->withErrors([
                    'api' => 'Ocurrió un error al cambiar el estado de la cuenta.',
                ]);
        }
    }
}
