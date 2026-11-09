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

            <div class="flex items-center gap-2">
                <!-- Botón de Exportar Excel Ejecutivo -->
                <a href="{{ route('radar.export') }}" class="px-3.5 py-2 bg-white border border-[#e3dbc2] hover:bg-[#f7f2df] text-[#00232c] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs cursor-pointer" title="Descargar libro Excel institucional de 3 hojas">
                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Exportar Excel</span>
                </a>

                <!-- Botón de Despachar Propuestas Actuales por Correo -->
                <form action="{{ route('configuracion.alertas.despachar_actuales') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-white border border-[#e3dbc2] hover:bg-[#f7f2df] text-[#00232c] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs cursor-pointer" title="Despachar las propuestas analizadas actuales por correo al equipo según sus preferencias">
                        <svg class="w-3.5 h-3.5 text-blue-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                        <span>Enviar por Correo</span>
                    </button>
                </form>

                <!-- Botón de Auto-Triage con IA -->
                <form action="{{ route('radar.auto_triage') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-[#0b4a57] hover:bg-[#00232c] text-[#fbfb75] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs cursor-pointer" title="Analizar propuestas con Gemini y descartar automáticamente las que no tienen que ver con Tornus">
                        <svg class="w-3.5 h-3.5 text-[#fbfb75]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L9.5 9.5L2 12L9.5 14.5L12 22L14.5 14.5L22 12L14.5 9.5L12 2Z"/>
                        </svg>
                        <span>Auto-Triage IA</span>
                        @if(($pendingEvaluationCount ?? 0) > 0)
                            <span class="text-[10px] bg-[#fbfb75] text-[#00232c] px-1.5 py-0.2 rounded font-bold">{{ $pendingEvaluationCount }}</span>
                        @endif
                    </button>
                </form>

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
        </div>

        <!-- Formulario de Búsqueda y Región -->
        <form action="{{ route('radar') }}" method="GET" class="space-y-4">
            <input type="hidden" name="urgency" value="{{ $selectedUrgency }}">
            <input type="hidden" name="ia_filter" value="{{ $selectedIaFilter ?? 'viable' }}">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <!-- Buscador Libre por Objeto / Entidad / Palabras Clave -->
                <div class="lg:col-span-3 sm:col-span-2 relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por objeto, entidad o palabra clave..."
                        class="w-full h-full min-h-[42px] px-3.5 py-2 bg-white border border-[#e3dbc2] rounded text-xs text-[#00232c] placeholder-[#9db3b4] focus:outline-none focus:border-[#00232c] focus:ring-1 focus:ring-[#00232c] transition-colors font-sans">
                </div>

                <!-- Líneas de Negocio (Multi-selección con Desplegable y Checkboxes) -->
                <div class="lg:col-span-3 sm:col-span-1 relative" id="lineasDropdownContainer">
                    @php
                        $categoryLabels = [
                            'turismo' => 'Turismo, Destinos y Marca Territorial',
                            'eventos' => 'Eventos, Ferias y Operación Logística',
                            'publicidad' => 'Publicidad, Medios y Creatividad',
                            'audiovisual' => 'Producción Audiovisual y Fotografía',
                            'estrategia' => 'Estrategia y Consultoría Especializada',
                            'digital' => 'Portales Web, Tecnología y Digital',
                        ];
                        $selectedCount = count($selectedCategories ?? []);
                    @endphp

                    <button type="button" id="lineasDropdownBtn" onclick="toggleLineasDropdown()"
                        class="w-full h-full min-h-[42px] px-3 py-2 bg-white border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] flex items-center justify-between hover:border-[#00232c] transition-colors text-left cursor-pointer">
                        <div class="truncate flex items-center gap-1.5 mr-1">
                            <svg class="w-3.5 h-3.5 text-[#56686b] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                <polyline points="2 17 12 22 22 17"></polyline>
                                <polyline points="2 12 12 17 22 12"></polyline>
                            </svg>
                            <span id="lineasDropdownLabel" class="truncate font-medium">
                                @if($selectedCount === 0 || $selectedCount === 6)
                                    Todas las líneas (6)
                                @elseif($selectedCount === 1)
                                    {{ $categoryLabels[$selectedCategories[0]] ?? $selectedCategories[0] }}
                                @else
                                    {{ $selectedCount }} líneas seleccionadas
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            @if($selectedCount > 0 && $selectedCount < 6)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-[#00232c] text-[#fbfb75] font-bold">
                                    {{ $selectedCount }}
                                </span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </button>

                    <!-- Menú Desplegable con Checkboxes -->
                    <div id="lineasDropdownMenu" class="hidden absolute top-full left-0 mt-1 w-72 sm:w-80 bg-white border border-[#e3dbc2] rounded-lg shadow-xl z-50 p-3">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-[#e3dbc2] text-[11px] font-mono">
                            <span class="font-bold text-[#00232c] uppercase tracking-wider">Líneas de Negocio</span>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="selectAllLineas(true)" class="text-[#0b4a57] hover:underline font-semibold cursor-pointer">Todas</button>
                                <span class="text-[#e3dbc2]">|</span>
                                <button type="button" onclick="selectAllLineas(false)" class="text-[#56686b] hover:text-rose-600 hover:underline cursor-pointer">Limpiar</button>
                            </div>
                        </div>

                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach($categoryLabels as $catKey => $catLabel)
                                <label class="flex items-start gap-2.5 p-2 rounded hover:bg-[#f7f2df] cursor-pointer transition-colors text-xs font-mono text-[#00232c]">
                                    <input type="checkbox" name="categories[]" value="{{ $catKey }}"
                                        {{ in_array($catKey, $selectedCategories ?? []) ? 'checked' : '' }}
                                        onchange="updateLineasSummary()"
                                        class="linea-checkbox mt-0.5 rounded border-[#e3dbc2] text-[#00232c] focus:ring-0">
                                    <span class="leading-tight select-none">{{ $catLabel }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="pt-2.5 mt-2.5 border-t border-[#e3dbc2] flex items-center justify-between gap-2">
                            <span class="text-[10px] font-mono text-[#56686b]" id="lineasSelectedCountText">
                                {{ $selectedCount === 0 || $selectedCount === 6 ? 'Todas activas' : $selectedCount . ' de 6 activas' }}
                            </span>
                            <button type="submit" class="px-3.5 py-1.5 bg-[#00232c] hover:bg-[#0b4a57] text-[#fbfb75] rounded text-xs font-mono font-bold transition-colors cursor-pointer">
                                Aplicar Filtro
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Selector de Departamentos -->
                <div class="lg:col-span-2 sm:col-span-1">
                    <select name="region" id="regionSelect" onchange="onRegionChange(this.value)" class="w-full h-full min-h-[42px] px-3 py-2 bg-white border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <option value="">Todos los departamentos</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg }}" {{ $selectedRegion == $reg ? 'selected' : '' }}>{{ $reg }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Selector de Municipios con Buscador Integrado (1.104 Municipios Oficiales) -->
                <div class="lg:col-span-3 sm:col-span-1 relative" id="municipioDropdownContainer">
                    <input type="hidden" name="municipio" id="municipioHiddenInput" value="{{ $selectedMunicipio }}">

                    <button type="button" id="municipioDropdownBtn" onclick="toggleMunDropdown()"
                        class="w-full h-full min-h-[42px] px-3 py-2 bg-white border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] flex items-center justify-between hover:border-[#00232c] transition-colors text-left cursor-pointer">
                        <div class="truncate flex items-center gap-1.5 mr-1">
                            <svg class="w-3.5 h-3.5 {{ !empty($selectedMunicipio) ? 'text-emerald-700' : 'text-[#56686b]' }} shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span id="municipioDropdownLabel" class="truncate {{ !empty($selectedMunicipio) ? 'font-bold text-[#00232c]' : 'text-[#56686b]' }}">
                                @if(!empty($selectedMunicipio))
                                    {{ $selectedMunicipio }}
                                @else
                                    Todos los municipios ({{ count($municipios) }})
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            @if(!empty($selectedMunicipio))
                                <span onclick="clearSelectedMunicipio(event)" class="text-[#56686b] hover:text-rose-600 px-1 font-bold text-xs cursor-pointer" title="Quitar filtro de municipio">
                                    ×
                                </span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </button>

                    <!-- Menú Desplegable con Buscador en Vivo de Municipios -->
                    <div id="municipioDropdownMenu" class="hidden absolute top-full left-0 right-0 sm:right-auto sm:w-80 mt-1 bg-white border border-[#e3dbc2] rounded-lg shadow-xl z-50 p-2.5">
                        <!-- Campo de Búsqueda Instantánea -->
                        <div class="relative mb-2">
                            <input type="text" id="munSearchInput" oninput="filterMunicipiosList(this.value)"
                                placeholder="Buscar municipio (ej: Manizales, Cali, Villeta)..."
                                class="w-full pl-8 pr-3 py-1.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] placeholder-[#9db3b4] focus:outline-none focus:border-[#00232c]"
                                autocomplete="off">
                            <svg class="w-3.5 h-3.5 text-[#56686b] absolute left-2.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>

                        <!-- Opción por Defecto: Todos los Municipios -->
                        <button type="button" onclick="selectMunicipio('')"
                            class="w-full text-left px-2.5 py-1.5 rounded hover:bg-[#f7f2df] text-xs font-mono font-bold text-[#00232c] border-b border-[#f0ebd8] mb-1 flex items-center justify-between cursor-pointer">
                            <span>Todos los municipios</span>
                            <span class="text-[10px] text-[#56686b] font-normal" id="munCounterBadge">{{ count($municipios) }} disponibles</span>
                        </button>

                        <!-- Lista de Municipios con Scroll (Renderizado Dinámico Ultrarrápido) -->
                        <div id="municipiosListContainer" class="max-h-56 overflow-y-auto space-y-0.5 divide-y divide-[#faf6ec]">
                            <!-- Los municipios se inyectan dinámicamente desde el dataset oficial en memoria -->
                        </div>
                    </div>
                </div>

                <!-- Botón de Filtrar -->
                <div class="lg:col-span-1 sm:col-span-1">
                    <button type="submit" class="w-full h-full min-h-[42px] py-2 bg-[#0b4a57] hover:bg-[#00232c] text-white rounded text-xs font-mono font-medium transition-colors cursor-pointer">
                        Filtrar
                    </button>
                </div>
            </div>

            <!-- FILTROS DE TIEMPO Y VENCIMIENTO (PLAZOS ESTRATÉGICOS) -->
            <div class="pt-3 border-t border-[#e3dbc2] flex flex-wrap items-center gap-2 font-mono text-xs">
                <span class="text-[#56686b] font-semibold mr-1">PLAZO DE CIERRE:</span>
                
                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'all'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors {{ $selectedUrgency === 'all' ? 'border-[#00232c] bg-[#00232c] text-[#fbfb75] font-semibold' : 'border-[#e3dbc2] bg-white text-[#56686b] hover:bg-[#f7f2df]' }}">
                    Todos los plazos
                </a>
                
                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'critical'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedUrgency === 'critical' ? 'border-rose-400 bg-rose-100 text-rose-900 font-bold' : 'border-rose-200 bg-rose-50 text-rose-800 hover:bg-rose-100' }}"
                    title="Propuestas con cierre urgente en los próximos 7 días (de 7 días hacia 0 horas)">
                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                    <span>Tiempos Críticos: ≤ 7 días (7 días a 0h)</span>
                    @if(isset($criticalCount) && $criticalCount > 0)
                        <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] {{ $selectedUrgency === 'critical' ? 'bg-rose-800 text-white' : 'bg-rose-200 text-rose-800' }}">{{ $criticalCount }}</span>
                    @endif
                </a>

                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'early'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedUrgency === 'early' ? 'border-emerald-400 bg-emerald-100 text-emerald-900 font-bold' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}"
                    title="Propuestas con tiempo de antelación para formulación y estructuración (desde 7 días hasta 5 meses)">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>Alertas Tempranas: 7 días a 5 meses</span>
                    @if(isset($earlyCount) && $earlyCount > 0)
                        <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] {{ $selectedUrgency === 'early' ? 'bg-emerald-800 text-white' : 'bg-emerald-200 text-emerald-800' }}">{{ $earlyCount }}</span>
                    @endif
                </a>

                <span class="text-[#e3dbc2] mx-1 hidden sm:inline">|</span>

                <!-- Botón de Foco en Entidades Prioritarias -->
                <a href="{{ route('radar', array_merge(request()->query(), ['priority_only' => $selectedPriorityOnly ? null : 1])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedPriorityOnly ? 'border-[#00232c] bg-[#fbfb75] text-[#00232c] font-bold shadow-2xs' : 'border-[#e3dbc2] bg-white text-[#56686b] hover:bg-[#f7f2df]' }}">
                    <svg class="w-3.5 h-3.5 {{ $selectedPriorityOnly ? 'text-[#00232c]' : 'text-[#56686b]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 21h18M3 7v14M21 7v14M6 7V3h12v4M9 11h2M9 15h2M13 11h2M13 15h2"></path>
                    </svg>
                    <span>Solo Entidades Prioritarias</span>
                    @if($selectedPriorityOnly)
                        <span class="text-[10px] bg-[#00232c] text-[#fbfb75] px-1.5 py-0.2 rounded font-mono">Activo</span>
                    @endif
                </a>
            </div>

            <!-- FILTRO INTELIGENTE DE AFINIDAD CON IA (AUTO-TRIAGE TORNUS) -->
            <div class="pt-3 border-t border-[#e3dbc2] flex flex-wrap items-center justify-between gap-2 font-mono text-xs">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[#56686b] font-semibold mr-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L9.5 9.5L2 12L9.5 14.5L12 22L14.5 14.5L22 12L14.5 9.5L12 2Z"/>
                        </svg>
                        <span>FILTRO INTELIGENTE IA:</span>
                    </span>

                    <!-- Opción 1: Solo Viables (Por Defecto) -->
                    <a href="{{ route('radar', array_merge(request()->query(), ['ia_filter' => 'viable'])) }}"
                        class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ ($selectedIaFilter ?? 'viable') === 'viable' ? 'border-emerald-600 bg-emerald-100 text-emerald-950 font-bold shadow-2xs' : 'border-[#e3dbc2] bg-white text-[#56686b] hover:bg-[#f7f2df]' }}">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>Solo Oportunidades Viables (Afines o Pendientes)</span>
                        @if(($selectedIaFilter ?? 'viable') === 'viable')
                            <span class="text-[10px] bg-emerald-700 text-white px-1.5 py-0.2 rounded font-mono">Activo</span>
                        @endif
                    </a>

                    <!-- Opción 2: Todas las Ofertas -->
                    <a href="{{ route('radar', array_merge(request()->query(), ['ia_filter' => 'all'])) }}"
                        class="px-2.5 py-1 rounded border transition-colors {{ ($selectedIaFilter ?? 'viable') === 'all' ? 'border-[#00232c] bg-[#00232c] text-[#fbfb75] font-semibold' : 'border-[#e3dbc2] bg-white text-[#56686b] hover:bg-[#f7f2df]' }}">
                        <span>Todas las ofertas</span>
                    </a>

                    <!-- Opción 3: Descartadas por IA -->
                    <a href="{{ route('radar', array_merge(request()->query(), ['ia_filter' => 'discarded'])) }}"
                        class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ ($selectedIaFilter ?? 'viable') === 'discarded' ? 'border-rose-400 bg-rose-100 text-rose-950 font-bold shadow-2xs' : 'border-rose-200 bg-rose-50/60 text-rose-800 hover:bg-rose-100' }}">
                        <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Descartadas por IA</span>
                        <span class="text-[10px] bg-rose-200 text-rose-900 px-1.5 py-0.2 rounded font-bold">{{ $discardedCount ?? 0 }}</span>
                    </a>
                </div>

                @if(($discardedCount ?? 0) > 0 && ($selectedIaFilter ?? 'viable') === 'viable')
                    <div class="text-[11px] text-[#56686b] italic">
                        <span>{{ $discardedCount }} ofertas no afines ocultadas automáticamente</span>
                    </div>
                @endif
            </div>
        </form>
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
                    $isPriority = false;
                    foreach ($priorityEntities as $pEnt) {
                        if (!empty($pEnt) && mb_stripos($lic->entidad, $pEnt, 0, 'UTF-8') !== false) {
                            $isPriority = true;
                            break;
                        }
                    }
                @endphp
                <div id="lic-card-{{ $lic->id }}" class="licitacion-card bg-[#fffdf6] border {{ $isPriority ? 'border-[#00232c] ring-1 ring-[#00232c]/20 shadow-sm' : 'border-[#e3dbc2] shadow-xs' }} border-l-4 {{ $isPriority ? 'border-l-[#00232c]' : 'border-l-[#8a252c]' }} rounded p-4 sm:p-5 hover:border-[#0b4a57] transition-all">
                    
                    <!-- Fila Superior: Badges a la izquierda y Presupuesto a la derecha -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-2">
                        
                        <!-- Badges con ancho seguro y whitespace-nowrap para evitar desbordes -->
                        <div class="flex flex-wrap items-center gap-1.5 font-mono text-[11px] min-w-0 flex-1">
                            @if($isPriority)
                                <span class="px-2 py-0.5 rounded bg-[#fbfb75] text-[#00232c] border border-[#d4d455] font-bold whitespace-nowrap shrink-0 flex items-center gap-1 shadow-2xs" title="Entidad Prioritaria del Radar">
                                    <svg class="w-3 h-3 text-[#00232c]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M3 21h18M3 7v14M21 7v14M6 7V3h12v4M9 11h2M9 15h2M13 11h2M13 15h2"></path>
                                    </svg>
                                    <span>FOCO PRIORITARIO</span>
                                </span>
                            @endif

                            @if($lic->score !== null && ($lic->score < 40 || strcasecmp($lic->estrategia_postulacion ?? '', 'Descartar') === 0))
                                <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-800 border border-rose-200 font-bold whitespace-nowrap shrink-0 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                    <span>DESCARTADA POR IA</span>
                                </span>
                            @endif

                            <!-- Badge de Vencimiento: Cierra hoy / Cierra en X días -->
                            <span class="px-2 py-0.5 rounded {{ $urgency['class'] }} font-medium whitespace-nowrap shrink-0">
                                {{ $urgency['badge'] }}
                            </span>

                            <!-- Departamento / Resto del país -->
                            <span class="px-2 py-0.5 rounded bg-[#00232c] text-[#fbfb75] font-bold whitespace-nowrap shrink-0">
                                {{ $lic->departamento ?: 'Resto del país' }}
                            </span>

                            <!-- Categoría -->
                            <span class="px-2 py-0.5 rounded bg-[#ece6d5] text-[#56686b] whitespace-nowrap shrink-0">
                                {{ $lic->categoria ?: 'Servicios Generales' }}
                            </span>

                            <!-- Estado / Fase -->
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 whitespace-nowrap shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>{{ $lic->fase ?: 'Convocado · Recibiendo ofertas' }}</span>
                            </span>

                            <!-- Ventana de Convocatoria para Ofertar -->
                            <span class="px-1.5 py-0.5 text-[#56686b] flex items-center gap-1 whitespace-nowrap shrink-0" title="Tiempo de convocatoria para presentar ofertas">
                                <svg class="w-3.5 h-3.5 text-[#56686b] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>{{ $lic->dias_convocatoria ? $lic->dias_convocatoria . ' d conv.' : 'Plazo s/pliego' }}</span>
                            </span>

                            <!-- Modalidad -->
                            <span class="px-2 py-0.5 rounded bg-[#dce6e7] text-[#0b4a57] font-medium whitespace-nowrap shrink-0">
                                {{ $lic->tipo_proceso ?: 'Mínima cuantía' }}
                            </span>
                        </div>

                        <!-- Presupuesto Oficial & Fechas Clave -->
                        <div class="text-right shrink-0">
                            @if(empty($lic->cuantia) || (float)$lic->cuantia <= 0)
                                <div class="font-bold text-xs sm:text-[13px] text-amber-900 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded inline-block tracking-tight mb-1 whitespace-nowrap">
                                    A definir (RFI / Cotización)
                                </div>
                            @else
                                <div class="font-bold text-xl sm:text-2xl text-[#00232c] tracking-tight whitespace-nowrap">
                                    {{ $lic->formatted_cuantia }}
                                </div>
                            @endif

                            <div class="text-[11px] font-mono text-[#56686b] whitespace-nowrap">
                                Cierre de ofertas: <strong class="text-[#00232c]">{{ $lic->fecha_cierre ? (strlen($lic->fecha_cierre) > 10 && substr($lic->fecha_cierre, 11, 5) !== '00:00' ? date('d M · H:i', strtotime($lic->fecha_cierre)) : date('d M', strtotime($lic->fecha_cierre))) : 'Pendiente' }}</strong>
                            </div>
                            @if($lic->fecha_publicacion)
                                <div class="text-[10px] font-mono text-[#56686b] whitespace-nowrap">
                                    Apertura / Salida: <strong>{{ date('d M', strtotime($lic->fecha_publicacion)) }}</strong>
                                </div>
                            @endif
                            <div class="text-[10px] font-mono text-[#9db3b4] mt-0.5 max-w-[220px] truncate ml-auto" title="{{ $lic->codigo_proceso }}">
                                {{ $lic->codigo_proceso }}
                            </div>
                        </div>
                    </div>

                    <!-- Objeto / Título Completo sin desborde -->
                    <h3 class="w-full block text-sm sm:text-[15px] font-bold text-[#00232c] leading-snug line-clamp-2 my-2.5 break-words">
                        {{ (!empty($lic->objeto) && $lic->objeto !== 'No definido') ? $lic->objeto : ($lic->referencia ?: 'Licitación pública en ' . App\Services\MarketService::sourceName()) }}
                    </h3>

                    <!-- Dictamen Estratégico de Gemini en la Tarjeta (Visible si está evaluado o al presionar Analizar) -->
                    <div id="card-gemini-box-{{ $lic->id }}" class="{{ $lic->score !== null ? '' : 'hidden' }} my-3 p-3.5 rounded-lg bg-[#fbf9f1] border border-[#e3dbc2] border-l-4 {{ ($lic->score ?? 0) >= 70 ? 'border-l-emerald-600' : (($lic->score ?? 0) >= 40 ? 'border-l-amber-500' : 'border-l-rose-500') }} text-xs font-mono space-y-1.5 transition-all">
                        <div class="flex items-center justify-between gap-2 pb-1.5 border-b border-[#e3dbc2]/60">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#00232c] flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L9.5 9.5L2 12L9.5 14.5L12 22L14.5 14.5L22 12L14.5 9.5L12 2Z"/>
                                </svg>
                                <span>DICTAMEN ESTRATÉGICO GEMINI IA</span>
                            </span>
                            <span id="card-gemini-status-{{ $lic->id }}" class="text-[10px] font-bold tracking-wider {{ ($lic->score ?? 0) >= 70 ? 'text-emerald-800' : (($lic->score ?? 0) >= 40 ? 'text-amber-800' : 'text-rose-800') }}">
                                {{ ($lic->score ?? 0) >= 70 ? 'APLICA DIRECTO' : (($lic->score ?? 0) >= 40 ? 'APLICA CON CONDICIONES' : 'NO APLICA') }}
                            </span>
                        </div>
                        <p id="card-gemini-desc-{{ $lic->id }}" class="text-[#3a4749] font-sans text-xs leading-relaxed pt-0.5">
                            {{ $lic->justificacion }}
                        </p>
                        <div id="card-gemini-points-wrapper-{{ $lic->id }}" class="{{ $lic->puntos_clave ? '' : 'hidden' }} pt-1 text-[11px] text-[#56686b] font-mono">
                            <strong class="text-[#00232c]">Punto clave a revisar:</strong> <span id="card-gemini-points-{{ $lic->id }}">{{ $lic->puntos_clave }}</span>
                        </div>
                    </div>

                    <!-- Fila Inferior: Ubicación a la izquierda y 2 Filas de Botones a la derecha -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pt-2 border-t border-[#f2ece0]">
                        
                        <!-- Ubicación y Entidad -->
                        <div class="text-xs font-mono text-[#56686b] flex flex-wrap items-center gap-2 min-w-0 flex-1">
                            <strong class="text-[#00232c] whitespace-nowrap">{{ $lic->municipio ? $lic->municipio . ' · ' . $lic->departamento : ($lic->departamento ?: 'Nacional') }}</strong>
                            <span class="truncate max-w-xs sm:max-w-md">{{ $lic->entidad }}</span>

                            <!-- Badge de Afinidad si ya está evaluado -->
                            <span id="card-affinity-{{ $lic->id }}" class="inline-flex">
                                @if($lic->score !== null)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold flex items-center gap-1 {{ $lic->score >= 70 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($lic->score >= 40 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-rose-50 text-rose-800 border border-rose-200') }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $lic->score >= 70 ? 'bg-emerald-600' : ($lic->score >= 40 ? 'bg-amber-600' : 'bg-rose-600') }}"></span>
                                        <span>{{ $lic->score }}% AFINIDAD · {{ $lic->nivel }}</span>
                                    </span>
                                @endif
                            </span>
                        </div>

                        <!-- Grupo de Botones de Acción (2 Filas como en la imagen) -->
                        <div class="flex flex-col items-end gap-1.5 shrink-0">
                            
                            <!-- Fila 1 de Botones: Preparar, Compartir, Ver en SECOP II, Descartar -->
                            <div class="flex flex-wrap items-center gap-1.5 text-xs font-mono">
                                <!-- Botón Preparar -->
                                <button type="button" onclick="openFichaModal({{ $lic->id }})"
                                    class="px-2.5 py-1 bg-white border border-[#e3dbc2] hover:bg-[#f7f2df] rounded text-[#00232c] font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                    <span>Preparar</span>
                                </button>

                                <!-- Botón Compartir -->
                                <button type="button" onclick="shareLicitacion({{ $lic->id }})"
                                    class="px-2.5 py-1 bg-white border border-[#e3dbc2] hover:bg-[#f7f2df] rounded text-[#00232c] font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="18" cy="5" r="3"></circle>
                                        <circle cx="6" cy="12" r="3"></circle>
                                        <circle cx="18" cy="19" r="3"></circle>
                                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                                    </svg>
                                    <span>Compartir</span>
                                </button>

                                <!-- Botón Ver en SECOP II -->
                                @if($lic->url_proceso)
                                    <a href="{{ $lic->url_proceso }}" target="_blank" rel="noopener noreferrer"
                                        class="px-2.5 py-1 bg-white border border-[#00232c] hover:bg-[#00232c] text-[#00232c] hover:text-white rounded font-medium transition-colors flex items-center gap-1 whitespace-nowrap">
                                        <span>Ver en {{ App\Services\MarketService::sourceName() }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                            <polyline points="15 3 21 3 21 9"></polyline>
                                            <line x1="10" y1="14" x2="21" y2="3"></line>
                                        </svg>
                                    </a>
                                @endif

                                <!-- Botón Descartar -->
                                <button type="button" onclick="dismissCard('lic-card-{{ $lic->id }}')"
                                    class="px-2.5 py-1 bg-white border border-[#e3dbc2] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 rounded text-[#56686b] font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                    <span>✕ DESCARTAR</span>
                                </button>
                            </div>

                            <!-- Fila 2 de Botones: Ficha, Analizar con Gemini, Guardar -->
                            <div class="flex flex-wrap items-center gap-1.5 text-xs font-mono">
                                <!-- Botón Ficha -->
                                <button type="button" onclick="openFichaModal({{ $lic->id }})"
                                    class="px-3 py-1 bg-white border border-[#e3dbc2] hover:bg-[#f7f2df] rounded text-[#00232c] font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <span>Ficha</span>
                                </button>

                                <!-- Botón Analizar con Gemini -->
                                <button type="button" id="btn-analyze-{{ $lic->id }}" onclick="analyzeWithGemini({{ $lic->id }})"
                                    class="px-3.5 py-1 text-white bg-[#4338ca] hover:bg-[#3730a3] rounded font-semibold transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 text-indigo-200" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L9.5 9.5L2 12L9.5 14.5L12 22L14.5 14.5L22 12L14.5 9.5L12 2Z"/>
                                    </svg>
                                    <span id="btn-text-{{ $lic->id }}">{{ $lic->score !== null ? 'Re-evaluar Gemini' : 'Analizar con Gemini' }}</span>
                                </button>

                                <!-- Botón Guardar en Cartera -->
                                <form action="{{ route('radar.cartera', $lic->id) }}" method="POST" class="inline" onsubmit="return toggleCarteraAjax(event, {{ $lic->id }})">
                                    @csrf
                                    <button type="submit" id="btn-cartera-{{ $lic->id }}"
                                        class="px-3 py-1 rounded font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap {{ $lic->en_cartera ? 'bg-emerald-700 text-white hover:bg-emerald-800' : 'bg-white text-[#00232c] border border-[#e3dbc2] hover:bg-[#f7f2df]' }}">
                                        <svg id="icon-cartera-{{ $lic->id }}" class="w-3.5 h-3.5 {{ $lic->en_cartera ? 'text-white' : 'text-[#56686b]' }}" fill="{{ $lic->en_cartera ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                        <span id="text-cartera-{{ $lic->id }}">{{ $lic->en_cartera ? 'Guardado' : 'Guardar' }}</span>
                                    </button>
                                </form>
                            </div>

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

<!-- Modal para Editar Palabra Clave -->
<script>
window.licitacionesStore = @json($licitaciones->keyBy('id'));
window.ALL_MUNICIPIOS = @json($municipios);
window.SELECTED_MUNICIPIO = @json($selectedMunicipio ?? '');

function toggleLineasDropdown() {
    const menu = document.getElementById('lineasDropdownMenu');
    menu.classList.toggle('hidden');
    document.getElementById('municipioDropdownMenu')?.classList.add('hidden');
}

function selectAllLineas(select) {
    document.querySelectorAll('.linea-checkbox').forEach(cb => {
        cb.checked = select;
    });
    updateLineasSummary();
}

function updateLineasSummary() {
    const checked = Array.from(document.querySelectorAll('.linea-checkbox:checked'));
    const label = document.getElementById('lineasDropdownLabel');
    const countText = document.getElementById('lineasSelectedCountText');
    if (checked.length === 0 || checked.length === 6) {
        label.textContent = 'Todas las líneas (6)';
    } else if (checked.length === 1) {
        label.textContent = checked[0].closest('label').querySelector('span').textContent.trim();
    } else {
        label.textContent = checked.length + ' líneas seleccionadas';
    }
    if (countText) {
        countText.textContent = (checked.length === 0 || checked.length === 6) ? 'Todas activas' : (checked.length + ' de 6 activas');
    }
}

function toggleMunDropdown() {
    const menu = document.getElementById('municipioDropdownMenu');
    const isHidden = menu.classList.contains('hidden');
    menu.classList.toggle('hidden');
    document.getElementById('lineasDropdownMenu')?.classList.add('hidden');
    if (isHidden) {
        renderMunicipios(window.ALL_MUNICIPIOS);
        const input = document.getElementById('munSearchInput');
        setTimeout(() => input?.focus(), 60);
    }
}

function renderMunicipios(list) {
    const container = document.getElementById('municipiosListContainer');
    if (!container) return;

    if (!list || list.length === 0) {
        container.innerHTML = '<div class="p-4 text-center text-xs font-mono text-[#56686b]">No se encontraron municipios coincidentes.</div>';
        const badge = document.getElementById('munCounterBadge');
        if (badge) badge.textContent = '0 disponibles';
        return;
    }

    const badge = document.getElementById('munCounterBadge');
    if (badge) badge.textContent = list.length + ' disponibles';

    const toRender = list.slice(0, 100);
    let html = '';

    for (let i = 0; i < toRender.length; i++) {
        const item = toRender[i];
        const nom = typeof item === 'object' ? (item.nombre || '') : item;
        const dep = typeof item === 'object' ? (item.departamento || '') : '';
        const isSelected = (window.SELECTED_MUNICIPIO === nom);
        const nomEscaped = nom.replace(/'/g, "\\'");

        html += `<button type="button" onclick="selectMunicipio('${nomEscaped}')" class="w-full text-left px-2.5 py-1.5 rounded hover:bg-[#f7f2df] text-xs font-mono flex items-center justify-between transition-colors cursor-pointer ${isSelected ? 'bg-[#00232c] text-[#fbfb75] font-bold' : 'text-[#00232c]'}">` +
                `<span class="truncate">${nom}</span>` +
                (dep ? `<span class="text-[10px] shrink-0 ml-2 ${isSelected ? 'text-[#fbfb75]/80' : 'text-[#56686b]'}">${dep}</span>` : '') +
                `</button>`;
    }

    if (list.length > 100) {
        html += `<div class="p-2 text-center text-[10px] font-mono text-[#56686b] bg-[#faf6ec] border-t border-[#e3dbc2]">Escribe en el buscador para filtrar entre los ${list.length} municipios...</div>`;
    }

    container.innerHTML = html;
}

function selectMunicipio(nombre) {
    document.getElementById('municipioHiddenInput').value = nombre;
    document.getElementById('municipioHiddenInput').closest('form').submit();
}

function clearSelectedMunicipio(event) {
    event.stopPropagation();
    selectMunicipio('');
}

function onRegionChange(val) {
    document.getElementById('municipioHiddenInput').value = '';
    document.getElementById('regionSelect').closest('form').submit();
}

function filterMunicipiosList(term) {
    term = (term || '').trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    if (!term) {
        renderMunicipios(window.ALL_MUNICIPIOS);
        return;
    }

    const filtered = (window.ALL_MUNICIPIOS || []).filter(item => {
        const nom = (typeof item === 'object' ? (item.nombre || '') : item).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        const dep = (typeof item === 'object' ? (item.departamento || '') : '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        return nom.includes(term) || dep.includes(term);
    });

    renderMunicipios(filtered);
}

// Inicializar lista al cargar
document.addEventListener('DOMContentLoaded', function() {
    renderMunicipios(window.ALL_MUNICIPIOS);
});

// Cerrar desplegables al hacer clic fuera
document.addEventListener('click', function(e) {
    const lineasContainer = document.getElementById('lineasDropdownContainer');
    if (lineasContainer && !lineasContainer.contains(e.target)) {
        document.getElementById('lineasDropdownMenu')?.classList.add('hidden');
    }
    const munContainer = document.getElementById('municipioDropdownContainer');
    if (munContainer && !munContainer.contains(e.target)) {
        document.getElementById('municipioDropdownMenu')?.classList.add('hidden');
    }
});
</script>

@include('partials.ficha-drawer')
@endsection
