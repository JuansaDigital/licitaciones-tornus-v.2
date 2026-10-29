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

                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'month'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedUrgency === 'month' ? 'border-emerald-400 bg-emerald-100 text-emerald-900 font-bold' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>Con tiempo: 15 a 30 días</span>
                </a>

                <a href="{{ route('radar', array_merge(request()->query(), ['urgency' => 'quarter'])) }}"
                    class="px-2.5 py-1 rounded border transition-colors flex items-center gap-1.5 {{ $selectedUrgency === 'quarter' ? 'border-blue-400 bg-blue-100 text-blue-900 font-bold' : 'border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Con tiempo: 1 a 3 meses</span>
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
        <div id="accordion-keywords" class="hidden p-5 pt-0 border-t border-[#f2ece0] space-y-5">
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
        <div id="accordion-exclusions" class="hidden p-5 pt-0 border-t border-[#f2ece0] space-y-5">
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
                @endphp
                <div id="lic-card-{{ $lic->id }}" class="licitacion-card bg-[#fffdf6] border border-[#e3dbc2] border-l-4 border-l-[#8a252c] rounded p-4 sm:p-5 shadow-xs hover:border-[#0b4a57] transition-all">
                    
                    <!-- Fila Superior: Badges a la izquierda y Presupuesto a la derecha -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-2">
                        
                        <!-- Badges con ancho seguro y whitespace-nowrap para evitar desbordes -->
                        <div class="flex flex-wrap items-center gap-1.5 font-mono text-[11px] min-w-0 flex-1">
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
                                Cierre de ofertas: <strong class="text-[#00232c]">{{ $lic->fecha_cierre ? date('d M', strtotime($lic->fecha_cierre)) : 'Pendiente' }}</strong>
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

<!-- ========================================================================= -->
<!-- SIDEBAR DERECHO (DRAWER OFF-CANVAS) DE FICHA TÉCNICA Y EVALUACIÓN GEMINI  -->
<!-- ========================================================================= -->
<div id="drawer-ficha-backdrop" onclick="closeFichaModal()" class="fixed inset-0 bg-[#00232c]/50 backdrop-blur-xs z-50 hidden transition-opacity duration-300"></div>

