<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ReporteFinancieroController extends Controller
{
    /**
     * Mostrar lista de reportes
     */
    public function index()
    {
        try {
            $token = Session::get('access_token');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get('http://localhost:3000/api/reportes');

            $data = $response->json();

            // Si la API devuelve error
            if (isset($data['estado']) && $data['estado'] === 'error') {
                return view('admin.reportes.index', [
                    'reportes' => ['data' => []],
                    'error' => $data['mensaje'] ?? 'Error al cargar reportes'
                ]);
            }

            // TRANSFORMAR LOS DATOS DE LA API AL FORMATO QUE ESPERA LA VISTA
            if (isset($data['cabecera']) && is_array($data['cabecera'])) {
                $reportes = [
                    'data' => $data['cabecera'],
                    'total_reportes' => $data['total_reportes'] ?? count($data['cabecera']),
                    'balance_valido' => $data['balance_valido'] ?? true,
                    'mensaje_validacion' => $data['mensaje_validacion'] ?? ''
                ];
            } else {
                $reportes = [
                    'data' => [],
                    'total_reportes' => 0,
                    'balance_valido' => true,
                    'mensaje_validacion' => ''
                ];
            }

            return view('admin.reportes.index', compact('reportes'));

        } catch (\Exception $e) {
            return view('admin.reportes.index', [
                'reportes' => ['data' => []],
                'error' => 'Error al conectar con la API: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mostrar formulario para crear reporte
     */
    public function create()
    {
        return view('admin.reportes.create');
    }

    /**
     * Guardar nuevo reporte
     */
    public function store(Request $request)
    {
        $request->validate([
            'cod_periodo' => 'required|integer',
            'tip_reporte' => 'required|string',
            'calcular_automaticamente' => 'required|boolean',
        ]);

        try {
            // Obtener el cod_user de la sesión
            $codUser = session('pinax_user.cod_user') ?? session('user.cod_user') ?? 1;

            if (!$codUser) {
                $codUser = 1;
            }

            $token = Session::get('access_token');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->post('http://localhost:3000/api/reportes', [
                'cod_periodo' => (int) $request->cod_periodo,
                'cod_user' => (int) $codUser,
                'tip_reporte' => $request->tip_reporte,
                'calcular_automaticamente' => (bool) $request->calcular_automaticamente,
                'tot_activo' => $request->tot_activo ?? null,
                'tot_pasivo' => $request->tot_pasivo ?? null,
                'tot_patrimonio' => $request->tot_patrimonio ?? null,
                'mon_utilidad_perdida' => $request->mon_utilidad_perdida ?? null,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['estado']) && $data['estado'] === 'ok') {
                return redirect()->route('reportes.index')
                    ->with('success', $data['mensaje'] ?? 'Reporte creado exitosamente.');
            }

            return back()->with('error', $data['mensaje'] ?? 'Error al crear reporte.')
                ->withInput();

        } catch (\Exception $e) {
            return back()->with('error', 'Error al conectar con la API: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar detalle de un reporte
     */
    public function show($id)
{
    try {
        $token = Session::get('access_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get("http://localhost:3000/api/reportes/{$id}");

        $data = $response->json();

        // Verificar si la API devolvió un error
        if (isset($data['estado']) && $data['estado'] === 'error') {
            return back()->with('error', $data['mensaje'] ?? 'Reporte no encontrado.');
        }

        // La API devuelve el reporte en 'cabecera' cuando es individual
        if (isset($data['cod_reporte'])) {
            $reporte = $data;
        } elseif (isset($data['cabecera']) && is_array($data['cabecera']) && count($data['cabecera']) > 0) {
            $reporte = $data['cabecera'][0];
        } else {
            $reporte = $data;
        }

        // Si $reporte es null, mostrar error
        if (empty($reporte)) {
            return back()->with('error', 'Reporte no encontrado.');
        }

        // Obtener detalle del reporte (opcional)
        $detalle = [];
        try {
            $detalleResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get("http://localhost:3000/api/reportes/{$id}/detalle");
            $detalle = $detalleResponse->json();
        } catch (\Exception $e) {
            $detalle = ['total_lineas' => 0];
        }

        return view('admin.reportes.show', compact('reporte', 'detalle'));

    } catch (\Exception $e) {
        return back()->with('error', 'Error al cargar el reporte: ' . $e->getMessage());
    }
}

    /**
     * Mostrar formulario para editar reporte
     */
    public function edit($id)
    {
        try {
            $token = Session::get('access_token');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get("http://localhost:3000/api/reportes/{$id}");

            $reporte = $response->json();

            return view('admin.reportes.edit', compact('reporte'));
        } catch (\Exception $e) {
            return back()->with('error', 'Reporte no encontrado.');
        }
    }

    /**
     * Actualizar reporte
     */
    public function update(Request $request, $id)
    {
        try {
            $token = Session::get('access_token');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->put("http://localhost:3000/api/reportes/{$id}", [
                'ind_estado' => $request->ind_estado,
                'tot_activo' => $request->tot_activo,
                'tot_pasivo' => $request->tot_pasivo,
                'tot_patrimonio' => $request->tot_patrimonio,
                'mon_utilidad_perdida' => $request->mon_utilidad_perdida,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['estado']) && $data['estado'] === 'ok') {
                return redirect()->route('reportes.index')
                    ->with('success', $data['mensaje'] ?? 'Reporte actualizado exitosamente.');
            }

            return back()->with('error', $data['mensaje'] ?? 'Error al actualizar reporte.')
                ->withInput();

        } catch (\Exception $e) {
            return back()->with('error', 'Error al conectar con la API: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Anular reporte
     */
    public function destroy($id)
    {
        try {
            $token = Session::get('access_token');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->patch("http://localhost:3000/api/reportes/{$id}/estado");

            $data = $response->json();

            if ($response->successful() && isset($data['estado']) && $data['estado'] === 'ok') {
                return redirect()->route('reportes.index')
                    ->with('success', 'Reporte anulado exitosamente.');
            }

            return back()->with('error', $data['mensaje'] ?? 'Error al anular reporte.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al conectar con la API.');
        }
    }
}