@extends('layouts.app')

@section('title', 'Propuestas analizadas con IA - Tornus')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <div class="p-3.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-mono text-sm cursor-pointer">✕</button>
        </div>
    @endif

    <!-- Encabezado Institucional -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#00232c] text-[#fbfb75] uppercase">
                    MÓDULO DE INTELIGENCIA
                </span>
                <span class="text-xs font-mono text-[#56686b]">Google Gemini 2.5 Flash</span>
            </div>
            <h1 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal">
                Propuestas analizadas con IA
            </h1>
            <p class="text-xs font-mono text-[#56686b] mt-0.5">
                Historial de licitaciones evaluadas estratégicamente frente a la base de conocimiento y capacidades de Tornus Agency.
            </p>
        </div>

        <form action="{{ route('afinidad.batch') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs cursor-pointer whitespace-nowrap">
                <svg class="w-3.5 h-3.5 text-[#fbfb75]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L9.5 9.5L2 12L9.5 14.5L12 22L14.5 14.5L22 12L14.5 9.5L12 2Z"/>
                </svg>
                <span>Evaluar Lote Pendiente (10)</span>
            </button>
        </form>
    </div>

    <!-- Filtros por Nivel de Afinidad (Métricas) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('afinidad', array_merge(request()->query(), ['nivel' => 'all'])) }}" 
            class="bg-[#fffdf6] border {{ $filterNivel === 'all' ? 'border-[#00232c] ring-1 ring-[#00232c]' : 'border-[#e3dbc2]' }} rounded-lg p-4 shadow-xs transition-all hover:border-[#00232c]">
            <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b] block">Total Analizadas</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-[#00232c] mt-1">{{ $stats['total_evaluadas'] }}</div>
        </a>
        <a href="{{ route('afinidad', array_merge(request()->query(), ['nivel' => 'ALTA'])) }}" 
            class="bg-[#fffdf6] border {{ $filterNivel === 'ALTA' ? 'border-emerald-600 ring-1 ring-emerald-600' : 'border-[#e3dbc2]' }} rounded-lg p-4 shadow-xs transition-all hover:border-emerald-600">
            <span class="text-[11px] font-mono uppercase tracking-wider text-emerald-800 block font-semibold">Afinidad Alta (&gt;= 70 pts)</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-emerald-800 mt-1">{{ $stats['alta'] }}</div>
        </a>
        <a href="{{ route('afinidad', array_merge(request()->query(), ['nivel' => 'MEDIA'])) }}" 
            class="bg-[#fffdf6] border {{ $filterNivel === 'MEDIA' ? 'border-amber-600 ring-1 ring-amber-600' : 'border-[#e3dbc2]' }} rounded-lg p-4 shadow-xs transition-all hover:border-amber-600">
            <span class="text-[11px] font-mono uppercase tracking-wider text-amber-800 block font-semibold">Afinidad Media (40-69 pts)</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-amber-800 mt-1">{{ $stats['media'] }}</div>
        </a>
        <a href="{{ route('afinidad', array_merge(request()->query(), ['nivel' => 'BAJA'])) }}" 
            class="bg-[#fffdf6] border {{ $filterNivel === 'BAJA' ? 'border-rose-600 ring-1 ring-rose-600' : 'border-[#e3dbc2]' }} rounded-lg p-4 shadow-xs transition-all hover:border-rose-600">
            <span class="text-[11px] font-mono uppercase tracking-wider text-rose-800 block font-semibold">Afinidad Baja (&lt; 40 pts)</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-rose-800 mt-1">{{ $stats['baja'] }}</div>
        </a>
    </div>

    <!-- Barra de Filtros por Fechas -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-4 shadow-xs">
        <form method="GET" action="{{ route('afinidad') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 text-xs font-mono">
            <input type="hidden" name="nivel" value="{{ $filterNivel }}">

            <!-- Presets rápidos de fecha -->
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[#56686b] font-bold uppercase mr-1">Fecha de análisis:</span>
                
                <a href="{{ route('afinidad', array_merge(request()->query(), ['periodo' => 'all', 'desde' => null, 'hasta' => null])) }}"
                    class="px-2.5 py-1 rounded border transition-colors {{ ($filterPeriodo === 'all' && empty($fechaDesde)) ? 'bg-[#00232c] text-[#fbfb75] border-[#00232c] font-bold' : 'bg-white text-[#56686b] border-[#e3dbc2] hover:bg-[#f7f2df]' }}">
                    Todas
                </a>

                <a href="{{ route('afinidad', array_merge(request()->query(), ['periodo' => 'today', 'desde' => null, 'hasta' => null])) }}"
                    class="px-2.5 py-1 rounded border transition-colors {{ $filterPeriodo === 'today' ? 'bg-[#00232c] text-[#fbfb75] border-[#00232c] font-bold' : 'bg-white text-[#56686b] border-[#e3dbc2] hover:bg-[#f7f2df]' }}">
                    Hoy
                </a>

                <a href="{{ route('afinidad', array_merge(request()->query(), ['periodo' => '7d', 'desde' => null, 'hasta' => null])) }}"
                    class="px-2.5 py-1 rounded border transition-colors {{ $filterPeriodo === '7d' ? 'bg-[#00232c] text-[#fbfb75] border-[#00232c] font-bold' : 'bg-white text-[#56686b] border-[#e3dbc2] hover:bg-[#f7f2df]' }}">
                    Últimos 7 días
                </a>

                <a href="{{ route('afinidad', array_merge(request()->query(), ['periodo' => '30d', 'desde' => null, 'hasta' => null])) }}"
                    class="px-2.5 py-1 rounded border transition-colors {{ $filterPeriodo === '30d' ? 'bg-[#00232c] text-[#fbfb75] border-[#00232c] font-bold' : 'bg-white text-[#56686b] border-[#e3dbc2] hover:bg-[#f7f2df]' }}">
                    Últimos 30 días
                </a>

                <a href="{{ route('afinidad', array_merge(request()->query(), ['periodo' => '5m', 'desde' => null, 'hasta' => null])) }}"
                    class="px-2.5 py-1 rounded border transition-colors {{ $filterPeriodo === '5m' ? 'bg-[#00232c] text-[#fbfb75] border-[#00232c] font-bold' : 'bg-white text-[#56686b] border-[#e3dbc2] hover:bg-[#f7f2df]' }}">
                    Últimos 5 meses
                </a>
            </div>

            <!-- Rango manual de fechas Desde - Hasta -->
            <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-[#e3dbc2]">
                <div class="flex items-center gap-1">
                    <span class="text-[#56686b]">Desde:</span>
                    <input type="date" name="desde" value="{{ $fechaDesde }}"
                        class="px-2 py-1 bg-white border border-[#e3dbc2] rounded text-xs text-[#00232c] focus:outline-none focus:border-[#00232c]">
                </div>

                <div class="flex items-center gap-1">
                    <span class="text-[#56686b]">Hasta:</span>
                    <input type="date" name="hasta" value="{{ $fechaHasta }}"
                        class="px-2 py-1 bg-white border border-[#e3dbc2] rounded text-xs text-[#00232c] focus:outline-none focus:border-[#00232c]">
                </div>

                <button type="submit" class="px-3 py-1 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded font-semibold transition-colors cursor-pointer">
                    Filtrar
                </button>

                @if(!empty($fechaDesde) || !empty($fechaHasta) || $filterPeriodo !== 'all')
                    <a href="{{ route('afinidad', ['nivel' => $filterNivel]) }}" class="px-2 py-1 text-rose-700 hover:underline">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Barra de Selección Conjunta / Acciones Masivas -->
    @if ($licitaciones->isNotEmpty())
        <div class="bg-[#f7f2df] border border-[#e3dbc2] rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 cursor-pointer font-medium text-[#00232c]">
                    <input type="checkbox" id="master-checkbox" onchange="toggleSelectAll(this)"
                        class="w-4 h-4 rounded border-[#e3dbc2] text-[#00232c] focus:ring-[#00232c] cursor-pointer">
                    <span>Seleccionar todas las propuestas visibles</span>
                </label>

                <span id="selected-badge" class="hidden px-2.5 py-0.5 rounded-full bg-[#00232c] text-[#fbfb75] font-bold">
                    <span id="selected-count">0</span> seleccionadas
                </span>
            </div>

            <!-- Botón de eliminación en lote (activo cuando hay seleccionadas) -->
            <button type="button" id="btn-bulk-delete" onclick="confirmBulkDelete()"
                class="hidden px-3.5 py-1.5 bg-rose-50 border border-rose-300 hover:bg-rose-100 text-rose-800 rounded font-bold transition-colors items-center gap-1.5 shadow-xs cursor-pointer">
                <svg class="w-3.5 h-3.5 text-rose-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                <span>ELIMINAR SELECCIONADAS</span>
            </button>
        </div>
    @endif

    <!-- Formulario Oculto para Bulk Delete -->
    <form id="bulk-delete-form" action="{{ route('afinidad.bulk-destroy') }}" method="POST" class="hidden">
        @csrf
        <div id="bulk-delete-inputs"></div>
    </form>

    <!-- Lista de Propuestas Analizadas -->
    @if ($licitaciones->isEmpty())
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-12 text-center text-[#56686b] font-mono text-xs space-y-2">
            <svg class="w-8 h-8 text-[#9db3b4] mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p class="font-bold text-[#00232c] text-sm">No se encontraron propuestas analizadas con los filtros seleccionados.</p>
            <p>Puedes evaluar nuevos procesos desde el Radar en Vivo o utilizar el botón "Evaluar Lote Pendiente".</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($licitaciones as $lic)
                <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs hover:border-[#0b4a57] transition-all">
                    
                    <!-- Fila Superior: Checkbox + Badges + Presupuesto -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <!-- Checkbox de selección individual -->
                            <input type="checkbox" value="{{ $lic->id }}" onchange="updateSelectedCount()"
                                class="item-checkbox w-4 h-4 rounded border-[#e3dbc2] text-[#00232c] focus:ring-[#00232c] cursor-pointer mt-0.5 shrink-0">

                            <!-- Badges -->
                            <div class="flex flex-wrap items-center gap-1.5 font-mono text-[11px]">
                                <span class="px-2 py-0.5 rounded font-bold bg-[#f7f2df] text-[#00232c] border border-[#e3dbc2] whitespace-nowrap shrink-0">
                                    {{ $lic->codigo_proceso }}
                                </span>

                                <span class="px-2.5 py-0.5 rounded-full border whitespace-nowrap shrink-0 {{ $lic->score >= 70 ? 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold' : ($lic->score >= 40 ? 'bg-amber-50 text-amber-800 border-amber-300 font-bold' : 'bg-rose-50 text-rose-800 border-rose-300 font-bold') }}">
                                    {{ $lic->score }}% AFINIDAD · {{ $lic->nivel }}
                                </span>

                                @if($lic->estrategia_postulacion)
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-800 border border-blue-200 font-semibold whitespace-nowrap shrink-0">
                                        {{ $lic->estrategia_postulacion }}
                                    </span>
                                @endif

                                <span class="px-2 py-0.5 rounded bg-[#00232c] text-[#fbfb75] font-bold whitespace-nowrap shrink-0">
                                    {{ $lic->departamento ?: 'Nacional' }}
                                </span>
                            </div>
                        </div>

                        <!-- Presupuesto Oficial -->
                        <div class="text-right shrink-0">
                            @if(empty($lic->cuantia) || (float)$lic->cuantia <= 0)
                                <div class="font-bold text-xs sm:text-[13px] text-amber-900 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded inline-block tracking-tight mb-1 whitespace-nowrap">
                                    A definir (RFI / Cotización)
                                </div>
                            @else
                                <div class="font-bold text-xl sm:text-2xl text-[#00232c] whitespace-nowrap">
                                    {{ $lic->formatted_cuantia }}
                                </div>
                            @endif
                            <div class="text-[10px] font-mono text-[#56686b] uppercase">
                                {{ $lic->moneda }} · {{ Str::limit($lic->entidad, 35) }}
                            </div>
                        </div>
                    </div>

                    <!-- Objeto de la licitación -->
                    <h3 class="text-sm sm:text-base font-bold text-[#00232c] mb-2 leading-snug break-words">
                        {{ $lic->objeto }}
                    </h3>

                    <!-- Dictamen Estratégico IA -->
                    @if($lic->justificacion)
                        <div class="bg-[#fbf9f1] rounded p-3 text-xs text-[#00232c] mb-3 border border-[#e3dbc2] leading-relaxed space-y-1.5">
                            <div>
                                <span class="font-mono text-[10px] font-bold uppercase text-[#56686b] tracking-wider block">DICTAMEN ESTRATÉGICO IA:</span>
                                <p class="text-[#3a4749]">{{ $lic->justificacion }}</p>
                            </div>
                            @if($lic->puntos_clave)
                                <div class="pt-1 border-t border-[#e3dbc2]/60 font-mono text-[11px]">
                                    <strong class="text-[#00232c]">Punto clave a revisar:</strong>
                                    <span class="text-[#56686b]">{{ $lic->puntos_clave }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Servicios sugeridos -->
                    @if(!empty($lic->servicios_sugeridos) && is_array($lic->servicios_sugeridos))
                        <div class="flex flex-wrap items-center gap-1.5 mb-3">
                            <span class="text-[11px] font-mono text-[#56686b] mr-1">Servicios sugeridos:</span>
                            @foreach($lic->servicios_sugeridos as $serv)
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-white border border-[#e3dbc2] text-[#00232c]">
                                    {{ $serv }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Barra Inferior: Metadatos de Fechas a la izquierda y Acciones a la derecha -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-[#f2ece0] text-xs font-mono">
                        
                        <!-- Fechas Clave -->
                        <div class="flex flex-wrap items-center gap-3 text-[#56686b]">
                            <span>Evaluado: <strong class="text-[#00232c]">{{ $lic->evaluado_en ? $lic->evaluado_en->format('d/m/Y H:i') : 'Reciente' }}</strong></span>
                            @if($lic->fecha_publicacion)
                                <span>Apertura: <strong class="text-[#00232c]">{{ substr($lic->fecha_publicacion, 0, 10) }}</strong></span>
                            @endif
                            @if($lic->fecha_cierre)
                                <span>Cierre: <strong class="text-[#00232c]">{{ substr($lic->fecha_cierre, 0, 10) }}</strong></span>
                            @endif
                        </div>

                        <!-- Botones de Acción: Eliminar Uno a Uno, Cartera y Ver en SECOP -->
                        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                            
                            <!-- Botón Eliminar Una a Una -->
                            <form action="{{ route('afinidad.destroy', $lic->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Deseas eliminar esta propuesta analizada con IA?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    class="px-2.5 py-1 bg-white border border-[#e3dbc2] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 rounded text-[#56686b] font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap"
                                    title="Eliminar esta propuesta analizada">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                    <span>Eliminar</span>
                                </button>
                            </form>

                            <!-- Botón Guardar en Cartera -->
                            <form action="{{ route('radar.cartera', $lic->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded font-medium transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap {{ $lic->en_cartera ? 'bg-emerald-700 text-white hover:bg-emerald-800' : 'bg-white text-[#00232c] border border-[#e3dbc2] hover:bg-[#f7f2df]' }}">
                                    <svg class="w-3.5 h-3.5 {{ $lic->en_cartera ? 'text-white' : 'text-[#56686b]' }}" fill="{{ $lic->en_cartera ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                    <span>{{ $lic->en_cartera ? 'En Cartera' : 'Guardar' }}</span>
                                </button>
                            </form>

                            <!-- Enlace a SECOP II si existe -->
                            @if($lic->url_proceso)
                                <a href="{{ $lic->url_proceso }}" target="_blank" rel="noopener noreferrer"
                                    class="px-2.5 py-1 bg-white border border-[#00232c] hover:bg-[#00232c] text-[#00232c] hover:text-white rounded font-medium transition-colors flex items-center gap-1 whitespace-nowrap">
                                    <span>Ver en SECOP II</span>
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

<script>
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = master.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    const count = checkboxes.length;
    const badge = document.getElementById('selected-badge');
    const countSpan = document.getElementById('selected-count');
    const bulkBtn = document.getElementById('btn-bulk-delete');
    const master = document.getElementById('master-checkbox');

    if (countSpan) countSpan.textContent = count;

    if (count > 0) {
        if (badge) badge.classList.remove('hidden');
        if (bulkBtn) {
            bulkBtn.classList.remove('hidden');
            bulkBtn.classList.add('flex');
        }
    } else {
        if (badge) badge.classList.add('hidden');
        if (bulkBtn) {
            bulkBtn.classList.add('hidden');
            bulkBtn.classList.remove('flex');
        }
        if (master) master.checked = false;
    }
}

function confirmBulkDelete() {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) {
        alert('Por favor selecciona al menos una propuesta analizada.');
        return;
    }

    if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente las ${checked.length} propuestas analizadas seleccionadas?`)) {
        return;
    }

    const form = document.getElementById('bulk-delete-form');
    const container = document.getElementById('bulk-delete-inputs');
    container.innerHTML = '';

    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    form.submit();
}
</script>
@endsection
