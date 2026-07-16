<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsurePinaxAuthenticated
{
    /**
     * Permite continuar solamente si existe una sesión Pinax válida.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response|RedirectResponse {
        $token = $request->session()->get('pinax_api_token');
        $usuario = $request->session()->get('pinax_user');
        $expiresAt = $request->session()->get(
            'pinax_token_expires_at'
        );

        // La sesión debe contener token y usuario.
        if (
            !is_string($token)
            || $token === ''
            || !is_array($usuario)
        ) {
            return redirect()
                ->guest(route('login'))
                ->withErrors([
                    'login' => 'Debe iniciar sesión para continuar.',
                ]);
        }

        try {
            // Comprobamos la expiración registrada al iniciar sesión.
            if (
                !$expiresAt
                || Carbon::parse($expiresAt)->isPast()
            ) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'login' => 'La sesión expiró. Inicie sesión nuevamente.',
                    ]);
            }
        } catch (Throwable) {
            // Una fecha inválida también invalida la sesión.
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' => 'La sesión no es válida.',
                ]);
        }

        return $next($request);
    }
}