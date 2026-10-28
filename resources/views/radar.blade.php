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
            No se encontraron licitaciones con los criterios de búsqueda seleccionados.
        </div>
    @else
        <div class="space-y-4">
            @foreach($licitaciones as $lic)
                @php
                    $urgency = $lic->urgency_info;
                @endphp
                <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs hover:border-[#0b4a57] transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-[#f7f2df] text-[#00232c] border border-[#e3dbc2]">
                                {{ $lic->referencia ?: $lic->external_id }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded text-[11px] font-mono border bg-emerald-50 text-emerald-700 border-emerald-200 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>{{ $lic->estado }}</span>
                            </span>
                            
                            <!-- Badge de Vencimiento Calculado -->
                            <span class="px-2.5 py-0.5 rounded text-[11px] font-mono border {{ $urgency['class'] }} flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>{{ $urgency['badge'] }}</span>
                            </span>
                        </div>

                        <!-- Presupuesto Oficial -->
                        <div class="text-right">
                            <div class="font-['Instrument_Serif',serif] text-2xl font-bold text-[#00232c]">
                                {{ $lic->formatted_cuantia }}
                            </div>
                            <div class="text-[10px] font-mono text-[#56686b] uppercase">
                                Presupuesto oficial {{ $lic->moneda }}
                            </div>
                        </div>
                    </div>

                    <h3 class="text-base font-semibold text-[#00232c] mb-2 leading-snug">
                        {{ $lic->objeto }}
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs font-mono text-[#56686b] py-2 border-y border-[#f2ece0] mb-3">
                        <div>
                            <span class="text-[#9db3b4]">Entidad:</span>
                            <span class="text-[#00232c] font-medium">{{ $lic->entidad }}</span>
                        </div>
                        <div>
                            <span class="text-[#9db3b4]">Región:</span>
                            <span class="text-[#00232c] font-medium">{{ $lic->departamento ?: 'No especificado' }}</span>
                        </div>
                        <div>
                            <span class="text-[#9db3b4]">Cierre:</span>
                            <span class="text-[#00232c] font-medium">{{ $lic->fecha_cierre ? substr($lic->fecha_cierre, 0, 16) : 'Pendiente' }}</span>
                        </div>
                    </div>

                    <!-- Fila de Acciones: Botón de Afinidad IA + Botón Cartera -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        
                        <!-- AFINIDAD IA GEMINI -->
                        <div class="flex items-center gap-2">
                            <form action="{{ route('afinidad.evaluate', $lic->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-[#f7f2df] hover:bg-[#e3dbc2] text-[#00232c] border border-[#e3dbc2] rounded text-xs font-mono font-medium transition-colors flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-[#0b4a57]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                    <span>Evaluar Afinidad (Gemini)</span>
                                </button>
                            </form>

                            @if($lic->score !== null)
                                <span class="px-2 py-1 rounded text-xs font-mono {{ $lic->nivel === 'ALTA' ? 'bg-purple-50 text-purple-700 border border-purple-200 font-bold' : ($lic->nivel === 'MEDIA' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                                    Afinidad: {{ $lic->nivel }} · {{ $lic->score }}/100
                                </span>
                            @endif
                        </div>

                        <!-- ACCIONES DE CARTERA Y FICHA -->
                        <div class="flex items-center gap-2">
                            <form action="{{ route('radar.cartera', $lic->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded text-xs font-mono font-semibold transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer {{ $lic->en_cartera ? 'bg-emerald-700 text-white hover:bg-emerald-800' : 'bg-[#00232c] hover:bg-[#0b4a57] text-[#fbfb75]' }}">
                                    @if($lic->en_cartera)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <span>En Mi Cartera</span>
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Agregar a Mi Cartera</span>
                                    @endif
                                </button>
                            </form>
                            
                            @if($lic->url_proceso)
                                <a href="{{ $lic->url_proceso }}" target="_blank" rel="noopener noreferrer" class="p-1.5 text-[#56686b] hover:text-[#00232c] rounded border border-[#e3dbc2] bg-white transition-colors" title="Ver pliego oficial en el Estado">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                </a>
                            @endif
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
</script>
@endsection
