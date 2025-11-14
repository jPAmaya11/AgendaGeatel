<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportesController extends Controller
{
    // Mostrar lista de carteras y reportes
    public function index()
    {
        $reportes = Reporte::with('cartera')->get();
        $carteras = Cartera::all();

        return Inertia::render('GestionarReportes', [
            'reportes' => $reportes,
            'carteras' => $carteras,
            'success' => session('success'),
        ]);
    }

    // Crear un nuevo reporte
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'link_desktop' => 'required|string|max:255',
            'link_mobile' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'orden' => 'nullable|integer',
            'cartera_id' => 'required|exists:carteras,id',
        ]);

        $reporte = Reporte::create([
            'nombre' => $request->nombre,
            'link_desktop' => $request->link_desktop,
            'link_mobile' => $request->link_mobile,
            'icon' => $request->icon,
            'orden' => $request->orden,
            'cartera_id' => $request->cartera_id,
        ]);

        return response()->json([
            'message' => "Reporte «{$reporte->nombre}» creado correctamente.",
            'reporte' => $reporte->load('cartera'),
        ]);
    }


    // Actualizar un reporte
    public function update(Request $request, Reporte $reporte)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'link_desktop' => 'required|string|max:255',
            'link_mobile' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'orden' => 'nullable|integer',
            'cartera_id' => 'required|exists:carteras,id',
        ]);

        $reporte->update([
            'nombre' => $request->nombre,
            'link_desktop' => $request->link_desktop,
            'link_mobile' => $request->link_mobile,
            'icon' => $request->icon,
            'orden' => $request->orden,
            'cartera_id' => $request->cartera_id,
        ]);

        return response()->json([
             'message' => "Reporte «{$reporte->nombre}»  actualizado correctamente.",
            'reporte' => $reporte->load('cartera'),
        ]);
    }

    // Eliminar un reporte
    public function destroy(Reporte $reporte)
    {
        $nombre = $reporte->nombre;
        $reporte->delete();

        return response()->json([
            'message' => "Reporte «{$nombre}» eliminado correctamente.",
            'id' => $reporte->id,
        ]);
    }


}
