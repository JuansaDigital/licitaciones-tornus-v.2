@extends('layouts.app')

@section('title', 'Evaluación de Afinidad IA')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <div class="p-3.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono flex items-center justify-between">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Encabezado del Evaluador IA -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal">
                Calificación de Afinidad con Inteligencia Artificial
            </h1>
            <p class="text-xs font-mono text-[#56686b] mt-0.5">
                Análisis estratégico con Google Gemini frente al perfil de capacidades de Tornus Agency
            </p>
        </div>

        <form action="{{ route('afinidad.batch') }}" method="POST">
            @csrf
            <button type="submit" class="px-3.5 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
                <span>Evaluar Lote con Gemini IA</span>
            </button>
        </form>
    </div>

    <!-- Filtros y Métricas de Afinidad -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('afinidad', ['nivel' => 'all']) }}" 
            class="bg-[#fffdf6] border {{ $filterNivel === 'all' ? 'border-[#00232c] ring-1 ring-[#00232c]' : 'border-[#e3dbc2]' }} rounded-lg p-4 shadow-xs transition-all">
            <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">Total Evaluadas</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-[#00232c] mt-1">{{ $stats['total_evaluadas'] }}</div>
        </a>
        <a href="{{ route('afinidad', ['nivel' => 'ALTA']) }}" 
            class="bg-[#fffdf6] border {{ $filterNivel === 'ALTA' ? 'border-purple-600 ring-1 ring-purple-600' : 'border-[#e3dbc2]' }} rounded-lg p-4 shadow-xs transition-all">
            <span class="text-[11px] font-mono uppercase tracking-wider text-purple-700">Afinidad Alta (&gt;= 70 pts)</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-purple-800 mt-1">{{ $stats['alta'] }}</div>
        </a>
        <a href="{{ route('afinidad', ['nivel' => 'MEDIA']) }}" 
            class="bg-[#fffdf6] border {{ $filterNivel === 'MEDIA' ? 'border-blue-600 ring-1 ring-blue-600' : 'border-[#e3dbc2]' }} rounded-lg p-4 shadow-xs transition-all">
            <span class="text-[11px] font-mono uppercase tracking-wider text-blue-700">Afinidad Media (40-69 pts)</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-blue-800 mt-1">{{ $stats['media'] }}</div>
        </a>
        <a href="{{ route('afinidad', ['nivel' => 'BAJA']) }}" 
            class="bg-[#fffdf6] border {{ $filterNivel === 'BAJA' ? 'border-slate-600 ring-1 ring-slate-600' : 'border-[#e3dbc2]' }} rounded-lg p-4 shadow-xs transition-all">
            <span class="text-[11px] font-mono uppercase tracking-wider text-slate-600">Afinidad Baja (&lt; 40 pts)</span>
            <div class="font-['Instrument_Serif',serif] text-3xl font-bold text-slate-700 mt-1">{{ $stats['baja'] }}</div>
        </a>
    </div>

    <!-- Lista de Evaluaciones -->
    @if ($licitaciones->isEmpty())
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-10 text-center text-[#56686b] font-mono text-xs">
            Aún no hay licitaciones evaluadas con este filtro. Presiona "Evaluar Lote con Gemini IA" o evalúa procesos individuales desde el Radar.
        </div>
    @else
        <div class="space-y-4">
            @foreach($licitaciones as $lic)
                <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-[#f7f2df] text-[#00232c] border border-[#e3dbc2]">
                                {{ $lic->referencia ?: $lic->external_id }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded text-[11px] font-mono border {{ $lic->nivel === 'ALTA' ? 'bg-purple-50 text-purple-800 border-purple-200 font-bold' : ($lic->nivel === 'MEDIA' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                                AFINIDAD {{ $lic->nivel }} · {{ $lic->score }}/100
                            </span>
                            @if($lic->estrategia_postulacion)
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-amber-50 text-amber-800 border border-amber-200 font-semibold">
                                    {{ $lic->estrategia_postulacion }}
                                </span>
                            @endif
                        </div>

                        <div class="text-right">
                            <div class="font-['Instrument_Serif',serif] text-2xl font-bold text-[#00232c]">
                                {{ $lic->formatted_cuantia }}
                            </div>
                            <div class="text-[10px] font-mono text-[#56686b] uppercase">
                                {{ $lic->moneda }} · {{ $lic->entidad }}
                            </div>
                        </div>
                    </div>

                    <h3 class="text-base font-semibold text-[#00232c] mb-2 leading-snug">
                        {{ $lic->objeto }}
                    </h3>

                    @if($lic->justificacion)
                        <div class="bg-[#f7f2df] rounded p-3 text-xs text-[#00232c] mb-3 border border-[#e3dbc2] leading-relaxed">
                            <span class="font-mono text-[11px] font-bold block mb-1 text-[#56686b]">DICTAMEN ESTRATÉGICO IA:</span>
                            {{ $lic->justificacion }}
                        </div>
                    @endif

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

                    <div class="flex items-center justify-between pt-2 border-t border-[#f2ece0] text-xs font-mono">
                        <span class="text-[#9db3b4]">Evaluado: {{ $lic->evaluado_en ? $lic->evaluado_en->format('d/m/Y H:i') : 'Reciente' }}</span>
                        
                        <form action="{{ route('radar.cartera', $lic->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded font-semibold text-xs transition-colors flex items-center gap-1.5 {{ $lic->en_cartera ? 'bg-emerald-700 text-white' : 'bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57]' }}">
                                <span>{{ $lic->en_cartera ? 'En Cartera' : '+ Agregar a Cartera' }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $licitaciones->links() }}
        </div>
    @endif

</div>
@endsection

