<?php

namespace App\Http\Controllers;

use App\Models\Chip;
use App\Models\Compania;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChipController extends Controller
{
    public function index()
    {
        $chips = Chip::with('compania')->get();
        return Inertia::render('chips/index', [
            'chips' => $chips,
        ]);
    }

    public function create()
    {
        $companias = Compania::all();
        return Inertia::render('chips/create', [
            'companias' => $companias,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => 'required|unique:chips,numero',
            'compania_id' => 'required|exists:companias,id',
        ]);

        Chip::create($validated);

        return redirect()->route('chips.index');
    }

    public function show(Chip $chip)
    {
        $chip->load('compania');
        return Inertia::render('chips/show', [
            'chip' => $chip,
        ]);
    }

    public function edit(Chip $chip)
    {
        $companias = Compania::all();
        return Inertia::render('chips/edit', [
            'chip' => $chip,
            'companias' => $companias,
        ]);
    }

    public function update(Request $request, Chip $chip)
    {
        $validated = $request->validate([
            'numero' => 'required|unique:chips,numero,' . $chip->id,
            'compania_id' => 'required|exists:companias,id',
        ]);

        $chip->update($validated);

        return redirect()->route('chips.index');
    }

    public function destroy(Chip $chip)
    {
        $chip->delete();
        return redirect()->route('chips.index');
    }
}