<aside id="drawer-ficha" class="fixed inset-y-0 right-0 w-full sm:max-w-xl md:max-w-2xl bg-[#fffdf6] border-l border-[#e3dbc2] shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    
    <!-- Encabezado Oscuro Institucional Tornus -->
    <div class="bg-[#00232c] text-[#f7f2df] p-5 sm:p-6 relative shrink-0">
        <button type="button" onclick="closeFichaModal()" class="absolute top-4 right-4 p-1.5 text-[#9db3b4] hover:text-white rounded transition-colors cursor-pointer" title="Cerrar panel lateral">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="flex items-center gap-2 mb-2 font-mono text-[11px]">
            <span id="ficha-referencia-badge" class="px-2 py-0.5 rounded bg-[#0b4a57] text-[#fbfb75] border border-[#15424d] font-bold"></span>
            <span id="ficha-categoria-badge" class="text-[#9db3b4]"></span>
        </div>
        <h2 id="ficha-objeto-title" class="font-['Instrument_Serif',serif] text-xl sm:text-2xl text-[#fbfb75] font-normal leading-tight tracking-wide pr-8"></h2>
    </div>

    <!-- Contenido Desplazable del Sidebar -->
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

        <!-- Grilla de 4 Tarjetas de Detalle Técnico Completo (FICHA 360) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            
            <!-- Tarjeta 1: Presupuesto y Cronograma de Convocatoria -->
            <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4 space-y-3">
                <div>
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">PRESUPUESTO OFICIAL</span>
                    <div id="ficha-cuantia" class="font-['Instrument_Serif',serif] text-2xl font-bold text-[#00232c]"></div>
                    <span id="ficha-cuantia-raw" class="block text-xs font-mono text-[#56686b]"></span>
                </div>

                <div class="pt-2 border-t border-[#e3dbc2]/60 space-y-2">
                    <div>
                        <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider">APERTURA / PUBLICACIÓN (DÍA QUE SALE)</span>
                        <div id="ficha-apertura-fecha" class="text-xs font-mono font-bold text-[#00232c]"></div>
                    </div>

                    <div>
                        <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider">RECEPCIÓN DE OFERTAS (DÍA QUE TERMINA)</span>
                        <div id="ficha-cierre-fecha" class="text-xs font-mono font-bold text-[#00232c]"></div>
                        <span id="ficha-cierre-dias" class="text-xs font-mono font-bold text-amber-700"></span>
                    </div>

                    <div id="ficha-apertura-ofertas-box" class="hidden">
                        <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider">APERTURA EFECTIVA DE OFERTAS</span>
                        <div id="ficha-apertura-ofertas" class="text-xs font-mono font-bold text-[#00232c]"></div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Entidad Contratante (NIT, Orden y Ubicación) -->
            <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4 space-y-3">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider">ENTIDAD CONTRATANTE</span>
                        <span id="ficha-orden-entidad-label" class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#e3dbc2]/60 text-[#00232c] hidden"></span>
                    </div>
                    <div id="ficha-entidad" class="text-xs font-bold text-[#00232c] leading-snug"></div>
                    <div id="ficha-nit-box" class="mt-1.5 hidden">
                        <span class="text-[10px] font-mono text-[#56686b] uppercase">NIT:</span>
                        <span id="ficha-nit" class="text-xs font-mono text-[#00232c] font-semibold"></span>
                    </div>
                    <div id="ficha-adjudicador-box" class="mt-1 hidden">
                        <span class="text-[10px] font-mono text-[#56686b] uppercase">Adjudicador:</span>
                        <span id="ficha-adjudicador" class="text-xs font-mono text-[#00232c] font-medium"></span>
                    </div>
                </div>

                <div class="pt-2 border-t border-[#e3dbc2]/60">
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">UBICACIÓN GEOGRÁFICA</span>
                    <div id="ficha-ubicacion" class="text-xs font-mono font-bold text-[#00232c]"></div>
                </div>
            </div>

            <!-- Tarjeta 3: Régimen y Modalidad Contractual -->
            <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4 space-y-3">
                <div>
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">MODALIDAD DE CONTRATACIÓN</span>
                    <div id="ficha-modalidad" class="text-xs font-mono font-bold text-[#00232c]"></div>
                </div>

                <div class="pt-2 border-t border-[#e3dbc2]/60 space-y-1.5">
                    <div>
                        <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider">TIPO DE CONTRATO</span>
                        <div id="ficha-tipo-contrato-text" class="text-xs font-mono font-bold text-[#00232c]">Prestación de Servicios</div>
                    </div>
                    <div id="ficha-unspsc-box" class="hidden">
                        <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider">CÓDIGO PRINCIPAL UNSPSC</span>
                        <div id="ficha-unspsc-text" class="text-xs font-mono font-bold text-[#0b4a57]"></div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 4: Plazo, Competencia y Estado -->
            <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded-lg p-4 space-y-3">
                <div>
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">PLAZO DE EJECUCIÓN (REAL)</span>
                    <div id="ficha-plazo" class="text-xs font-mono font-bold text-[#00232c]"></div>
                </div>

                <div class="pt-2 border-t border-[#e3dbc2]/60">
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">COMPETENCIA / INTERÉS REGISTRADO</span>
                    <div id="ficha-vistas" class="text-xs font-mono font-bold text-[#00232c]">Sin postulaciones registradas</div>
                </div>

                <div class="pt-2 border-t border-[#e3dbc2]/60">
                    <span class="block text-[10px] font-mono font-bold uppercase text-[#56686b] tracking-wider mb-1">ESTADO / FASE ACTUAL</span>
                    <div id="ficha-estado" class="text-xs font-mono font-bold text-emerald-800"></div>
                </div>

                <div id="ficha-adenda-box" class="pt-2 border-t border-[#e3dbc2]/60 hidden">
                    <span class="block text-[10px] font-mono font-bold uppercase text-amber-800 tracking-wider">ÚLTIMA ADENDA REGISTRADA</span>
                    <div id="ficha-adenda-fecha" class="text-xs font-mono font-bold text-[#00232c]"></div>
                </div>
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

    <!-- Barra Inferior de Acciones Fija en el Sidebar -->
    <div class="bg-[#f7f2df] border-t border-[#e3dbc2] p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-2">
            <form id="ficha-cartera-form" method="POST" class="inline" onsubmit="return toggleCarteraAjax(event, currentFichaLicitacion ? currentFichaLicitacion.id : null)">
                @csrf
                <button type="submit" id="ficha-cartera-btn" class="px-3.5 py-2 border border-[#e3dbc2] bg-white rounded text-xs font-mono font-bold text-[#00232c] hover:bg-[#f2ece0] transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg id="ficha-cartera-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
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

