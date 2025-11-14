<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarterasController extends Controller
{
    public function index()
    {
        $carteras = Cartera::all();
        $reportes = \App\Models\Reporte::with('cartera')->get();

        return Inertia::render('GestionarCarteras', [
            'carteras' => $carteras,
            'reportes' => $reportes,
            'success' => session('success'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
            'estado' => 'required|boolean',
        ]);

        $cartera = Cartera::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cartera creada correctamente.',
                'cartera' => $cartera,
            ]);
        }

        return redirect()->route('carteras.index')->with('success', 'Cartera creada correctamente.');
    }

    public function update(Request $request, Cartera $cartera)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
            'estado' => 'required|boolean',
        ]);

        $cartera->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cartera actualizada correctamente.',
                'cartera' => $cartera,
            ]);
        }

        return redirect()->route('carteras.index')->with('success', 'Cartera actualizada correctamente.');
    }

    public function destroy(Request $request, Cartera $cartera)
    {
        $cartera->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cartera eliminada correctamente.',
            ]);
        }

        return redirect()->route('carteras.index')->with('success', 'Cartera eliminada correctamente.');
    }
}
