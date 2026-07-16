<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PinaxApiService
{
    // Cliente HTTP base
    private function client(): PendingRequest
    {
        // Obtenemos la URL desde config/services.php y quitamos la barra final.
        $baseUrl = rtrim((string) config('services.pinax.base_url', '/'));

        // Creamos un cliente HTTP preparado para recibir y enviar JSON.
        $client = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(3)
            ->timeout((int) config('services.pinax.timeout', 10));

            /* Token de autenticacion futuro
            Cuando implementemos login, Laravel guaradara el token que entregue
            la API en la sesion. Si existe, se agrega automaticamente como:

            Authorization: Bearer <token>
            */

            $token = session('pinax_api_token');

            if (is_string($token) && $token !== '') {
                $client = $client->withToken($token);
            }

            return $client;
    }

        /* Metodos HTTP reutilizables
        Ejecuta solicitudes GET, opcionalmente con query parameters.
        */
        public function get(string $endpoint, array $query = []): Response
        {
            return $this->client()->get(ltrim($endpoint, '/'), $query);
        }

        // Ejecuta solicitudes POST enviando datos JSON.
        public function post(string $endpoint, array $data = []): Response
        {
            return $this->client()->post(ltrim($endpoint, '/'), $data);
        }

        // Ejecuta solicitudes PUT enviando datos JSON.
        public function put(string $endpoint, array $data = []): Response
        {
            return $this->client()->put(ltrim($endpoint, '/'), $data);
        }

        /*
        No utilizamos DELETE para modulos contables.
        La eliminacion logica se realizara con PUT y un estado como
        "inactivo" o "anulado". Este metodo queda disponible para una
        necesidad futura que si justifique eliminacion fisica
        */
        public function delete(string $endpoint): Response
        {
            return $this->client()->delete(ltrim($endpoint, '/'));
        }
}