</aside>

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
window.licitacionesStore = @json($licitaciones->keyBy('id'));
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

function openFichaModal(licOrId) {
    let lic = null;
    if (typeof licOrId === 'object' && licOrId !== null) {
        lic = licOrId;
        if (lic.id && window.licitacionesStore && window.licitacionesStore[lic.id]) {
            lic = Object.assign({}, lic, window.licitacionesStore[lic.id]);
        }
    } else if (window.licitacionesStore && window.licitacionesStore[licOrId]) {
        lic = window.licitacionesStore[licOrId];
    }
    if (!lic) return;
    currentFichaLicitacion = lic;
    
    document.getElementById('ficha-referencia-badge').textContent = lic.codigo_proceso || lic.referencia || lic.external_id || 'SIN REF';
    document.getElementById('ficha-categoria-badge').textContent = lic.categoria || 'Servicios Generales';
    document.getElementById('ficha-objeto-title').textContent = lic.objeto;
    
    // Tarjeta 1: Presupuesto y Fechas (Apertura y Cierre)
    const cuantiaVal = Number(lic.cuantia || 0);
    if (cuantiaVal <= 0) {
        document.getElementById('ficha-cuantia').textContent = 'A definir';
        document.getElementById('ficha-cuantia-raw').textContent = 'Solicitud de información / cotización previa sin cuantía fijada';
    } else {
        const symbol = lic.moneda === 'PEN' ? 'S/' : '$';
        document.getElementById('ficha-cuantia').textContent = symbol + ' ' + (cuantiaVal > 1000000 ? (cuantiaVal / 1000000).toFixed(0) + ' M' : cuantiaVal.toLocaleString());
        document.getElementById('ficha-cuantia-raw').textContent = symbol + ' ' + cuantiaVal.toLocaleString() + ' ' + (lic.moneda || 'COP');
    }
    
    // Fecha Apertura / Salida
    const aperturaEl = document.getElementById('ficha-apertura-fecha');
    if (lic.fecha_publicacion) {
        aperturaEl.textContent = lic.fecha_publicacion.substring(0, 10);
    } else {
        aperturaEl.textContent = 'No registrada en SECOP II';
    }

    // Fecha Cierre / Recepción
    const cierreEl = document.getElementById('ficha-cierre-fecha');
    const cierreDiasEl = document.getElementById('ficha-cierre-dias');
    if (lic.fecha_cierre) {
        const cierreStr = lic.fecha_cierre.substring(0, 10);
        cierreEl.textContent = cierreStr;
        
        // Calcular días restantes
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const cierreDate = new Date(cierreStr + 'T00:00:00');
        const diffTime = cierreDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays < 0) {
            cierreDiasEl.textContent = 'Proceso cerrado';
            cierreDiasEl.className = 'text-xs font-mono font-bold text-rose-700';
        } else if (diffDays === 0) {
            cierreDiasEl.textContent = '¡Cierra hoy!';
            cierreDiasEl.className = 'text-xs font-mono font-bold text-rose-700 animate-pulse';
        } else if (diffDays === 1) {
            cierreDiasEl.textContent = 'Queda 1 día (Vence mañana)';
            cierreDiasEl.className = 'text-xs font-mono font-bold text-rose-700';
        } else {
            cierreDiasEl.textContent = `Quedan ${diffDays} días`;
            cierreDiasEl.className = diffDays <= 7 ? 'text-xs font-mono font-bold text-amber-700' : 'text-xs font-mono font-bold text-emerald-700';
        }
    } else {
        cierreEl.textContent = 'Pendiente por definir';
        cierreDiasEl.textContent = '';
    }

    // Apertura efectiva de ofertas
    const aperturaOfertasBox = document.getElementById('ficha-apertura-ofertas-box');
    if (lic.fecha_apertura_ofertas) {
        document.getElementById('ficha-apertura-ofertas').textContent = lic.fecha_apertura_ofertas.substring(0, 10);
        aperturaOfertasBox.classList.remove('hidden');
    } else {
        aperturaOfertasBox.classList.add('hidden');
    }
    
    // Tarjeta 2: Entidad, NIT, Orden, Adjudicador y Ubicación
    document.getElementById('ficha-entidad').textContent = lic.entidad || 'Entidad no especificada';
    
    const ordenEl = document.getElementById('ficha-orden-entidad-label');
    if (lic.orden_entidad) {
        ordenEl.textContent = lic.orden_entidad;
        ordenEl.classList.remove('hidden');
    } else {
        ordenEl.classList.add('hidden');
    }

    const nitBox = document.getElementById('ficha-nit-box');
    if (lic.nit_entidad) {
        document.getElementById('ficha-nit').textContent = lic.nit_entidad;
        nitBox.classList.remove('hidden');
    } else {
        nitBox.classList.add('hidden');
    }

    const adjBox = document.getElementById('ficha-adjudicador-box');
    const adjEl = document.getElementById('ficha-adjudicador');
    if (lic.adjudicador) {
        adjEl.textContent = lic.adjudicador;
        adjBox.classList.remove('hidden');
    } else {
        adjBox.classList.add('hidden');
    }
    document.getElementById('ficha-ubicacion').textContent = (lic.municipio ? lic.municipio + ', ' : '') + (lic.departamento || 'Colombia');
    
    // Tarjeta 3: Modalidad, Tipo de Contrato y Código UNSPSC
    document.getElementById('ficha-modalidad').textContent = lic.tipo_proceso || 'Convocatoria Pública';
    document.getElementById('ficha-tipo-contrato-text').textContent = lic.tipo_contrato || 'Prestación de Servicios';
    
    const unspscBox = document.getElementById('ficha-unspsc-box');
    if (lic.codigo_unspsc) {
        document.getElementById('ficha-unspsc-text').textContent = 'UNSPSC: ' + lic.codigo_unspsc;
        unspscBox.classList.remove('hidden');
    } else {
        unspscBox.classList.add('hidden');
    }
    
    // Tarjeta 4: Plazo real, Competencia, Estado y Adendas
    if (lic.plazo_ejecucion_texto) {
        document.getElementById('ficha-plazo').textContent = lic.plazo_ejecucion_texto;
    } else if (lic.duracion && lic.unidad_duracion) {
        document.getElementById('ficha-plazo').textContent = `${lic.duracion} ${lic.unidad_duracion}`;
    } else if (lic.duracion) {
        document.getElementById('ficha-plazo').textContent = `${lic.duracion} día(s)`;
    } else {
        document.getElementById('ficha-plazo').textContent = 'Según pliego de condiciones';
    }
    document.getElementById('ficha-estado').textContent = (lic.fase || lic.estado || 'Publicado') + ' · abierto';
    
    const vistasEl = document.getElementById('ficha-vistas');
    if (vistasEl) {
        const provs = Number(lic.prov_unicos || 0);
        vistasEl.textContent = provs > 0 ? (provs + ' proveedor(es) con interés') : 'Sin postulaciones registradas';
    }
    const adendaBox = document.getElementById('ficha-adenda-box');
    const adendaEl = document.getElementById('ficha-adenda-fecha');
    if (lic.adenda_fecha) {
        adendaEl.textContent = lic.adenda_fecha.substring(0, 10);
        adendaBox.classList.remove('hidden');
    } else {
        adendaBox.classList.add('hidden');
    }
    
    // Objeto Full
    document.getElementById('ficha-objeto-full').textContent = lic.objeto;
    
    // URL SECOP II
    const urlBtn = document.getElementById('ficha-url-btn');
    if (lic.url_proceso) {
        urlBtn.href = lic.url_proceso;
        urlBtn.classList.remove('hidden');
    } else {
        urlBtn.classList.add('hidden');
    }
    
    // Form Cartera
    document.getElementById('ficha-cartera-form').action = '/radar/' + lic.id + '/cartera';
    renderFichaCarteraBtn(lic.en_cartera);
    
    // Gemini Affinity (Persistencia en vivo)
    renderFichaGeminiBox(lic);
    
    // Mostrar Drawer Derecho y Fondo
    document.getElementById('drawer-ficha-backdrop').classList.remove('hidden');
    const drawer = document.getElementById('drawer-ficha');
    drawer.classList.remove('translate-x-full');
    drawer.classList.add('translate-x-0');
    document.body.style.overflow = 'hidden';
}

