<?php

namespace App\Http\Controllers;

use App\Services\PinaxApiService;
use Illuminate\Http\Request;

class AsientosContablesController extends Controller
{
    protected $apiService;

    public function __construct(PinaxApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        $asientos = $this->apiService->get('/asientos') ?? [];
        return view('asientos.asientos_index', compact('asientos'));
    }

    public function create()
    {
        $cuentas = $this->apiService->get('/catalogo') ?? [];
        return view('asientos.asientos_form', compact('cuentas'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'fecha' => 'required|date',
            'glosa' => 'required|string',
            'detalles' => 'required|array',
        ]);

        $response = $this->apiService->post('/asientos', $payload);

        if ($response) {
            return redirect()->route('asientos-contables.index')->with('success', 'Asiento guardado correctamente.');
        }

        return back()->withInput()->withErrors(['error' => 'No se pudo guardar el asiento mediante la API.']);
    }

    public function show($id)
    {
        $asiento = $this->apiService->get("/asientos/{$id}");
        return view('asientos.asientos_show', compact('asiento'));
    }

    public function edit($id)
    {
        $asiento = $this->apiService->get("/asientos/{$id}");
        $cuentas = $this->apiService->get('/catalogo') ?? [];
        return view('asientos.asientos_form', compact('asiento', 'cuentas'));
    }

    public function update(Request $request, $id)
    {
        $payload = $request->validate([
            'fecha' => 'required|date',
            'glosa' => 'required|string',
            'detalles' => 'required|array',
        ]);

        $this->apiService->put("/asientos/{$id}", $payload);

        return redirect()->route('asientos-contables.index')->with('success', 'Asiento actualizado correctamente.');
    }

    public function destroy($id)
    {
        $this->apiService->delete("/asientos/{$id}");
        return redirect()->route('asientos-contables.index')->with('success', 'Asiento eliminado.');
    }
}