<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use App\Models\Chip;
use App\Models\Estado;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegistroController extends Controller
{
    public function index()
    {
        $registros = Registro::with(['chip', 'estado', 'usuario'])->get();

        return Inertia::render('registros/index', [
            'registros' => $registros,
        ]);
    }

    public function create()
    {
        $chipsRegistrados = Registro::pluck('chip_id')->toArray();
        $chips = Chip::whereNotIn('id', $chipsRegistrados)
            ->with('compania')
            ->get();

        $estados = Estado::all();
        $usuarios = Usuario::all();

        return Inertia::render('registros/create', [
            'chips' => $chips,
            'estados' => $estados,
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'chip_id' => 'required|exists:chips,id',
            'estado_id' => 'required|exists:estados,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'conteo_cambios' => 'required|integer|min:0',
            'fecha_revision' => 'required|date',
        ]);

        Registro::create($request->all());

        return redirect()->route('registros.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function show($id)
    {
        $registro = Registro::with(['chip', 'estado', 'usuario'])->findOrFail($id);

        return Inertia::render('registros/show', [
            'registro' => $registro,
        ]);
    }

    public function edit($id)
    {
        $registro = Registro::with(['chip', 'estado', 'usuario'])->findOrFail($id);
        $chips = Chip::with('compania')->get();
        $estados = Estado::all();
        $usuarios = Usuario::all();

        return Inertia::render('registros/edit', [
            'registro' => $registro,
            'chips' => $chips,
            'estados' => $estados,
            'usuarios' => $usuarios,
        ]);
    }

    public function update(Request $request, $id)
    {
        $registro = Registro::findOrFail($id);

        $request->validate([
            'chip_id' => 'required|exists:chips,id',
            'estado_id' => 'required|exists:estados,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'conteo_cambios' => 'required|integer|min:0',
            'fecha_revision' => 'required|date',
        ]);

        $registro->update($request->all());

        return redirect()->route('registros.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy($id)
    {
        $registro = Registro::findOrFail($id);
        $registro->delete();

        return redirect()->route('registros.index')
            ->with('success', 'Registro eliminado correctamente.');
    }
}
