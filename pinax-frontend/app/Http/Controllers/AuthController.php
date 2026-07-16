<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\PinaxApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    
    // Muestra el formulario de acceso.
    public function showLogin(Request $request): View|RedirectResponse
    {
        // Si ya existe una sesión, enviamos al dashboard.
        if ($request->session()->has('pinax_api_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /*
    - Envía las credenciales a la API.
    - Laravel no consulta la tabla users ni valida contraseñas.
     */
    public function login(
        LoginRequest $request,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            $response = $pinaxApi->post('/auth/login', [
                'name' => $request->validated('name'),
                'password' => $request->validated('password'),
            ]);

            if ($response->failed()) {
                return back()
                    ->withInput([
                        'name' => $request->validated('name'),
                    ])
                    ->withErrors([
                        'login' => $response->json(
                            'mensaje',
                            'No fue posible iniciar sesión.'
                        ),
                    ]);
            }

            $token = $response->json('access_token');
            $usuario = $response->json('usuario');

            // Validamos que la API haya entregado los datos esperados.
            if (
                !is_string($token)
                || $token === ''
                || !is_array($usuario)
            ) {
                Log::error('La API devolvió un login incompleto.', [
                    'respuesta' => $response->json(),
                ]);

                return back()
                    ->withInput([
                        'name' => $request->validated('name'),
                    ])
                    ->withErrors([
                        'login' => 'La API devolvió una sesión incompleta.',
                    ]);
            }

            // Regeneramos el identificador para prevenir fijación de sesión.
            $request->session()->regenerate();

            /*
            - Guardamos el token solamente en la sesión de Laravel.
            - El token nunca se almacena en JavaScript o localStorage.
             */
            $request->session()->put([
                'pinax_api_token' => $token,
                'pinax_user' => $usuario,

                /*
                - La API entrega tokens de dos horas.
                - Posteriormente podemos devolver expires_at directamente.
                */
                'pinax_token_expires_at' => now()
                    ->addHours(2)
                    ->toIso8601String(),
            ]);

            return redirect()
                ->intended(route('dashboard'))
                ->with(
                    'success',
                    'Bienvenido a Pinax, '
                    . data_get($usuario, 'firstname', data_get($usuario, 'name'))
                    . '.'
                );
        } catch (ConnectionException $exception) {
            Log::error('No se pudo conectar con la API durante el login.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput([
                    'name' => $request->validated('name'),
                ])
                ->withErrors([
                    'login' => 'No fue posible conectar con la API de Pinax.',
                ]);
        } catch (Throwable $exception) {
            Log::error('Error inesperado durante el login.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput([
                    'name' => $request->validated('name'),
                ])
                ->withErrors([
                    'login' => 'Ocurrió un error inesperado al iniciar sesión.',
                ]);
        }
    }

    /*
    - Destruye la sesión web.
    - El JWT deja de estar disponible para Laravel.
    */
    public function logout(Request $request): RedirectResponse
    {
        // Elimina todos los datos y regenera el identificador.
        $request->session()->invalidate();

        // Genera un nuevo token CSRF para la siguiente sesión.
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'La sesión fue cerrada correctamente.');
    }
}