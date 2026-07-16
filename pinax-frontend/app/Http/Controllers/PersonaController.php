<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdatePersonaRequest as RequestsUpdatePersonaRequest;
use App\Services\PinaxApiService;
use Illuminate\Http\Client\UpdatePersonaRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PersonaController extends Controller
{

    // GET /personas: Consulta las personas exclusivamente por medio de la API Pinax.


    public function index(Request $request, PinaxApiService $pinaxApi): View
    {
        try {
            // Recibimos solamente los filtros aceptados por la API.
            $filtros = collect($request->only([
                'cod_people',
                'dni',
                'ind_people',
            ]))
                ->filter(fn ($valor) => filled($valor))
                ->all();

            // Laravel realiza una solicitud HTTP hacia GET /api/personas.
            $response = $pinaxApi->get('/personas', $filtros);

            // Controlamos respuestas HTTP 400, 404 o 500 de la API.
            if ($response->failed()) {
                Log::warning('La API Pinax devolvió un error al consultar personas.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return view('personas.index', [
                    'personas' => [],
                    'errorApi' => $response->json('mensaje')
                        ?? 'No fue posible consultar las personas en la API.',
                ]);
            }

            // La API responde con una estructura como: { estado, total, datos }.
            $respuestaApi = $response->json();

            return view('personas.index', [
                'personas' => $respuestaApi['datos'] ?? [],
                'errorApi' => null,
            ]);

        } catch (ConnectionException $exception) {
            Log::error('No fue posible conectar con la API Pinax.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return view('personas.index', [
                'personas' => [],
                'errorApi' => 'No fue posible conectar con la API Pinax. Verifica que Node.js esté ejecutándose.',
            ]);

        } catch (\Throwable $exception) {
            Log::error('Error inesperado al consultar personas.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return view('personas.index', [
                'personas' => [],
                'errorApi' => 'Ocurrió un error inesperado al obtener las personas.',
            ]);
        }
    }

    // GET /personas/crear: Muestra el formulario de registro.

    public function create(): View
    {
        return view('personas.create');
    }

    /*
    POST /personas
    Recibe el formulario, valida su formato y reenvía los datos a
    POST /api/personas. Laravel nunca inserta directamente en MySQL.
    */

    public function store(
        StorePersonaRequest $request,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            // Obtenemos solo los campos aprobados por StorePersonaRequest.
            $datosPersona = $request->validated();

            /*
            | Este valor no se recibe desde el navegador.
            | Más adelante se reemplazará por el usuario autenticado
            | que venga desde la sesión de Laravel.
            */
            $datosPersona['usr_add'] = 'laravel_frontend';

            // Enviamos el JSON a la API Node.js.
            $response = $pinaxApi->post('/personas', $datosPersona);

            // La API debe responder con 201 o cualquier estado 2xx.
            if ($response->successful()) {
                return to_route('personas.index')->with(
                    'success',
                    $response->json('mensaje')
                        ?? 'Persona registrada correctamente.'
                );
            }

            /*
            | Si la API rechaza el registro, mostramos su mensaje.
            | Ejemplo: DNI duplicado o validación del procedimiento.
            */
            Log::warning('La API Pinax rechazó el registro de una persona.', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => $response->json('mensaje')
                        ?? 'La API no pudo registrar la persona.',
                ]);

        } catch (ConnectionException $exception) {
            Log::error('No fue posible conectar con la API al crear una persona.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'No fue posible conectar con la API Pinax.',
                ]);

        } catch (\Throwable $exception) {
            Log::error('Error inesperado al crear una persona.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'Ocurrió un error inesperado al registrar la persona.',
                ]);
        }
    }

    // Consulta una persona en la API y muestra el formulario de edicion
    public function edit(
        int $codPeople,
        PinaxApiService $pinaxApi
    ):View|RedirectResponse {
        try {
            // La API consutla una persona mediante el filtro cod_people.
            $response = $pinaxApi->get('/personas', [
                'cod_people' => $codPeople,
            ]);

            if ($response->failed()) {
                return redirect()
                ->route('personas.index')
                ->withErrors([
                    'api' => $response->json(
                        'mensaje',
                        'No fue posible consultar la persona.'
                    ),
                ]);
            }

            // El endpoint devuelve los registros dentro de la propiedad datos.
            $personas = $response->json('datos', []);

            if (empty($personas)) {
                return redirect()
                ->route('personas.index')
                ->withErrors([
                    'api' => 'La persona solicitada no existe.'
                ]);
            }

            return view('personas.edit', [
                'persona' => $personas[0],
            ]);
        } catch (ConnectionException $exception) {
            Log::error('No se pudo conectar con la API al consultar la persona.', [
                'cod_people' => $codPeople,
                'mensaje' => $exception->getMessage(),
            ]);

            return redirect()
            ->route('personas.index')
            ->withErrors([
                'api' => 'No fue posible conectar con la API de Pinax.',
            ]);
        } catch (Throwable $exception) {
            Log::error('Error inesperado al consultar una persona.', [
                'cod_people' => $codPeople,
                'mensaje' => $exception->getMessage(),
            ]);

            return redirect()
            ->route('personas.index')
            ->withErrors([
                'api' => 'Ocurrio un error al cargar la persona.',
            ]);
        }
    }

    // Valida la información y solicita la actualización a la API.
    public function update(
        RequestsUpdatePersonaRequest $request,
        int $codPeople,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            // Solo enviamos los campos previamente validados.
            $datos = $request->validated();

            $response = $pinaxApi->put(
                "/personas/{$codPeople}",
                $datos
            );

            if ($response->failed()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'api' => $response->json(
                            'mensaje',
                            'No fue posible actualizar la persona.'
                        ),
                    ]);
            }

            return redirect()
                ->route('personas.index')
                ->with(
                    'success',
                    $response->json(
                        'mensaje',
                        'Persona actualizada correctamente.'
                    )
                );
        } catch (ConnectionException $exception) {
            Log::error('No se pudo conectar con la API al actualizar.', [
                'cod_people' => $codPeople,
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'No fue posible conectar con la API de Pinax.',
                ]);
        } catch (Throwable $exception) {
            Log::error('Error inesperado al actualizar una persona.', [
                'cod_people' => $codPeople,
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'Ocurrió un error inesperado al actualizar la persona.',
                ]);
        }
    }


    // Alterna el estado lógico de una persona entre activo e inactivo.
    public function toggleStatus(
        int $codPeople,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            // Consultamos la persona actual mediante la API.
            $consulta = $pinaxApi->get('/personas', [
                'cod_people' => $codPeople,
            ]);

            if ($consulta->failed()) {
                return redirect()
                    ->route('personas.index')
                    ->withErrors([
                        'api' => $consulta->json(
                            'mensaje',
                            'No fue posible consultar la persona.'
                        ),
                    ]);
            }

            // La API devuelve los registros dentro de la propiedad datos.
            $personas = $consulta->json('datos', []);

            if (empty($personas)) {
                return redirect()
                    ->route('personas.index')
                    ->withErrors([
                        'api' => 'La persona seleccionada no existe.',
                    ]);
            }

            $persona = $personas[0];

            // Determinamos el estado contrario al estado actual.
            $estadoActual = data_get($persona, 'ind_people');

            $nuevoEstado = $estadoActual === 'activo'
                ? 'inactivo'
                : 'activo';

            // Construimos todos los campos que el PUT de la API requiere.
            $datosActualizados = [
                'dni' => data_get($persona, 'dni'),
                'firstname' => data_get($persona, 'firstname'),
                'middlename' => data_get($persona, 'middlename'),
                'lastname' => data_get($persona, 'lastname'),
                'sex' => data_get($persona, 'sex'),
                'ind_civil' => data_get($persona, 'ind_civil'),
                'age' => data_get($persona, 'age'),
                'tip_person' => data_get($persona, 'tip_person'),
                'ind_people' => $nuevoEstado,
            ];

            // La API actual utiliza PUT para actualizar la persona completa.
            $respuesta = $pinaxApi->put(
                "/personas/{$codPeople}",
                $datosActualizados
            );

            if ($respuesta->failed()) {
                return redirect()
                    ->route('personas.index')
                    ->withErrors([
                        'api' => $respuesta->json(
                            'mensaje',
                            'No fue posible cambiar el estado de la persona.'
                        ),
                    ]);
            }

            $mensaje = $nuevoEstado === 'activo'
                ? 'Persona activada correctamente.'
                : 'Persona inactivada correctamente.';

            return redirect()
                ->route('personas.index')
                ->with('success', $mensaje);

        } catch (ConnectionException $exception) {
            Log::error('No se pudo conectar con la API al cambiar estado.', [
                'cod_people' => $codPeople,
                'mensaje' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('personas.index')
                ->withErrors([
                    'api' => 'No fue posible conectar con la API de Pinax.',
                ]);

        } catch (Throwable $exception) {
            Log::error('Error inesperado al cambiar estado de persona.', [
                'cod_people' => $codPeople,
                'mensaje' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('personas.index')
                ->withErrors([
                    'api' => 'Ocurrió un error al cambiar el estado de la persona.',
                ]);
        }
    }
}