<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $userId = auth()->id();

        $data = $request->validate([
            // la categoría debe ser única por usuario, no global
            'name'  => [
                'required',
                'string',
                'max:100',
                // unique:categories,name,NULL,id,user_id,<id>
                'unique:categories,name,NULL,id,user_id,' . $userId,
            ],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        Category::create([
            'user_id' => $userId,
            'name'    => $data['name'],
            'color'   => $data['color'] ?? null,
        ]);

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function destroy(Category $category)
    {
        // Asegurar que la categoría pertenece al usuario logueado
        if ($category->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar esta categoría.');
        }

        // Quitar la categoría SOLO de las notas del usuario actual
        Note::where('user_id', auth()->id())
            ->where('category', $category->name)
            ->update(['category' => null]);

        $category->delete();

        return back()->with('success', 'Categoría eliminada correctamente.');
    }
}
