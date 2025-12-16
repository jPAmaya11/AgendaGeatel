<?php

namespace App\Http\Controllers;

use App\Models\Notebook;
use Illuminate\Http\Request;

class NotebookController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notebooks = Notebook::where('owner_id', $user->id)
            ->orderBy('title')
            ->get();

        return inertia('Notes/Notebooks', [
            'notebooks' => $notebooks,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $data['owner_id'] = $user->id;

        Notebook::create($data);

        return back()->with('success', 'Libreta creada correctamente');
    }

    public function update(Request $request, Notebook $notebook)
    {
        $user = $request->user();

        if ($notebook->owner_id !== $user->id) {
            abort(403, 'No puedes editar esta libreta');
        }

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $notebook->update($data);

        return back()->with('success', 'Libreta actualizada');
    }

    public function destroy(Request $request, Notebook $notebook)
    {
        $user = $request->user();

        if ($notebook->owner_id !== $user->id) {
            abort(403, 'No puedes eliminar esta libreta');
        }

        $notebook->delete();

        return back()->with('success', 'Libreta eliminada');
    }
}
