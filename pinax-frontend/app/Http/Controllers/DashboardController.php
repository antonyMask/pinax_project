<?php

namespace App\Http\Controllers;

use App\Services\PinaxApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal de Pinax.
     * Laravel obtiene todos los datos mediante la API.
     */
    public function index(PinaxApiService $pinaxApi): View
    {
        // Aquí guardaremos advertencias de endpoints que no respondan.
        $advertencias = [];

        // Cada recurso se obtiene de manera independiente.
        // Si uno falla, los demás todavía pueden mostrarse.
        $personas = $this->obtenerDatos(
            $pinaxApi,
            '/personas',
            'personas',
            $advertencias
        );

        $cuentas = $this->obtenerDatos(
            $pinaxApi,
            '/catalogo',
            'catálogo de cuentas',
            $advertencias
        );

        $asientos = $this->obtenerDatos(
            $pinaxApi,
            '/asientos',
            'asientos contables',
            $advertencias
        );

        // Calculamos métricas utilizando únicamente las respuestas de la API.
        $totalPersonas = count($personas);

        $personasActivas = count(array_filter(
            $personas,
            fn (array $persona): bool =>
                strtolower((string) data_get($persona, 'ind_people')) === 'activo'
        ));

        $totalCuentas = count($cuentas);

        $cuentasActivas = count(array_filter(
            $cuentas,
            fn (array $cuenta): bool =>
                strtolower((string) data_get($cuenta, 'ind_estado')) === 'activo'
        ));

        $totalAsientos = count($asientos);

        $asientosAprobados = count(array_filter(
            $asientos,
            fn (array $asiento): bool =>
                strtolower((string) data_get($asiento, 'ind_estado')) === 'aprobado'
        ));

        // Calculamos el porcentaje para la barra visual.
        $porcentajePersonasActivas = $totalPersonas > 0
            ? (int) round(($personasActivas / $totalPersonas) * 100)
            : 0;

        // Ordenamos los asientos desde la fecha más reciente.
        usort($asientos, function (array $primero, array $segundo): int {
            $fechaPrimero = strtotime(
                (string) data_get($primero, 'fec_asiento')
            ) ?: 0;

            $fechaSegundo = strtotime(
                (string) data_get($segundo, 'fec_asiento')
            ) ?: 0;

            return $fechaSegundo <=> $fechaPrimero;
        });

        // En el dashboard mostramos solamente los cinco más recientes.
        $asientosRecientes = array_slice($asientos, 0, 5);

        return view('dashboard.index', [
            'metricas' => [
                'total_personas' => $totalPersonas,
                'personas_activas' => $personasActivas,
                'total_cuentas' => $totalCuentas,
                'cuentas_activas' => $cuentasActivas,
                'total_asientos' => $totalAsientos,
                'asientos_aprobados' => $asientosAprobados,
            ],
            'porcentajePersonasActivas' => $porcentajePersonasActivas,
            'asientosRecientes' => $asientosRecientes,
            'advertencias' => $advertencias,
        ]);
    }

    /**
     * Consulta un recurso y devuelve su arreglo datos.
     * Un error en un endpoint no debe impedir que cargue todo el dashboard.
     */
    private function obtenerDatos(
        PinaxApiService $pinaxApi,
        string $endpoint,
        string $nombreRecurso,
        array &$advertencias
    ): array {
        try {
            $response = $pinaxApi->get($endpoint);

            if ($response->failed()) {
                $advertencias[] = "No se pudo cargar {$nombreRecurso}.";

                Log::warning('La API respondió con error en el dashboard.', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'respuesta' => $response->json(),
                ]);

                return [];
            }

            $datos = $response->json('datos', []);

            return is_array($datos) ? $datos : [];
        } catch (Throwable $exception) {
            $advertencias[] = "No se pudo cargar {$nombreRecurso}.";

            Log::error('Error al consumir la API desde el dashboard.', [
                'endpoint' => $endpoint,
                'mensaje' => $exception->getMessage(),
            ]);

            return [];
        }
    }
}