function renderFichaCarteraBtn(enCartera) {
    const btn = document.getElementById('ficha-cartera-btn');
    const text = document.getElementById('ficha-cartera-btn-text');
    const icon = document.getElementById('ficha-cartera-icon');
    if (!btn || !text) return;

    if (enCartera) {
        text.textContent = 'En Mi Cartera';
        btn.className = 'px-3.5 py-2 border border-emerald-800 bg-emerald-700 text-white hover:bg-emerald-800 rounded text-xs font-mono font-bold transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer';
        if (icon) icon.setAttribute('fill', 'currentColor');
    } else {
        text.textContent = 'Guardar en Cartera';
        btn.className = 'px-3.5 py-2 border border-[#e3dbc2] bg-white rounded text-xs font-mono font-bold text-[#00232c] hover:bg-[#f2ece0] transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer';
        if (icon) icon.setAttribute('fill', 'none');
    }
}

async function toggleCarteraAjax(event, id) {
    if (!id) return true;
    event.preventDefault();

    try {
        const token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';
        const res = await fetch('/radar/' + id + '/cartera', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });
        const data = await res.json();
        if (data.success) {
            const enCartera = !!data.en_cartera;
            if (window.licitacionesStore && window.licitacionesStore[id]) {
                window.licitacionesStore[id].en_cartera = enCartera;
                window.licitacionesStore[id].cartera_estado = data.cartera_estado || 'Identificada';
            }
            if (currentFichaLicitacion && currentFichaLicitacion.id == id) {
                currentFichaLicitacion.en_cartera = enCartera;
                renderFichaCarteraBtn(enCartera);
            }

            const cardBtn = document.getElementById('btn-cartera-' + id);
            const cardIcon = document.getElementById('icon-cartera-' + id);
            const cardText = document.getElementById('text-cartera-' + id);
            if (cardBtn && cardText) {
                if (enCartera) {
                    cardBtn.className = 'px-3 py-1 rounded font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap bg-emerald-700 text-white hover:bg-emerald-800';
                    cardText.textContent = 'Guardado';
                    if (cardIcon) {
                        cardIcon.className = 'w-3.5 h-3.5 text-white';
                        cardIcon.setAttribute('fill', 'currentColor');
                    }
                } else {
                    cardBtn.className = 'px-3 py-1 rounded font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap bg-white text-[#00232c] border border-[#e3dbc2] hover:bg-[#f7f2df]';
                    cardText.textContent = 'Guardar';
                    if (cardIcon) {
                        cardIcon.className = 'w-3.5 h-3.5 text-[#56686b]';
                        cardIcon.setAttribute('fill', 'none');
                    }
                }
            }
        }
    } catch (e) {
        event.target.submit();
    }
    return false;
}

function closeFichaModal() {
    const drawer = document.getElementById('drawer-ficha');
    if (drawer) {
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
    }
    const backdrop = document.getElementById('drawer-ficha-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function dismissCard(cardId) {
    const card = document.getElementById(cardId);
    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.98)';
        setTimeout(() => card.remove(), 250);
    }
}

function shareLicitacion(licOrId) {
    const lic = typeof licOrId === 'object' ? licOrId : (window.licitacionesStore[licOrId] || {});
    const shareText = `Licitación: ${lic.referencia || lic.external_id}\nObjeto: ${lic.objeto}\nPresupuesto: ${lic.formatted_cuantia || ''}\nVer en SECOP II: ${lic.url_proceso || window.location.href}`;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(shareText).then(() => {
            alert('Detalles de la licitación copiados al portapapeles.');
        });
    } else {
        alert(shareText);
    }
}

function renderFichaGeminiBox(lic) {
    const box = document.getElementById('ficha-gemini-box');
    const placeholder = document.getElementById('ficha-gemini-placeholder');
    
    if (lic.score !== null && lic.score !== undefined) {
        placeholder.classList.add('hidden');
        box.classList.remove('hidden');
        
        const pill = document.getElementById('ficha-gemini-pill');
        const status = document.getElementById('ficha-gemini-status');
        
        pill.textContent = lic.score + '% AFINIDAD · ' + (lic.nivel || 'EVALUADO');
        
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
        
        document.getElementById('ficha-gemini-justificacion').textContent = lic.justificacion || 'Evaluación estratégica generada por IA contrastando con el perfil de Tornus Agency.';
        document.getElementById('ficha-gemini-puntos').textContent = lic.puntos_clave || 'Revisar términos de referencia y habilitantes jurídicos antes de postulación.';
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
            
            // Persistir en el almacén local del cliente
            if (window.licitacionesStore && window.licitacionesStore[id]) {
                Object.assign(window.licitacionesStore[id], evalData);
            }
            
            // Actualizar chip de afinidad en la tarjeta del radar
            const cardAffinity = document.getElementById('card-affinity-' + id);
            if (cardAffinity) {
                const colorClass = evalData.score >= 70 ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : (evalData.score >= 40 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-rose-50 text-rose-800 border-rose-200');
                const dotColor = evalData.score >= 70 ? 'bg-emerald-600' : (evalData.score >= 40 ? 'bg-amber-600' : 'bg-rose-600');
                
                cardAffinity.innerHTML = `
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold flex items-center gap-1 ${colorClass} border">
                        <span class="w-1.5 h-1.5 rounded-full ${dotColor}"></span>
                        <span>${evalData.score}% AFINIDAD · ${evalData.nivel}</span>
                    </span>
                `;
            }

            // Desplegar bloque descriptivo estratégico de Gemini directamente en la tarjeta
            const descBox = document.getElementById('card-gemini-box-' + id);
            const descText = document.getElementById('card-gemini-desc-' + id);
            const statusText = document.getElementById('card-gemini-status-' + id);
            const pointsText = document.getElementById('card-gemini-points-' + id);
            const pointsWrapper = document.getElementById('card-gemini-points-wrapper-' + id);

            if (descBox && descText) {
                descText.textContent = evalData.justificacion || 'Evaluación estratégica completada con Gemini IA.';
                if (statusText) {
                    statusText.textContent = evalData.score >= 70 ? 'APLICA DIRECTO' : (evalData.score >= 40 ? 'APLICA CON CONDICIONES' : 'NO APLICA');
                    statusText.className = 'text-[10px] font-bold tracking-wider ' + (evalData.score >= 70 ? 'text-emerald-800' : (evalData.score >= 40 ? 'text-amber-800' : 'text-rose-800'));
                }
                if (pointsText && evalData.puntos_clave) {
                    pointsText.textContent = evalData.puntos_clave;
                    if (pointsWrapper) pointsWrapper.classList.remove('hidden');
                }
                descBox.className = 'my-3 p-3.5 rounded-lg bg-[#fbf9f1] border border-[#e3dbc2] border-l-4 text-xs font-mono space-y-1.5 transition-all ' + 
                    (evalData.score >= 70 ? 'border-l-emerald-600' : (evalData.score >= 40 ? 'border-l-amber-500' : 'border-l-rose-500'));
                descBox.classList.remove('hidden');
            }
            
            if (btnText) btnText.textContent = 'Re-evaluar Gemini';
            
            // Si está abierta la ficha de esta licitación, actualizarla en tiempo real
            if (currentFichaLicitacion && currentFichaLicitacion.id == id) {
                Object.assign(currentFichaLicitacion, evalData);
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
