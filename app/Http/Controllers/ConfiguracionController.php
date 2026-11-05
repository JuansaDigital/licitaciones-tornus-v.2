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
        $conAlertasEmail = $users->where('notificar_email', true)->count();

        // Métricas de Consumo de Inteligencia Artificial (Tokens y Costos Gemini)
        $todayStr = now()->toDateString();
        $thirtyDaysAgo = now()->subDays(30)->toDateString();

        $tokensHoy = \App\Models\AiTokenUsage::whereDate('date', $todayStr)->sum('total_tokens');
        $costoHoy = \App\Models\AiTokenUsage::whereDate('date', $todayStr)->sum('cost_usd');
        $llamadasHoy = \App\Models\AiTokenUsage::whereDate('date', $todayStr)->count();

        $tokensMes = \App\Models\AiTokenUsage::where('date', '>=', $thirtyDaysAgo)->sum('total_tokens');
        $costoMes = \App\Models\AiTokenUsage::where('date', '>=', $thirtyDaysAgo)->sum('cost_usd');
        $llamadasMes = \App\Models\AiTokenUsage::where('date', '>=', $thirtyDaysAgo)->count();

        // Desglose diario de los últimos 7 días
        $historialDiario = \App\Models\AiTokenUsage::where('date', '>=', now()->subDays(7)->toDateString())
            ->selectRaw('date, count(*) as llamadas, sum(prompt_tokens) as prompt_tokens, sum(candidate_tokens) as candidate_tokens, sum(total_tokens) as total_tokens, sum(cost_usd) as cost_usd')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $aiModel = env('GEMINI_MODEL', 'gemini-3.8-flash');

        // Configuración de frecuencia y límites de consulta de datos (SECOP / OECE)
        $syncConfig = \App\Services\SyncConfigService::getConfig();
        $syncFrequencies = \App\Services\SyncConfigService::FREQUENCIES;

        return view('configuracion', compact(
            'users',
            'totalMiembros',
            'usuariosActivos',
            'administradores',
            'conAlertasEmail',
            'tokensHoy',
            'costoHoy',
            'llamadasHoy',
            'tokensMes',
            'costoMes',
            'llamadasMes',
            'historialDiario',
            'aiModel',
            'syncConfig',
            'syncFrequencies'
        ));
    }

    /**
     * Activar o pausar avisos por correo para un miembro
     */
    public function toggleNotification(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);

        if (!Auth::user()->isAdmin() && Auth::id() !== $targetUser->id) {
            return back()->withErrors(['error' => 'No tienes permisos para modificar las alertas de este usuario.']);
        }

        $targetUser->notificar_email = !$targetUser->notificar_email;
        $targetUser->save();

        $action = $targetUser->notificar_email ? 'activadas' : 'desactivadas';
        return back()->with('status', "Alertas por correo {$action} para {$targetUser->name}.");
    }

    /**
     * Actualizar criterios y preferencias de alertas por correo
     */
    public function updateNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'notificar_email' => ['nullable', 'boolean'],
            'notificar_criterio' => ['required', 'in:viables,todas,afinidad_alta,prioritarias'],
            'notificar_mercado' => ['required', 'in:TODOS,CO,PE'],
        ]);

        $user = Auth::user();
        $user->notificar_email = $request->has('notificar_email');
        $user->notificar_criterio = $validated['notificar_criterio'];
        $user->notificar_mercado = $validated['notificar_mercado'];
        $user->save();

        return back()->with('status', 'Preferencias de alertas por correo actualizadas correctamente.');
    }

    /**
     * Enviar correo de prueba inmediato al usuario autenticado
     */
    public function sendTestEmail(Request $request)
    {
        try {
            \App\Services\AlertNotificationService::sendTestEmail(Auth::user());
            return back()->with('status', 'Correo de prueba enviado exitosamente a ' . Auth::user()->email . '. Revisa tu bandeja de entrada.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'No se pudo enviar el correo de prueba: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar parámetros de frecuencia y cuota de consultas a SECOP II / OECE
     */
    public function updateSyncConfig(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores pueden modificar los parámetros de sincronización.']);
        }

        $validated = $request->validate([
            'frequency' => ['required', 'string', 'in:hourly,everyTwoHours,everyFourHours,everySixHours,twiceDaily,daily'],
            'daily_manual_limit' => ['required', 'integer', 'min:1', 'max:100']
        ]);

        \App\Services\SyncConfigService::saveConfig(
            $validated['frequency'],
            (int) $validated['daily_manual_limit']
        );

        return back()->with('status', 'Parámetros de frecuencia y límites de consulta a SECOP II actualizados correctamente.');
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

