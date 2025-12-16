<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;

class NoteShareController extends Controller
{
    protected function ensureOwner(Note $note)
    {
        if ($note->owner_id !== auth()->id()) {
            abort(403, "No tienes permiso para administrar esta nota.");
        }
    }

    public function index(Note $note, Request $request)
    {
        $user = $request->user();

        if ($note->owner_id !== $user->id && !$note->sharedWith()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        return response()->json(
            $note->sharedWith->map(fn ($u) => [
                'id'       => $u->id,
                'name'     => $u->name,
                'email'    => $u->email,
                'can_edit' => (bool) $u->pivot->can_edit,
            ])
        );
    }

    public function store(Request $request, Note $note)
    {
        $user = $request->user();

        if ($note->owner_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'email'    => ['required', 'email'],
            'can_edit' => ['required', 'boolean'],
        ]);

        $target = User::where('email', $data['email'])->first();

        if (!$target) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 422);
        }

        if ($target->id === $user->id) {
            return response()->json([
                'message' => 'No puedes compartir contigo mismo'
            ], 422);
        }

        $note->sharedWith()->syncWithoutDetaching([
            $target->id => ['can_edit' => $data['can_edit']],
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Note $note, User $user, Request $request)
    {
        if ($note->owner_id !== $request->user()->id) {
            abort(403);
        }

        $note->sharedWith()->detach($user->id);

        return response()->json(['success' => true]);
    }
}
