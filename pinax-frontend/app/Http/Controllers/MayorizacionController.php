<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexMayorizacionRequest;
use App\Http\Requests\StoreMayorizacionRequest;
use App\Http\Requests\UpdateMayorizacionRequest;
use App\Services\PinaxApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class MayorizacionController extends Controller
{
    /**
     * Consulta el resumen y las opciones del módulo exclusivamente mediante
     * GET /api/mayorizacion. Laravel no accede directamente a MySQL.
     */
    public function index(
        IndexMayorizacionRequest $request,
        PinaxApiService $pinaxApi
    ): View {
        // Inicializamos los datos para poder renderizar aun si la API falla.
        $saldos = [];
        $cuentas = [];
        $periodos = [];
        $erroresApi = [];

        // Solo reenviamos filtros validados y con contenido útil.
        $filtros = collect($request->validated())
            ->reject(
                fn (mixed $valor): bool =>
                    $valor === null || $valor === ''
            )
            ->all();

        try {
            /*
             * La vista "resumen" devuelve los saldos mayorizados.
             *
             * El servidor establece el tipo de vista, por lo que el formulario
             * del navegador no puede modificar libremente esta operación.
             */
            $respuestaResumen = $pinaxApi->get('/mayorizacion', [
                'vista' => 'resumen',
                ...$filtros,
            ]);

            if ($respuestaResumen->successful()) {
                // Recuperamos solamente el arreglo almacenado en "datos".
                $datosResumen = $respuestaResumen->json('datos', []);

                // Garantizamos que Blade siempre reciba un arreglo.
                $saldos = is_array($datosResumen)
                    ? $datosResumen
                    : [];
            } else {
                /*
                 * Registramos la respuesta completa para diagnóstico,
                 * pero no mostramos detalles técnicos en el navegador.
                 */
                Log::warning(
                    'La API rechazó la consulta de Mayorización.',
                    [
                        'status' => $respuestaResumen->status(),
                        'body' => $respuestaResumen->json(),
                        'filtros' => $filtros,
                    ]
                );

                $erroresApi[] = $respuestaResumen->json(
                    'mensaje',
                    'No fue posible consultar los saldos mayorizados.'
                );
            }

            /*
             * La vista "opciones" proporciona las cuentas y períodos
             * disponibles sin consultar directamente las tablas de MySQL.
             */
            $respuestaOpciones = $pinaxApi->get('/mayorizacion', [
                'vista' => 'opciones',
            ]);

            if ($respuestaOpciones->successful()) {
                // Recuperamos la estructura completa de opciones.
                $datosOpciones = $respuestaOpciones->json('datos', []);

                // Extraemos las cuentas y períodos devueltos por la API.
                $cuentasApi = data_get(
                    $datosOpciones,
                    'cuentas',
                    []
                );

                $periodosApi = data_get(
                    $datosOpciones,
                    'periodos',
                    []
                );

                // Blade siempre recibirá arreglos válidos.
                $cuentas = is_array($cuentasApi)
                    ? $cuentasApi
                    : [];

                $periodos = is_array($periodosApi)
                    ? $periodosApi
                    : [];
            } else {
                Log::warning(
                    'La API rechazó las opciones de Mayorización.',
                    [
                        'status' => $respuestaOpciones->status(),
                        'body' => $respuestaOpciones->json(),
                    ]
                );

                $erroresApi[] = $respuestaOpciones->json(
                    'mensaje',
                    'No fue posible cargar las cuentas y los períodos.'
                );
            }
        } catch (ConnectionException $exception) {
            /*
             * Esta excepción ocurre cuando Laravel no puede comunicarse
             * con la API desarrollada en Node.js.
             */
            Log::error(
                'No fue posible conectar con la API de Mayorización.',
                [
                    'mensaje' => $exception->getMessage(),
                ]
            );

            $erroresApi[] = 'No fue posible conectar con la API Pinax. '
                .'Verifica que Node.js esté ejecutándose.';
        } catch (Throwable $exception) {
            // Registramos cualquier error inesperado sin exponerlo al usuario.
            Log::error(
                'Error inesperado al consultar Mayorización.',
                [
                    'mensaje' => $exception->getMessage(),
                ]
            );

            $erroresApi[] =
                'Ocurrió un error inesperado al cargar Mayorización.';
        }

        /*
         * Estas métricas cuentan registros.
         *
         * No sumamos saldos monetarios porque las cuentas pueden tener
         * naturalezas contables diferentes.
         */
        $coleccionSaldos = collect($saldos);

        $metricas = [
            // Total de saldos obtenidos con los filtros actuales.
            'total' => $coleccionSaldos->count(),

            // Saldos que todavía se encuentran abiertos.
            'abiertos' => $coleccionSaldos
                ->where('ind_estado', 'abierto')
                ->count(),

            // Saldos que ya fueron recalculados.
            'recalculados' => $coleccionSaldos
                ->where('ind_estado', 'recalculado')
                ->count(),

            // Saldos contables que ya fueron consolidados.
            'cerrados' => $coleccionSaldos
                ->where('ind_estado', 'cerrado')
                ->count(),
        ];

        // Enviamos a Blade toda la información necesaria.
        return view('mayorizacion.index', [
            'saldos' => $saldos,
            'cuentas' => $cuentas,
            'periodos' => $periodos,
            'metricas' => $metricas,

            /*
             * Si hay varios errores, eliminamos los duplicados y los
             * combinamos en un solo mensaje.
             */
            'errorApi' => empty($erroresApi)
                ? null
                : implode(
                    ' ',
                    array_unique($erroresApi)
                ),
        ]);
    }

    /**
     * Muestra los movimientos que componen una Cuenta T.
     *
     * El navegador únicamente proporciona el código del saldo.
     * Laravel consulta primero el resumen para obtener la cuenta y el período
     * correspondientes y luego solicita el detalle completo a la API.
     */
    public function show(
        PinaxApiService $pinaxApi,
        int $cod_saldo
    ): View|RedirectResponse {
        try {
            /*
             * Primera consulta:
             *
             * Buscamos el saldo específico para conocer:
             * - cod_cuenta;
             * - cod_periodo.
             *
             * Esto evita recibir identificadores contables redundantes
             * o contradictorios desde el navegador.
             */
            $respuestaSaldo = $pinaxApi->get('/mayorizacion', [
                'vista' => 'resumen',
                'cod_saldo' => $cod_saldo,
            ]);

            if (!$respuestaSaldo->successful()) {
                Log::warning(
                    'La API rechazó la búsqueda del saldo.',
                    [
                        'status' => $respuestaSaldo->status(),
                        'body' => $respuestaSaldo->json(),
                        'cod_saldo' => $cod_saldo,
                    ]
                );

                return to_route('mayorizacion.index')
                    ->withErrors([
                        'api' => $respuestaSaldo->json(
                            'mensaje',
                            'No fue posible localizar la mayorización solicitada.'
                        ),
                    ]);
            }

            /*
             * El filtro por cod_saldo debe devolver como máximo un registro.
             * Por eso recuperamos la primera posición del arreglo.
             */
            $saldo = $respuestaSaldo->json('datos.0');

            // Los saldos inactivos tampoco aparecen en la consulta normal.
            if (!is_array($saldo)) {
                return to_route('mayorizacion.index')
                    ->withErrors([
                        'api' => 'La mayorización solicitada no existe '
                            .'o está inactiva.',
                    ]);
            }

            /*
             * Recuperamos las dimensiones contables desde la respuesta
             * confiable de la API.
             */
            $codCuenta = (int) data_get(
                $saldo,
                'cod_cuenta'
            );

            $codPeriodo = (int) data_get(
                $saldo,
                'cod_periodo'
            );

            // Comprobamos que ambos identificadores sean válidos.
            if ($codCuenta < 1 || $codPeriodo < 1) {
                Log::error(
                    'El saldo no contiene dimensiones contables válidas.',
                    [
                        'cod_saldo' => $cod_saldo,
                        'saldo' => $saldo,
                    ]
                );

                return to_route('mayorizacion.index')
                    ->withErrors([
                        'api' => 'La mayorización no contiene una cuenta '
                            .'o período válido.',
                    ]);
            }

            /*
             * Segunda consulta:
             *
             * Solicitamos la vista "cuenta_t" utilizando la cuenta y el
             * período obtenidos desde el saldo.
             *
             * La respuesta contiene:
             * - resumen;
             * - movimientos;
             * - total_movimientos.
             */
            $respuestaCuentaT = $pinaxApi->get('/mayorizacion', [
                'vista' => 'cuenta_t',
                'cod_cuenta' => $codCuenta,
                'cod_periodo' => $codPeriodo,
            ]);

            if (!$respuestaCuentaT->successful()) {
                Log::warning(
                    'La API rechazó la consulta de la Cuenta T.',
                    [
                        'status' => $respuestaCuentaT->status(),
                        'body' => $respuestaCuentaT->json(),
                        'cod_saldo' => $cod_saldo,
                        'cod_cuenta' => $codCuenta,
                        'cod_periodo' => $codPeriodo,
                    ]
                );

                return to_route('mayorizacion.index')
                    ->withErrors([
                        'api' => $respuestaCuentaT->json(
                            'mensaje',
                            'No fue posible cargar los movimientos '
                                .'de la Cuenta T.'
                        ),
                    ]);
            }

            // Recuperamos la estructura principal devuelta por la API.
            $datosCuentaT = $respuestaCuentaT->json(
                'datos',
                []
            );

            // Extraemos el encabezado y los totales.
            $resumen = data_get(
                $datosCuentaT,
                'resumen'
            );

            // Extraemos los movimientos individuales.
            $movimientos = data_get(
                $datosCuentaT,
                'movimientos',
                []
            );

            // La vista necesita obligatoriamente un resumen válido.
            if (!is_array($resumen)) {
                return to_route('mayorizacion.index')
                    ->withErrors([
                        'api' => 'La API no devolvió el resumen '
                            .'de la Cuenta T.',
                    ]);
            }

            // Si los movimientos tienen un formato inesperado, usamos un arreglo vacío.
            if (!is_array($movimientos)) {
                $movimientos = [];
            }

            // Mostramos la página dedicada al detalle de la Cuenta T.
            return view('mayorizacion.show', [
                'resumen' => $resumen,
                'movimientos' => $movimientos,
                'totalMovimientos' => count($movimientos),
            ]);
        } catch (ConnectionException $exception) {
            Log::error(
                'No fue posible conectar con la API al abrir la Cuenta T.',
                [
                    'cod_saldo' => $cod_saldo,
                    'mensaje' => $exception->getMessage(),
                ]
            );

            return to_route('mayorizacion.index')
                ->withErrors([
                    'api' => 'No fue posible conectar con la API Pinax. '
                        .'Verifica que Node.js esté ejecutándose.',
                ]);
        } catch (Throwable $exception) {
            Log::error(
                'Error inesperado al consultar la Cuenta T.',
                [
                    'cod_saldo' => $cod_saldo,
                    'mensaje' => $exception->getMessage(),
                ]
            );

            return to_route('mayorizacion.index')
                ->withErrors([
                    'api' => 'Ocurrió un error inesperado al consultar '
                        .'la Cuenta T.',
                ]);
        }
    }

    /**
     * Genera la primera mayorización de una cuenta dentro de un período.
     *
     * Laravel valida el formulario y delega el cálculo real a la API.
     * Los importes nunca son recibidos desde el navegador.
     */
    public function store(
        StoreMayorizacionRequest $request,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            /*
             * Extraemos exclusivamente la cuenta y el período previamente
             * validados por StoreMayorizacionRequest.
             */
            $datosMayorizacion = $request->safe()->only([
                'cod_cuenta',
                'cod_periodo',
            ]);

            /*
             * Laravel actúa como cliente HTTP.
             *
             * La API calculará los saldos desde los asientos aprobados
             * y ejecutará el procedimiento almacenado correspondiente.
             */
            $respuesta = $pinaxApi->post(
                '/mayorizacion',
                $datosMayorizacion
            );

            // Cualquier respuesta HTTP 2xx representa una operación exitosa.
            if ($respuesta->successful()) {
                return to_route('mayorizacion.index')
                    ->with(
                        'success',
                        $respuesta->json(
                            'mensaje',
                            'La cuenta fue mayorizada correctamente.'
                        )
                    );
            }

            /*
             * Registramos la respuesta técnica para diagnóstico,
             * pero no mostramos el cuerpo completo al usuario.
             */
            Log::warning(
                'La API rechazó la generación de Mayorización.',
                [
                    'status' => $respuesta->status(),
                    'body' => $respuesta->json(),
                    'datos' => $datosMayorizacion,
                ]
            );

            /*
             * Regresamos al formulario conservando la cuenta y el período
             * seleccionados.
             */
            return back()
                ->withInput()
                ->withErrors([
                    'api' => $respuesta->json(
                        'mensaje',
                        'No fue posible generar la mayorización.'
                    ),
                ]);
        } catch (ConnectionException $exception) {
            Log::error(
                'No fue posible conectar con la API al mayorizar.',
                [
                    'mensaje' => $exception->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'No fue posible conectar con la API Pinax. '
                        .'Verifica que Node.js esté ejecutándose.',
                ]);
        } catch (Throwable $exception) {
            Log::error(
                'Error inesperado al generar la mayorización.',
                [
                    'mensaje' => $exception->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'Ocurrió un error inesperado al generar '
                        .'la mayorización.',
                ]);
        }
    }

    /**
     * Ejecuta una acción controlada sobre un saldo mayorizado.
     *
     * Las acciones admitidas son:
     * - recalcular;
     * - cerrar;
     * - inactivar.
     *
     * Inactivar representa el soft delete y no elimina físicamente el saldo.
     */
    public function update(
        UpdateMayorizacionRequest $request,
        PinaxApiService $pinaxApi,
        int $cod_saldo
    ): RedirectResponse {
        try {
            /*
             * UpdateMayorizacionRequest ya normalizó y validó:
             * - el código recibido desde la URL;
             * - la acción recibida desde el formulario.
             */
            $datosValidados = $request->validated();

            // Convertimos explícitamente el código a entero.
            $codSaldo = (int) $datosValidados['cod_saldo'];

            // La acción ya se encuentra normalizada en minúsculas.
            $accion = $datosValidados['accion'];

            /*
             * El código del saldo permanece en la URL.
             *
             * En el cuerpo solo enviamos la acción para evitar que el
             * navegador cambie directamente el estado contable.
             */
            $respuesta = $pinaxApi->put(
                "/mayorizacion/{$codSaldo}",
                [
                    'accion' => $accion,
                ]
            );

            /*
             * Regresamos al resumen después de una operación exitosa
             * para consultar y mostrar los datos actualizados.
             */
            if ($respuesta->successful()) {
                return to_route('mayorizacion.index')
                    ->with(
                        'success',
                        $respuesta->json(
                            'mensaje',
                            'La mayorización fue actualizada correctamente.'
                        )
                    );
            }

            // Conservamos información suficiente para diagnosticar el rechazo.
            Log::warning(
                'La API rechazó una acción de Mayorización.',
                [
                    'status' => $respuesta->status(),
                    'body' => $respuesta->json(),
                    'cod_saldo' => $codSaldo,
                    'accion' => $accion,
                ]
            );

            // Mostramos solamente el mensaje controlado por la API.
            return back()
                ->withErrors([
                    'api' => $respuesta->json(
                        'mensaje',
                        'No fue posible actualizar la mayorización.'
                    ),
                ]);
        } catch (ConnectionException $exception) {
            Log::error(
                'No fue posible conectar con la API al actualizar '
                    .'Mayorización.',
                [
                    'cod_saldo' => $cod_saldo,
                    'mensaje' => $exception->getMessage(),
                ]
            );

            return back()
                ->withErrors([
                    'api' => 'No fue posible conectar con la API Pinax. '
                        .'Verifica que Node.js esté ejecutándose.',
                ]);
        } catch (Throwable $exception) {
            Log::error(
                'Error inesperado al actualizar Mayorización.',
                [
                    'cod_saldo' => $cod_saldo,
                    'mensaje' => $exception->getMessage(),
                ]
            );

            return back()
                ->withErrors([
                    'api' => 'Ocurrió un error inesperado al actualizar '
                        .'la mayorización.',
                ]);
        }
    }
}