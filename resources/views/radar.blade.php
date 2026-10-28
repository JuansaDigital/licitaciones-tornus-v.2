@extends('layouts.app')

@section('title', 'Radar de Licitaciones')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <div class="p-3.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono flex items-center justify-between">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Encabezado y Filtros Rápidos -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h1 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal">
                    Radar en Vivo · {{ App\Services\MarketService::marketName() }}
                </h1>
                <p class="text-xs font-mono text-[#56686b] mt-0.5">
                    {{ number_format($totalCount) }} procesos registrados en {{ App\Services\MarketService::sourceName() }}
                </p>
            </div>

            <!-- Botón de actualización de radar -->
            <form action="{{ route('radar.sync') }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.3"/>
                    </svg>
                    <span>Sincronizar Radar</span>
                </button>
            </form>
        </div>

        <!-- Formulario de Búsqueda y Región -->
        <form action="{{ route('radar') }}" method="GET" class="space-y-4">
            <input type="hidden" name="urgency" value="{{ $selectedUrgency }}">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-8 relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por objeto, palabras clave o entidad..."
                        class="w-full px-3.5 py-2.5 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] placeholder-[#9db3b4] focus:outline-none focus:border-[#00232c] focus:ring-1 focus:ring-[#00232c] transition-colors font-sans">
                </div>
                <div class="md:col-span-3">
                    <select name="region" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-white border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <option value="">Todas las regiones / departamentos</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg }}" {{ $selectedRegion == $reg ? 'selected' : '' }}>{{ $reg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <button type="submit" class="w-full h-full py-2.5 bg-[#0b4a57] hover:bg-[#00232c] text-white rounded text-xs font-mono font-medium transition-colors">
                        Filtrar
                    </button>
                </div>
            </div>

            <!-- FILTROS DE TIEMPO Y VENCIMIENTO (SEMÁFORO DE URGENCIAS) -->
            <div class="pt-3 border-t border-[#e3dbc2] flex flex-wrap items-center gap-2 font-mono text-xs">
                <span class="text-[#56686b] font-semibold mr-1">PLAZO DE CIERRE:</span>
                
                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'all'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors {{ $selectedUrgency === 'all' ? 'border-[#00232c] bg-[#00232c] text-[#fbfb75] font-semibold' : 'border-[#e3dbc2] bg-white text-[#56686b] hover:bg-[#f7f2df]' }}">
                    Todos los plazos
                </a>
                
                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'critical'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedUrgency === 'critical' ? 'border-rose-400 bg-rose-100 text-rose-900 font-bold' : 'border-rose-200 bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                    <span>Crítico: &lt; 48 horas</span>
                </a>

                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'week'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedUrgency === 'week' ? 'border-amber-400 bg-amber-100 text-amber-900 font-bold' : 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Esta semana: 3 a 7 días</span>
                </a>

                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'ideal'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedUrgency === 'ideal' ? 'border-emerald-400 bg-emerald-100 text-emerald-900 font-bold' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>Con tiempo: &gt; 7 días</span>
                </a>

                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'recent'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedUrgency === 'recent' ? 'border-blue-400 bg-blue-100 text-blue-900 font-bold' : 'border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Recién convocada: &lt; 24h</span>
                </a>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- ACORDEÓN 1: PALABRAS CLAVE DEL RADAR                                      -->
    <!-- ========================================================================= -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg shadow-xs overflow-hidden">
        
        <!-- Botón de apertura/cierre del acordeón -->
        <button type="button" onclick="toggleAccordion('accordion-keywords', 'chevron-keywords')" 
            class="w-full p-5 text-left flex flex-col sm:flex-row sm:items-center justify-between gap-2 hover:bg-[#f7f2df]/50 transition-colors cursor-pointer">
            <div class="flex items-center gap-3">
                <h2 class="font-['Instrument_Serif',serif] text-xl sm:text-2xl text-[#00232c] font-normal tracking-tight uppercase">
                    PALABRAS CLAVE DEL RADAR
                </h2>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#f7f2df] text-[#56686b] border border-[#e3dbc2] font-semibold">
                    {{ count($keywords) }} activas
                </span>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#56686b]">
                <span>Términos de búsqueda en {{ App\Services\MarketService::sourceName() }}. Toca para ver, agregar o editar.</span>
                <svg id="chevron-keywords" class="w-4 h-4 text-[#00232c] transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>
        </button>

        <!-- Contenido desplegable del acordeón -->
        <div id="accordion-keywords" class="p-5 pt-0 border-t border-[#f2ece0] space-y-5">
            <p class="text-xs font-mono text-[#56686b] pt-4">
                El Radar busca cada término en el nombre, la descripción o la entidad del proceso. Agrega, edita o elimina términos y luego vuelve a consultar {{ App\Services\MarketService::sourceName() }}.
            </p>

            <!-- Formulario para Agregar Nueva Palabra Clave -->
            <form action="{{ route('radar.keywords.add') }}" method="POST" class="space-y-2">
                @csrf
                <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b]">
                    NUEVA PALABRA O FRASE
                </label>
                <div class="flex flex-col sm:flex-row items-center gap-2 max-w-xl">
                    <input type="text" name="keyword" required placeholder="Ej.: turismo comunitario"
                        class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] placeholder-[#9db3b4] focus:outline-none focus:border-[#00232c]">
                    <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold tracking-wider whitespace-nowrap transition-colors shadow-xs cursor-pointer">
                        AGREGAR PALABRA CLAVE
                    </button>
                </div>
            </form>

            <!-- Rejilla de Palabras Clave Configuradas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 pt-2">
                @foreach($keywords as $kw)
                    <div class="bg-white border border-[#e3dbc2] rounded p-2.5 flex items-center justify-between gap-2 shadow-2xs hover:border-[#00232c] transition-colors">
                        <a href="{{ route('radar', ['q' => $kw]) }}" 
                            class="font-mono text-xs font-bold text-[#00232c] hover:underline truncate"
                            title="Filtrar radar por: {{ $kw }}">
                            {{ $kw }}
                        </a>
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" onclick="openEditKeywordModal('{{ addslashes($kw) }}')" 
                                class="px-2 py-0.5 rounded text-[10px] font-mono text-[#56686b] hover:text-[#00232c] hover:bg-[#f7f2df] border border-transparent hover:border-[#e3dbc2] transition-colors cursor-pointer">
                                EDITAR
                            </button>
                            <form action="{{ route('radar.keywords.remove') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="keyword" value="{{ $kw }}">
                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-mono text-[#56686b] hover:text-rose-700 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors cursor-pointer">
                                    ELIMINAR
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Barra inferior con totales y botón para restablecer -->
            <div class="pt-4 border-t border-[#f2ece0] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs font-mono">
                <span class="text-[#56686b]">
                    {{ count($keywords) }} de 50 palabras o frases configuradas.
                </span>

                <div class="flex items-center gap-2">
                    <form action="{{ route('radar.filters.reset') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded border border-[#e3dbc2] bg-white text-[#56686b] hover:bg-[#f7f2df] transition-colors cursor-pointer">
                            RESTABLECER PALABRAS PREDETERMINADAS
                        </button>
                    </form>

                    <form action="{{ route('radar.sync') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] font-semibold transition-colors shadow-xs cursor-pointer">
                            GUARDAR Y ACTUALIZAR RADAR
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- ACORDEÓN 2: PALABRAS A EXCLUIR EN EL RADAR (ANTI-FALSOS POSITIVOS)        -->
    <!-- ========================================================================= -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg shadow-xs overflow-hidden">
        
        <!-- Botón de apertura/cierre del acordeón -->
        <button type="button" onclick="toggleAccordion('accordion-exclusions', 'chevron-exclusions')" 
            class="w-full p-5 text-left flex flex-col sm:flex-row sm:items-center justify-between gap-2 hover:bg-[#f7f2df]/50 transition-colors cursor-pointer">
            <div class="flex items-center gap-3">
                <h2 class="font-['Instrument_Serif',serif] text-xl sm:text-2xl text-[#00232c] font-normal tracking-tight uppercase">
                    PALABRAS A EXCLUIR EN EL RADAR
                </h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-rose-50 text-rose-700 border border-rose-200 font-semibold">
                    {{ count($exclusions) }} términos
                </span>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#56686b]">
                <span>Filtro de exclusión automática (policía, militar, aseo, combustible, dotaciones). Toca para ver o modificar.</span>
                <svg id="chevron-exclusions" class="w-4 h-4 text-[#00232c] transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>
        </button>

        <!-- Contenido desplegable del acordeón -->
        <div id="accordion-exclusions" class="p-5 pt-0 border-t border-[#f2ece0] space-y-5">
            <p class="text-xs font-mono text-[#56686b] pt-4">
                Cualquier licitación cuyo objeto o entidad contenga una de estas palabras será descartada automáticamente del Radar. Agrega o elimina términos según el foco de Tornus.
            </p>

            <!-- Formulario para Agregar Término de Exclusión -->
            <form action="{{ route('radar.exclusions.add') }}" method="POST" class="space-y-2">
                @csrf
                <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b]">
                    NUEVA PALABRA O TÉRMINO A EXCLUIR
                </label>
                <div class="flex flex-col sm:flex-row items-center gap-2 max-w-xl">
                    <input type="text" name="exclusion" required placeholder="Ej.: vigilancia, policía, dotación"
                        class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] placeholder-[#9db3b4] focus:outline-none focus:border-[#00232c]">
                    <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold tracking-wider whitespace-nowrap transition-colors shadow-xs cursor-pointer">
                        AGREGAR EXCLUSIÓN
                    </button>
                </div>
            </form>

            <!-- Rejilla de Términos Excluidos -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 pt-2">
                @foreach($exclusions as $ex)
                    <div class="bg-white border border-[#e3dbc2] rounded p-2.5 flex items-center justify-between gap-1.5 shadow-2xs">
                        <span class="font-mono text-xs font-bold text-[#00232c] truncate">
                            {{ $ex }}
                        </span>
                        <form action="{{ route('radar.exclusions.remove') }}" method="POST" class="shrink-0 inline">
                            @csrf
                            <input type="hidden" name="exclusion" value="{{ $ex }}">
                            <button type="submit" class="text-[10px] font-mono text-rose-700 hover:text-rose-900 transition-colors font-medium cursor-pointer">
                                × QUITAR
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <!-- Lista Dinámica de Licitaciones -->
    @if ($licitaciones->isEmpty())
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-10 text-center text-[#56686b] font-mono text-xs">
            No se encontraron licitaciones vigentes con los criterios de búsqueda seleccionados.
        </div>
    @else
        <div class="space-y-4">
            @foreach($licitaciones as $lic)
                @php
                    $urgency = $lic->urgency_info;
                    $borderClass = $lic->score !== null 
                        ? ($lic->score >= 70 ? 'border-l-4 border-l-emerald-600' : ($lic->score >= 40 ? 'border-l-4 border-l-amber-500' : 'border-l-4 border-l-rose-500')) 
                        : 'border-l-4 border-l-[#00232c]';
                @endphp
                <div class="bg-[#fffdf6] border border-[#e3dbc2] {{ $borderClass }} rounded-lg p-5 shadow-xs hover:border-[#0b4a57] transition-all">
                    
                    <!-- Fila Superior de Badges y Cuantía -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-2.5">
                        <div class="flex flex-wrap items-center gap-1.5 font-mono text-[11px]">
                            <!-- Badge de Vencimiento -->
                            <span class="px-2.5 py-0.5 rounded border {{ $urgency['class'] }}">
                                {{ $urgency['badge'] }}
                            </span>

                            <!-- Departamento -->
                            <span class="px-2.5 py-0.5 rounded bg-[#00232c] text-[#fbfb75] font-semibold">
                                {{ $lic->departamento ?: 'Nacional' }}
                            </span>

                            <!-- Categoría -->
                            <span class="px-2.5 py-0.5 rounded bg-[#f7f2df] text-[#56686b] border border-[#e3dbc2]">
                                {{ $lic->categoria ?: 'Servicios Generales' }}
                            </span>

                            <!-- Estado / Fase -->
                            <span class="px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>{{ $lic->fase ?: ($lic->estado ?: 'Convocado · Recibiendo ofertas') }}</span>
                            </span>

                            <!-- Plazo de Ejecución -->
                            <span class="px-2 py-0.5 rounded text-[#56686b] flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>{{ $lic->prov_unicos ? $lic->prov_unicos . ' día(s)' : '60 día(s)' }}</span>
                            </span>

                            <!-- Vistas / Competencia -->
                            <span class="px-2 py-0.5 rounded text-[#56686b]">
                                1 vistas
                            </span>
                        </div>

                        <!-- Presupuesto Oficial & Cierre -->
                        <div class="text-right shrink-0">
                            <div class="font-bold text-lg text-[#00232c] tracking-tight">
                                {{ $lic->formatted_cuantia }}
                            </div>
                            <div class="text-[11px] font-mono text-[#56686b]">
                                Recepción de ofertas: <strong class="text-[#00232c]">{{ $lic->fecha_cierre ? date('d M', strtotime($lic->fecha_cierre)) : 'Pendiente' }}</strong>
                            </div>
                            <div class="text-[10px] font-mono text-[#9db3b4] mt-0.5">
                                {{ $lic->referencia ?: $lic->external_id }}
                            </div>
                        </div>
                    </div>

                    <!-- Sub-badges de Modalidad -->
                    <div class="flex items-center gap-2 mb-2 font-mono text-[10px]">
                        <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-800 border border-sky-200 font-semibold">
                            {{ $lic->tipo_proceso ?: 'Convocatoria Pública' }}
                        </span>
                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200">
                            Relacionado
                        </span>
                    </div>

                    <!-- Título / Objeto Contractual -->
                    <h3 class="text-base font-semibold text-[#00232c] mb-2 leading-snug">
                        {{ $lic->objeto }}
                    </h3>

                    <!-- Entidad y Ubicación -->
                    <div class="text-xs font-mono text-[#56686b] mb-4 flex flex-wrap items-center gap-3">
                        <span class="font-bold text-[#00232c]">{{ $lic->departamento ?: 'Nacional' }}</span>
                        <span>{{ $lic->entidad }}</span>
                    </div>

                    <!-- Fila de Acciones: Botones Ficha, Analizar con Gemini y Cartera -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[#f2ece0]">
                        
                        <!-- Afinidad Evaluada en la Tarjeta -->
                        <div id="card-affinity-{{ $lic->id }}" class="flex items-center gap-2">
                            @if($lic->score !== null)
                                <span class="px-2.5 py-1 rounded text-xs font-mono font-bold flex items-center gap-1.5 {{ $lic->score >= 70 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($lic->score >= 40 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-rose-50 text-rose-800 border border-rose-200') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $lic->score >= 70 ? 'bg-emerald-600' : ($lic->score >= 40 ? 'bg-amber-600' : 'bg-rose-600') }}"></span>
                                    <span>{{ $lic->score }}% AFINIDAD · {{ $lic->nivel }}</span>
                                </span>
                            @endif
                        </div>

                        <!-- Botones de Acción Operativa -->
                        <div class="flex flex-wrap items-center gap-2">
                            
                            @if($lic->url_proceso)
                                <a href="{{ $lic->url_proceso }}" target="_blank" rel="noopener noreferrer" 
                                    class="px-3 py-1.5 text-xs font-mono text-[#00232c] bg-white border border-[#e3dbc2] hover:bg-[#f7f2df] rounded transition-colors flex items-center gap-1.5 font-medium">
                                    <span>Ver en {{ App\Services\MarketService::sourceName() }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                </a>
                            @endif

                            <!-- Botón Abrir Ficha Técnica -->
                            <button type="button" onclick="openFichaModal({{ json_encode($lic) }})"
                                class="px-3 py-1.5 text-xs font-mono text-[#00232c] bg-white border border-[#e3dbc2] hover:bg-[#f7f2df] rounded transition-colors flex items-center gap-1.5 font-medium cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                <span>Ficha</span>
                            </button>

                            <!-- Botón Analizar con Gemini -->
                            <button type="button" id="btn-analyze-{{ $lic->id }}" onclick="analyzeWithGemini({{ $lic->id }})"
                                class="px-3.5 py-1.5 text-xs font-mono text-white bg-indigo-600 hover:bg-indigo-700 rounded transition-colors flex items-center gap-1.5 font-semibold shadow-xs cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-indigo-200" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L9.5 9.5L2 12L9.5 14.5L12 22L14.5 14.5L22 12L14.5 9.5L12 2Z"/>
                                </svg>
                                <span id="btn-text-{{ $lic->id }}">{{ $lic->score !== null ? 'Re-evaluar Gemini' : 'Analizar con Gemini' }}</span>
                            </button>

                            <!-- Botón Guardar en Cartera -->
                            <form action="{{ route('radar.cartera', $lic->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                    class="px-3 py-1.5 rounded text-xs font-mono font-semibold transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer {{ $lic->en_cartera ? 'bg-emerald-700 text-white hover:bg-emerald-800' : 'bg-white text-[#00232c] border border-[#e3dbc2] hover:bg-[#f7f2df]' }}">
                                    <svg class="w-3.5 h-3.5 {{ $lic->en_cartera ? 'text-white' : 'text-[#56686b]' }}" fill="{{ $lic->en_cartera ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                    <span>{{ $lic->en_cartera ? 'Guardado' : 'Guardar' }}</span>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $licitaciones->links() }}
        </div>
    @endif

</div>

<!-- ========================================================================= -->
<!-- MODAL DE FICHA TÉCNICA Y EVALUACIÓN GEMINI (PANTALLAS 2 Y 3)              -->
<!-- ========================================================================= -->
<div id="modal-ficha" class="fixed inset-0 z-50 bg-[#00232c]/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 hidden overflow-y-auto">
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-xl shadow-2xl max-w-3xl w-full my-auto overflow-hidden flex flex-col max-h-[92vh]">
        
        <!-- Encabezado Oscuro Institucional Tornus -->
        <div class="bg-[#00232c] text-[#f7f2df] p-5 sm:p-6 relative shrink-0">
            <button type="button" onclick="closeFichaModal()" class="absolute top-4 right-4 p-1.5 text-[#9db3b4] hover:text-white rounded transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="flex items-center gap-2 mb-2 font-mono text-[11px]">
                <span id="ficha-referencia-badge" class="px-2 py-0.5 rounded bg-[#0b4a57] text-[#fbfb75] border border-[#15424d] font-bold"></span>
                <span id="ficha-categoria-badge" class="text-[#9db3b4]"></span>
            </div>
            <h2 id="ficha-objeto-title" class="font-['Instrument_Serif',serif] text-xl sm:text-2xl text-[#fbfb75] font-normal leading-tight tracking-wide"></h2>
        </div>

        <!-- Contenido Desplazable del Modal -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-5 flex-1">
            
            <!-- TARJETA DE ANÁLISIS GEMINI (Si está evaluado) -->
            <div id="ficha-gemini-box" class="p-5 rounded-lg border hidden transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#e3dbc2]">
                    <div class="flex items-center gap-2">
                        <span id="ficha-gemini-pill" class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold tracking-wider"></span>
                        <span class="text-xs font-mono text-[#56686b] font-medium">Gemini 2.5 Flash</span>
                    </div>
                    <span id="ficha-gemini-status" class="text-xs font-mono font-bold tracking-wider"></span>
                </div>

                <div class="pt-3 space-y-3 text-xs leading-relaxed">
                    <div>
                        <strong class="text-[#00232c] font-bold block mb-1 font-mono text-[11px] uppercase">¿Por qué aplica para Tornus?:</strong>
                        <p id="ficha-gemini-justificacion" class="text-[#3a4749]"></p>
                    </div>

                    <div id="ficha-gemini-keypoints-box" class="bg-white border border-[#e3dbc2] rounded p-3 text-xs font-mono">
                        <strong class="text-[#00232c] font-bold block mb-0.5">Punto clave a revisar:</strong>
                        <span id="ficha-gemini-puntos" class="text-[#56686b]"></span>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="button" id="ficha-re-eval-btn" onclick="reEvaluateCurrentFicha()"
                            class="px-4 py-2 bg-[#00232c] hover:bg-[#0b4a57] text-[#fbfb75] rounded text-xs font-mono font-bold tracking-wider flex items-center gap-2 shadow-xs transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.3"/>
                            </svg>
                            <span>RE-EVALUAR CON GEMINI</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Botón de Análisis si aún no está evaluado -->
            <div id="ficha-gemini-placeholder" class="bg-indigo-50/50 border border-indigo-200 rounded-lg p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs font-mono text-indigo-950">
                    <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L9.5 9.5L2 12L9.5 14.5L12 22L14.5 14.5L22 12L14.5 9.5L12 2Z"/>
                    </svg>
                    <span>Licitación pendiente de evaluación estratégica con IA frente al perfil de Tornus.</span>
                </div>
                <button type="button" onclick="reEvaluateCurrentFicha()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-mono font-semibold whitespace-nowrap shadow-xs transition-colors cursor-pointer">
                    Analizar con Gemini
                </button>
            </div>

            <!-- Grilla de 4 Tarjetas de Detalle Técnico -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                
                <!-- Tarjeta 1: Presupuesto y Cierre -->
                <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4">
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">PRESUPUESTO OFICIAL</span>
                    <div id="ficha-cuantia" class="font-['Instrument_Serif',serif] text-2xl font-bold text-[#00232c]"></div>
                    <span id="ficha-cuantia-raw" class="block text-xs font-mono text-[#56686b] mb-3"></span>

                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">CIERRE DE RECEPCIÓN</span>
                    <div id="ficha-cierre-fecha" class="text-xs font-mono font-bold text-[#00232c]"></div>
                    <span id="ficha-cierre-dias" class="text-xs font-mono font-bold text-amber-700"></span>
                </div>

                <!-- Tarjeta 2: Entidad y Ubicación -->
                <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4">
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">ENTIDAD CONTRATANTE</span>
                    <div id="ficha-entidad" class="text-xs font-bold text-[#00232c] mb-3 leading-snug"></div>

                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">UBICACIÓN GEOGRÁFICA</span>
                    <div id="ficha-ubicacion" class="text-xs font-mono font-bold text-[#00232c]"></div>
                </div>

                <!-- Tarjeta 3: Modalidad y Estado -->
                <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4">
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">MODALIDAD</span>
                    <div id="ficha-modalidad" class="text-xs font-mono font-bold text-[#00232c] mb-3"></div>

                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">ESTADO / FASE</span>
                    <div id="ficha-estado" class="text-xs font-mono font-bold text-emerald-800"></div>
                </div>

                <!-- Tarjeta 4: Plazo y Competencia -->
                <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4">
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">PLAZO DE EJECUCIÓN</span>
                    <div id="ficha-plazo" class="text-xs font-mono font-bold text-[#00232c] mb-3"></div>

                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">COMPETENCIA / INTERÉS</span>
                    <div id="ficha-vistas" class="text-xs font-mono font-bold text-[#00232c]">1 vistas</div>
                </div>

            </div>

            <!-- Objeto y Descripción Completa -->
            <div>
                <span class="block text-[11px] font-mono font-bold uppercase tracking-wider text-[#56686b] mb-1.5">
                    OBJETO Y DESCRIPCIÓN DEL PROCESO
                </span>
                <div id="ficha-objeto-full" class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4 text-xs font-mono text-[#00232c] leading-relaxed"></div>
            </div>

        </div>

        <!-- Barra Inferior de Acciones -->
        <div class="bg-[#f7f2df] border-t border-[#e3dbc2] p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2">
                <form id="ficha-cartera-form" method="POST" class="inline">
                    @csrf
                    <button type="submit" id="ficha-cartera-btn" class="px-3.5 py-2 border border-[#e3dbc2] bg-white rounded text-xs font-mono font-bold text-[#00232c] hover:bg-[#f2ece0] transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <span id="ficha-cartera-btn-text">Guardar en Cartera</span>
                    </button>
                </form>

                <button type="button" onclick="shareCurrentFicha()" class="px-3.5 py-2 border border-[#e3dbc2] bg-white rounded text-xs font-mono font-bold text-[#00232c] hover:bg-[#f2ece0] transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                    <span>Compartir</span>
                </button>
            </div>

            <a id="ficha-url-btn" href="#" target="_blank" rel="noopener noreferrer" 
                class="px-5 py-2 bg-[#00232c] hover:bg-[#0b4a57] text-[#fbfb75] rounded text-xs font-mono font-bold tracking-wider flex items-center gap-2 shadow-xs transition-colors cursor-pointer">
                <span>VER EN {{ strtoupper(App\Services\MarketService::sourceName()) }}</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
            </a>
        </div>

    </div>
</div>

<!-- Modal para Editar Palabra Clave -->
<div id="modal-edit-keyword" class="fixed inset-0 z-50 bg-[#00232c]/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#e3dbc2]">
            <h3 class="font-['Instrument_Serif',serif] text-xl text-[#00232c]">Editar Palabra Clave</h3>
            <button type="button" onclick="closeEditKeywordModal()" class="text-[#56686b] hover:text-[#00232c] text-lg font-mono">✕</button>
        </div>
        <form action="{{ route('radar.keywords.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="old_keyword" id="edit-old-keyword" value="">
            <div>
                <label class="block text-xs font-mono text-[#56686b] mb-1">TÉRMINO O FRASE</label>
                <input type="text" name="keyword" id="edit-new-keyword" required
                    class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] font-sans">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditKeywordModal()" class="px-3.5 py-2 border border-[#e3dbc2] bg-white rounded text-xs font-mono text-[#56686b] hover:bg-[#f7f2df] cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold cursor-pointer">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentFichaLicitacion = null;

function toggleAccordion(contentId, chevronId) {
    const content = document.getElementById(contentId);
    const chevron = document.getElementById(chevronId);
    if (!content) return;
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
}

function openEditKeywordModal(keyword) {
    document.getElementById('edit-old-keyword').value = keyword;
    document.getElementById('edit-new-keyword').value = keyword;
    document.getElementById('modal-edit-keyword').classList.remove('hidden');
    document.getElementById('edit-new-keyword').focus();
}

function closeEditKeywordModal() {
    document.getElementById('modal-edit-keyword').classList.add('hidden');
}

function openFichaModal(lic) {
    currentFichaLicitacion = lic;
    
    document.getElementById('ficha-referencia-badge').textContent = lic.referencia || lic.external_id || 'SIN REF';
    document.getElementById('ficha-categoria-badge').textContent = lic.categoria || 'Servicios Generales';
    document.getElementById('ficha-objeto-title').textContent = lic.objeto;
    
    // Tarjeta 1
    const cuantiaVal = Number(lic.cuantia || 0);
    const symbol = lic.moneda === 'PEN' ? 'S/' : '$';
    document.getElementById('ficha-cuantia').textContent = symbol + ' ' + (cuantiaVal > 1000000 ? (cuantiaVal / 1000000).toFixed(0) + ' M' : cuantiaVal.toLocaleString());
    document.getElementById('ficha-cuantia-raw').textContent = symbol + ' ' + cuantiaVal.toLocaleString() + ' ' + (lic.moneda || 'COP');
    
    if (lic.fecha_cierre) {
        document.getElementById('ficha-cierre-fecha').textContent = lic.fecha_cierre.substring(0, 10);
        document.getElementById('ficha-cierre-dias').textContent = 'Fecha de recepción activa';
    } else {
        document.getElementById('ficha-cierre-fecha').textContent = 'Pendiente por definir';
        document.getElementById('ficha-cierre-dias').textContent = '';
    }
    
    // Tarjeta 2
    document.getElementById('ficha-entidad').textContent = lic.entidad || 'Entidad no especificada';
    document.getElementById('ficha-ubicacion').textContent = (lic.municipio ? lic.municipio + ', ' : '') + (lic.departamento || 'Colombia');
    
    // Tarjeta 3
    document.getElementById('ficha-modalidad').textContent = lic.tipo_proceso || 'Convocatoria Pública';
    document.getElementById('ficha-estado').textContent = (lic.fase || lic.estado || 'Publicado') + ' · abierto';
    
    // Tarjeta 4
    document.getElementById('ficha-plazo').textContent = (lic.prov_unicos ? lic.prov_unicos + ' día(s)' : '60 día(s)');
    
    // Objeto Full
    document.getElementById('ficha-objeto-full').textContent = lic.objeto;
    
    // URL
    const urlBtn = document.getElementById('ficha-url-btn');
    if (lic.url_proceso) {
        urlBtn.href = lic.url_proceso;
        urlBtn.classList.remove('hidden');
    } else {
        urlBtn.classList.add('hidden');
    }
    
    // Form Cartera
    document.getElementById('ficha-cartera-form').action = '/radar/' + lic.id + '/cartera';
    const carteraText = document.getElementById('ficha-cartera-btn-text');
    if (lic.en_cartera) {
        carteraText.textContent = 'En Mi Cartera';
    } else {
        carteraText.textContent = 'Guardar en Cartera';
    }
    
    // Gemini Affinity
    renderFichaGeminiBox(lic);
    
    document.getElementById('modal-ficha').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFichaModal() {
    document.getElementById('modal-ficha').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function renderFichaGeminiBox(lic) {
    const box = document.getElementById('ficha-gemini-box');
    const placeholder = document.getElementById('ficha-gemini-placeholder');
    
    if (lic.score !== null && lic.score !== undefined) {
        placeholder.classList.add('hidden');
        box.classList.remove('hidden');
        
        const pill = document.getElementById('ficha-gemini-pill');
        const status = document.getElementById('ficha-gemini-status');
        
        pill.textContent = lic.score + '% AFINIDAD · ' + lic.nivel;
        
        if (lic.score >= 70) {
            box.className = 'p-5 rounded-lg border border-emerald-200 bg-emerald-50/50 border-l-4 border-l-emerald-600 space-y-3';
            pill.className = 'px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
            status.textContent = 'APLICA';
            status.className = 'text-xs font-mono font-bold text-emerald-800 tracking-wider';
        } else if (lic.score >= 40) {
            box.className = 'p-5 rounded-lg border border-amber-200 bg-amber-50/50 border-l-4 border-l-amber-500 space-y-3';
            pill.className = 'px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-800 border border-amber-300';
            status.textContent = 'APLICA CON CONDICIONES';
            status.className = 'text-xs font-mono font-bold text-amber-800 tracking-wider';
        } else {
            box.className = 'p-5 rounded-lg border border-rose-200 bg-rose-50/40 border-l-4 border-l-rose-500 space-y-3';
            pill.className = 'px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-rose-100 text-rose-800 border border-rose-300';
            status.textContent = 'NO APLICA';
            status.className = 'text-xs font-mono font-bold text-rose-800 tracking-wider';
        }
        
        document.getElementById('ficha-gemini-justificacion').textContent = lic.justificacion || 'Evaluación generada con el modelo Gemini contrastando el perfil de Tornus Agency.';
        document.getElementById('ficha-gemini-puntos').textContent = lic.puntos_clave || 'Revisar términos de referencia y habilitantes jurídicos.';
    } else {
        box.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
}

async function analyzeWithGemini(id) {
    const btnText = document.getElementById('btn-text-' + id);
    const origText = btnText ? btnText.textContent : 'Analizar';
    if (btnText) btnText.textContent = 'Analizando...';
    
    try {
        const token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';
        const res = await fetch('/afinidad/' + id + '/evaluar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });
        
        const data = await res.json();
        if (data.success && data.data) {
            const evalData = data.data;
            
            // Actualizar tarjeta en radar
            const cardAffinity = document.getElementById('card-affinity-' + id);
            if (cardAffinity) {
                const colorClass = evalData.score >= 70 ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : (evalData.score >= 40 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-rose-50 text-rose-800 border-rose-200');
                const dotColor = evalData.score >= 70 ? 'bg-emerald-600' : (evalData.score >= 40 ? 'bg-amber-600' : 'bg-rose-600');
                
                cardAffinity.innerHTML = `
                    <span class="px-2.5 py-1 rounded text-xs font-mono font-bold flex items-center gap-1.5 ${colorClass} border">
                        <span class="w-1.5 h-1.5 rounded-full ${dotColor}"></span>
                        <span>${evalData.score}% AFINIDAD · ${evalData.nivel}</span>
                    </span>
                `;
            }
            
            if (btnText) btnText.textContent = 'Re-evaluar Gemini';
            
            // Si está abierta la ficha de esta licitación, actualizarla
            if (currentFichaLicitacion && currentFichaLicitacion.id === id) {
                currentFichaLicitacion.score = evalData.score;
                currentFichaLicitacion.nivel = evalData.nivel;
                currentFichaLicitacion.justificacion = evalData.justificacion;
                currentFichaLicitacion.puntos_clave = evalData.puntos_clave;
                renderFichaGeminiBox(currentFichaLicitacion);
            }
        }
    } catch (e) {
        console.error('Error analizando con Gemini:', e);
        if (btnText) btnText.textContent = origText;
    }
}

async function reEvaluateCurrentFicha() {
    if (!currentFichaLicitacion) return;
    const reEvalBtn = document.getElementById('ficha-re-eval-btn');
    if (reEvalBtn) {
        reEvalBtn.disabled = true;
        reEvalBtn.innerHTML = `
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <span>ANALIZANDO CON IA...</span>
        `;
    }
    
    await analyzeWithGemini(currentFichaLicitacion.id);
    
    if (reEvalBtn) {
        reEvalBtn.disabled = false;
        reEvalBtn.innerHTML = `
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.3"/></svg>
            <span>RE-EVALUAR CON GEMINI</span>
        `;
    }
}

function shareCurrentFicha() {
    if (!currentFichaLicitacion) return;
    const shareText = `Licitación: ${currentFichaLicitacion.referencia || currentFichaLicitacion.external_id}\nObjeto: ${currentFichaLicitacion.objeto}\nPresupuesto: ${currentFichaLicitacion.formatted_cuantia || ''}\nVer en SECOP II: ${currentFichaLicitacion.url_proceso || window.location.href}`;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(shareText).then(() => {
            alert('Enlace y detalles de la ficha copiados al portapapeles.');
        });
    } else {
        alert(shareText);
    }
}
</script>
@endsection
