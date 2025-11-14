<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompaniaController extends Controller
{
    /**
     * Muestra todas las compañías.
     */
    public function index()
    {
        $companias = Compania::withCount('chips')->get();

        // Archivo en minúscula: resources/js/Pages/companias/index.vue
        return Inertia::render('companias/index', [
            'companias' => $companias,
        ]);
    }

    /**
     * Muestra una compañía específica con sus chips asociados.
     */
    public function show(Compania $compania)
    {
        $compania->load('chips');

        // Archivo en minúscula: resources/js/Pages/companias/show.vue
        return Inertia::render('companias/show', [
            'compania' => $compania,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva compañía.
     */
    public function create()
    {
        // Archivo en minúscula: resources/js/Pages/companias/create.vue
        return Inertia::render('companias/create');
    }

    /**
     * Guarda una nueva compañía en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:companias,nombre',
            'descripcion' => 'nullable|string',
        ]);

        Compania::create($request->all());

        return redirect()->route('companias.index')
                         ->with('success', 'Compañía creada correctamente.');
    }

    /**
     * Muestra el formulario de edición de una compañía.
     */
    public function edit(Compania $compania)
    {
        // Archivo en minúscula: resources/js/Pages/companias/edit.vue
        return Inertia::render('companias/edit', [
            'compania' => $compania,
        ]);
    }

    /**
     * Actualiza una compañía en la base de datos.
     */
    public function update(Request $request, Compania $compania)
    {
        $request->validate([
            'nombre' => 'required|unique:companias,nombre,' . $compania->id,
            'descripcion' => 'nullable|string',
        ]);

        $compania->update($request->all());

        return redirect()->route('companias.index')
                         ->with('success', 'Compañía actualizada correctamente.');
    }

    /**
     * Elimina una compañía de la base de datos.
     */
    public function destroy(Compania $compania)
    {
        $compania->delete();

        return redirect()->route('companias.index')
                         ->with('success', 'Compañía eliminada correctamente.');
    }
}
