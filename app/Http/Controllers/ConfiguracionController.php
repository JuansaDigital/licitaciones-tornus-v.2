<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ConfiguracionController extends Controller
{
    /**
     * Panel de configuración y gestión de usuarios del equipo
     */
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();

        $totalMiembros = $users->count();
        $usuariosActivos = $users->where('activo', true)->count();
        $administradores = $users->where('rol', 'administrador')->count();

        return view('configuracion', compact(
            'users',
            'totalMiembros',
            'usuariosActivos',
            'administradores'
        ));
    }

    /**
     * Registrar un nuevo miembro del equipo (solo Admin)
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores pueden registrar nuevos miembros.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'rol' => ['required', 'in:administrador,colaborador'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'activo' => true,
            'ultimo_acceso' => null,
        ]);

        return back()->with('status', 'Miembro agregado exitosamente al equipo.');
    }

    /**
     * Asignar o cambiar clave de un miembro
     */
    public function updatePassword(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);

        // Solo el propio usuario o un administrador pueden modificar la clave
        if (!Auth::user()->isAdmin() && Auth::id() !== $targetUser->id) {
            return back()->withErrors(['error' => 'No tienes permisos para modificar esta clave.']);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $targetUser->password = Hash::make($validated['password']);
        $targetUser->save();

        return back()->with('status', "Contraseña de {$targetUser->name} actualizada correctamente.");
    }

    /**
     * Activar o desactivar acceso de un miembro
     */
    public function toggleStatus(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores pueden cambiar el estado de acceso.']);
        }

        $targetUser = User::findOrFail($id);

        if (Auth::id() === $targetUser->id) {
            return back()->withErrors(['error' => 'No puedes desactivar tu propia cuenta administradora.']);
        }

        $targetUser->activo = !$targetUser->activo;
        $targetUser->save();

        $action = $targetUser->activo ? 'activado' : 'desactivado';
        return back()->with('status', "El acceso de {$targetUser->name} ha sido {$action}.");
    }

    /**
     * Eliminar usuario del equipo
     */
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores pueden eliminar miembros.']);
        }

        $targetUser = User::findOrFail($id);

        if (Auth::id() === $targetUser->id) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta administradora.']);
        }

        $nombre = $targetUser->name;
        $targetUser->delete();

        return back()->with('status', "El usuario {$nombre} ha sido eliminado del sistema.");
    }
}

