<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Carga el dashboard principal sin selección específica.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return Inertia::render('Dashboard');
    }

    /**
     * Carga el dashboard con un reporte específico y/o cartera preseleccionada.
     */
    public function showReporte($id, Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return Inertia::render('Dashboard', [
            'selectedReporteId' => (int) $id,
            'selectedCarteraId' => $request->query('cartera')
                ? (int) $request->query('cartera')
                : null,
        ]);
    }
}
