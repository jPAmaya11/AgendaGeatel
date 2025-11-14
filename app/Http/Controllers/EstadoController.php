<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EstadoController extends Controller
{
    /**
     * Muestra todos los estados.
     */
    public function index()
    {
        $estados = Estado::all();
        return Inertia::render('estados/index', [
            'estados' => $estados,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo estado.
     */
    public function create()
    {
        return Inertia::render('estados/create');
    }

    /**
     * Guarda un nuevo estado en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:estados,nombre',
            'descripcion' => 'nullable|string',
        ]);

        Estado::create($request->all());

        return redirect()->route('estados.index')
            ->with('success', 'Estado creado correctamente.');
    }

    /**
     * Muestra un estado específico.
     */
    public function show(Estado $estado)
    {
        return Inertia::render('estados/show', [
            'estado' => $estado,
        ]);
    }

    /**
     * Muestra el formulario para editar un estado.
     */
    public function edit(Estado $estado)
    {
        return Inertia::render('estados/edit', [
            'estado' => $estado,
        ]);
    }

    /**
     * Actualiza un estado en la base de datos.
     */
    public function update(Request $request, Estado $estado)
    {
        $request->validate([
            'nombre' => 'required|unique:estados,nombre,' . $estado->id,
            'descripcion' => 'nullable|string',
        ]);

        $estado->update($request->all());

        return redirect()->route('estados.index')
            ->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Elimina un estado.
     */
    public function destroy(Estado $estado)
    {
        $estado->delete();

        return redirect()->route('estados.index')
            ->with('success', 'Estado eliminado correctamente.');
    }
}
