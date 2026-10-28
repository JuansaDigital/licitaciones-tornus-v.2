@extends('layouts.app')

@section('title', 'Base de Conocimiento · Tornus Agency')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <div class="p-3.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono flex items-center justify-between">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Contenedor Principal del Perfil -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-6 sm:p-8 shadow-xs">
        
        <!-- Encabezado con Icono y Estado -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-6 border-b border-[#e3dbc2]">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#00232c]/5 border border-[#e3dbc2] flex items-center justify-center text-[#00232c] shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </div>
                <div>
                    <h1 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal tracking-tight">
                        Base de Conocimiento de Tornus Agency (Contexto para Gemini)
                    </h1>
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-mono text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full shrink-0 self-start sm:self-auto">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                <span>Perfil base activo</span>
            </div>
        </div>

        <p class="text-xs font-mono text-[#56686b] mt-4 mb-6 leading-relaxed">
            Esta información define la identidad, portafolio y criterios de Tornus. Cuando le des a <strong class="text-[#00232c]">"Analizar con Gemini"</strong> en cualquier licitación, la IA evaluará el proyecto contrastándolo con estos datos.
        </p>

        <!-- Formulario de Configuración de Perfil -->
        <form action="{{ route('perfil.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Fila 1: Datos Básicos -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                        NOMBRE DE LA AGENCIA
                    </label>
                    <input type="text" name="nombre_agencia" value="{{ old('nombre_agencia', $profile['nombre_agencia'] ?? 'Tornus Agency') }}" required
                        class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-sans">
                </div>

                <div>
                    <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                        SITIO WEB CORPORATIVO
                    </label>
                    <input type="url" name="sitio_web" value="{{ old('sitio_web', $profile['sitio_web'] ?? 'https://tornus.agency') }}"
                        class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-sans">
                </div>

                <div>
                    <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                        INSTAGRAM OFICIAL
                    </label>
                    <input type="text" name="instagram" value="{{ old('instagram', $profile['redes_sociales']['instagram'] ?? 'https://instagram.com/tornusagency') }}"
                        class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-sans">
                </div>
            </div>

            <!-- Fila 2: LinkedIn -->
            <div class="max-w-md">
                <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                    LINKEDIN CORPORATIVO
                </label>
                <input type="text" name="linkedin" value="{{ old('linkedin', $profile['redes_sociales']['linkedin'] ?? 'https://linkedin.com/company/tornusagency') }}"
                    class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-sans">
            </div>

            <!-- Fila 3: Propuesta de Valor y Foco de Mercado -->
            <div>
                <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                    PROPUESTA DE VALOR Y FOCO DE MERCADO
                </label>
                <textarea name="propuesta_valor" rows="3" required
                    class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-sans leading-relaxed">{{ old('propuesta_valor', $profile['propuesta_valor'] ?? '') }}</textarea>
            </div>

            <!-- Fila 4: Servicios Principales -->
            <div>
                <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                    SERVICIOS PRINCIPALES (UNO POR LÍNEA)
                </label>
                <textarea name="servicios_principales" rows="6" required
                    class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-mono text-xs leading-relaxed">{{ old('servicios_principales', isset($profile['servicios_principales']) ? implode("\n", $profile['servicios_principales']) : '') }}</textarea>
            </div>

            <!-- Fila 5: Entidades Foco y Criterios de Exclusión (2 Columnas) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                        ENTIDADES FOCO / CLIENTES IDEALES (UNO POR LÍNEA)
                    </label>
                    <textarea name="entidades_objetivo" rows="5"
                        class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-mono text-xs leading-relaxed">{{ old('entidades_objetivo', isset($profile['entidades_objetivo']) ? implode("\n", $profile['entidades_objetivo']) : '') }}</textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                        CRITERIOS DE EXCLUSIÓN (LO QUE NO HACEMOS - UNO POR LÍNEA)
                    </label>
                    <textarea name="criterios_exclusion" rows="5"
                        class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-mono text-xs leading-relaxed">{{ old('criterios_exclusion', isset($profile['criterios_exclusion']) ? implode("\n", $profile['criterios_exclusion']) : '') }}</textarea>
                </div>
            </div>

            <!-- Fila 6: Rango de Presupuesto y Notas -->
            <div>
                <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                    RANGO DE PRESUPUESTO Y NOTAS ESTRATÉGICAS
                </label>
                <input type="text" name="rango_presupuesto_ideal" value="{{ old('rango_presupuesto_ideal', $profile['rango_presupuesto_ideal'] ?? '') }}"
                    class="w-full px-3.5 py-2.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] focus:bg-white font-sans">
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#e3dbc2]">
                <a href="{{ route('radar') }}" class="px-5 py-2.5 border border-[#e3dbc2] bg-white rounded text-xs font-mono font-bold tracking-wider text-[#56686b] hover:bg-[#f7f2df] transition-colors">
                    CANCELAR
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#00232c] hover:bg-[#0b4a57] text-[#fbfb75] rounded text-xs font-mono font-bold tracking-wider flex items-center gap-2 shadow-xs transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>GUARDAR PERFIL DE TORNUS</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
