@extends('layouts.app')

@section('title', 'Mi Cartera')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <div class="p-3.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono flex items-center justify-between">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Encabezado del Tablero Comercial -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal">
                Mi Cartera Comercial · {{ App\Services\MarketService::marketName() }}
            </h1>
            <p class="text-xs font-mono text-[#56686b] mt-0.5">
                Seguimiento comercial y pipeline de oportunidades postuladas
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Feed de Calendario iCal -->
            <a href="{{ route('calendar.feed', App\Services\MarketService::current()) }}" 
                class="px-3 py-2 bg-white border border-[#e3dbc2] hover:border-[#00232c] rounded text-xs font-mono text-[#00232c] font-medium transition-colors flex items-center gap-1.5 shadow-xs"
                title="Suscribir Google Calendar u Outlook a estas fechas">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Sincronizar Calendario (.ics)</span>
            </a>

            <!-- Exportar CSV -->
            <a href="{{ route('cartera.export') }}" 
                class="px-3.5 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Exportar Cartera</span>
            </a>
        </div>
    </div>

    <!-- Métricas del Pipeline -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-4 shadow-xs">
            <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">Oportunidades en Cartera</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-[#00232c] mt-1">{{ $totalEnCartera }}</div>
        </div>
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-4 shadow-xs">
            <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">Valor Total en Seguimiento</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-[#00232c] mt-1">
                {{ App\Services\MarketService::currencySymbol() }} {{ number_format($valorTotal, 2) }}
            </div>
        </div>
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-4 shadow-xs">
            <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">En Preparación Activa</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-amber-700 mt-1">
                {{ count($kanban['En Preparación'] ?? []) }}
            </div>
        </div>
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-4 shadow-xs">
            <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">Adjudicadas (Ganadas)</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-emerald-700 mt-1">
                {{ count($kanban['Adjudicada'] ?? []) }}
            </div>
        </div>
    </div>

    <!-- TABLERO KANBAN DE 5 COLUMNAS -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-start overflow-x-auto pb-4">
        @foreach(App\Http\Controllers\CarteraController::ESTADOS as $estado)
            @php
                $cards = $kanban[$estado] ?? collect();
                $headerColor = match($estado) {
                    'Identificada' => 'border-t-4 border-t-slate-400',
                    'En Preparación' => 'border-t-4 border-t-amber-500',
                    'Presentada' => 'border-t-4 border-t-blue-500',
                    'Adjudicada' => 'border-t-4 border-t-emerald-500',
                    'Descartada' => 'border-t-4 border-t-rose-400',
                    default => 'border-t-4 border-t-[#00232c]'
                };
            @endphp
            <div class="bg-[#f2ece0] rounded-lg p-3 border border-[#e3dbc2] {{ $headerColor }} flex flex-col min-h-[450px]">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="font-mono text-xs font-bold text-[#00232c] uppercase tracking-wide">{{ $estado }}</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-[#fffdf6] text-[#56686b] border border-[#e3dbc2] font-semibold">
                        {{ $cards->count() }}
                    </span>
                </div>

                <div class="space-y-3 flex-1">
                    @forelse($cards as $lic)
                        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded p-3 shadow-xs hover:border-[#00232c] transition-all">
                            <div class="flex items-center justify-between text-[10px] font-mono text-[#56686b] mb-1.5">
                                <span class="font-bold text-[#00232c]">{{ $lic->referencia ?: $lic->external_id }}</span>
                                <span class="text-emerald-700 font-semibold">{{ $lic->formatted_cuantia }}</span>
                            </div>

                            <p class="text-xs text-[#00232c] font-medium leading-snug line-clamp-3 mb-2">
                                {{ $lic->objeto }}
                            </p>

                            <div class="text-[10px] font-mono text-[#56686b] mb-2 space-y-0.5">
                                <div><span class="text-[#9db3b4]">Entidad:</span> {{ Str::limit($lic->entidad, 30) }}</div>
                                <div><span class="text-[#9db3b4]">Cierre:</span> {{ $lic->fecha_cierre ? substr($lic->fecha_cierre, 0, 10) : 'N/A' }}</div>
                            </div>

                            @if($lic->score !== null)
                                <div class="mb-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono {{ $lic->nivel === 'ALTA' ? 'bg-purple-50 text-purple-700 border border-purple-200 font-semibold' : 'bg-slate-100 text-slate-600' }}">
                                        Afinidad: {{ $lic->nivel }} ({{ $lic->score }} pts)
                                    </span>
                                </div>
                            @endif

                            <!-- Cambio de Estado y Notas -->
                            <form action="{{ route('cartera.status', $lic->id) }}" method="POST" class="pt-2 border-t border-[#f2ece0] space-y-2">
                                @csrf
                                <div class="flex items-center gap-1.5">
                                    <select name="cartera_estado" onchange="this.form.submit()" class="w-full text-[10px] font-mono bg-white border border-[#e3dbc2] rounded px-1.5 py-1 text-[#00232c]">
                                        @foreach(App\Http\Controllers\CarteraController::ESTADOS as $opt)
                                            <option value="{{ $opt }}" {{ $lic->cartera_estado === $opt ? 'selected' : '' }}>
                                                Mover a: {{ $opt }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="text" name="notas" value="{{ $lic->notas }}" placeholder="Nota interna..." 
                                    onchange="this.form.submit()"
                                    class="w-full text-[10px] font-sans bg-white border border-[#e3dbc2] rounded px-2 py-1 placeholder-[#9db3b4] text-[#00232c]">
                            </form>
                        </div>
                    @empty
                        <div class="h-32 flex items-center justify-center text-[11px] font-mono text-[#9db3b4] border border-dashed border-[#e3dbc2] rounded">
                            Sin licitaciones
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection

