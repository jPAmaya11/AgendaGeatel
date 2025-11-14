<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reporte;
use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with([
            'carteras',
            'reportes.cartera',
            'roles.carteras',
            'roles.reportes.cartera',
        ])->get()->map(function ($user) {
            return [
                ...$user->toArray(),
                'effective_carteras' => $user->getEffectiveCarteras(),
                'effective_reportes' => $user->getEffectiveReportes(),
            ];
        });

        $carteras = Cartera::with('reportes')->get();
        $reportes = Reporte::all();
        $roles    = Role::with(['carteras', 'reportes.cartera'])->get();

        return Inertia::render('GestionarUsuarios', [
            'users'     => $users,
            'carteras'  => $carteras,
            'reportes'  => $reportes,
            'roles'     => $roles,
            'success'   => session('success'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'active'    => 'boolean',
            'carteras'  => 'nullable|array',
            'carteras.*' => 'exists:carteras,id',
            'reportes'  => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'active'   => $data['active'],
        ]);

        $user->syncRoles($data['roles'] ?? []);

        // Aplicar lógica de herencia en creación
        $idsCarterasRol = $user->roles->flatMap->carteras->pluck('id')->unique();
        $idsReportesRol = $user->roles->flatMap->reportes->pluck('id')->unique();

        $carterasPersonalizadas = collect($data['carteras'] ?? [])
            ->filter(fn($id) => !$idsCarterasRol->contains($id));

        $reportesPersonalizados = collect($data['reportes'] ?? [])
            ->filter(fn($id) => !$idsReportesRol->contains($id));

        $user->carteras()->sync($carterasPersonalizadas);
        $user->reportes()->sync($reportesPersonalizados);

        return redirect()
            ->route('users.index')
            ->with('success', "Usuario «{$user->name}» creado correctamente.");
    }

    public function update(Request $request, User $user)
    {
        Log::info('Datos recibidos en update()', $request->all());
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => 'nullable|string|min:6',
            'active'    => 'boolean',
            'carteras'  => 'nullable|array',
            'carteras.*' => 'exists:carteras,id',
            'reportes'  => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:roles,id',
        ]);

        $updateData = [
            'name'   => $data['name'],
            'email'  => $data['email'],
            'active' => $data['active'],
        ];

        if (array_key_exists('password', $data) && $data['password']) {
            Log::info("Nueva contraseña para usuario {$user->id}: " . $data['password']);
            $updateData['password'] = bcrypt($data['password']);
        }

        $user->update($updateData);


        $user->syncRoles($data['roles'] ?? []);

        // Recalcular herencias
        $idsCarterasRol = $user->roles->flatMap->carteras->pluck('id')->unique();
        $idsReportesRol = $user->roles->flatMap->reportes->pluck('id')->unique();

        $carterasPersonalizadas = collect($data['carteras'] ?? [])
            ->filter(fn($id) => !$idsCarterasRol->contains($id));

        $reportesPersonalizados = collect($data['reportes'] ?? [])
            ->filter(fn($id) => !$idsReportesRol->contains($id));

        $user->carteras()->sync($carterasPersonalizadas);
        $user->reportes()->sync($reportesPersonalizados);

        return redirect()
            ->route('users.index')
            ->with('success', "Usuario «{$user->name}» actualizado correctamente.");
    }

    public function destroy(Request $request, User $user)
    {
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente.'
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    // No se usa actualmente, pero lo dejamos como utilidad interna
    private function saveUser(User $user, array $data): void
    {
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->active = $data['active'];

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        $user->reportes()->sync($data['reportes'] ?? []);
    }
}
