@extends('layouts.app')

@section('title', 'Seleccionar Mercado')

@section('content')
<div class="max-w-5xl mx-auto py-6 sm:py-10 space-y-8">

    <!-- Encabezado de la pantalla -->
    <div class="text-center max-w-2xl mx-auto space-y-3">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-[#fffdf6] border border-[#e3dbc2] text-xs font-mono text-[#56686b]">
            <span class="w-2 h-2 rounded-full bg-[#0b4a57]"></span>
            <span>AISLAMIENTO DE MERCADOS MULTI-TENANT</span>
        </div>
        <h1 class="font-['Instrument_Serif',serif] text-3xl sm:text-5xl text-[#00232c] font-normal tracking-tight">
            Selecciona el Mercado Operativo
        </h1>
        <p class="text-sm font-sans text-[#56686b] leading-relaxed">
            Cada país opera con una base de datos física independiente, su respectiva moneda local y su normativa de contratación pública para garantizar que nunca ocurra contaminación cruzada.
        </p>
    </div>

    <!-- Rejilla de Selección de Países -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">

        <!-- TARJETA COLOMBIA -->
        <div class="bg-[#fffdf6] border-2 {{ $currentMarket === 'CO' ? 'border-[#00232c] shadow-md' : 'border-[#e3dbc2] hover:border-[#00232c]' }} rounded-xl p-6 sm:p-8 flex flex-col justify-between transition-all group">
            <div class="space-y-6">
                
                <!-- Encabezado de la Tarjeta -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-lg bg-[#00232c] text-[#fbfb75] font-mono font-bold text-lg flex items-center justify-center shadow-xs">
                            CO
                        </span>
                        <div>
                            <h2 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal">
                                Colombia
                            </h2>
                            <span class="text-xs font-mono text-[#56686b]">SECOP II · Colombia Compra Eficiente</span>
                        </div>
                    </div>

                    @if($currentMarket === 'CO')
                        <span class="px-2.5 py-1 rounded text-[11px] font-mono font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Activo</span>
                        </span>
                    @endif
                </div>

                <!-- Detalles Técnicos del Mercado -->
                <div class="space-y-3 pt-2 border-t border-[#f2ece0] text-xs font-mono text-[#56686b]">
                    <div class="flex items-center justify-between py-1 border-b border-[#f2ece0]">
                        <span class="text-[#9db3b4]">Fuente Oficial:</span>
                        <span class="text-[#00232c] font-semibold">API Socrata v3 (datos.gov.co)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#f2ece0]">
                        <span class="text-[#9db3b4]">Moneda Oficial:</span>
                        <span class="text-[#00232c] font-semibold">Pesos Colombianos (COP · $)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#f2ece0]">
                        <span class="text-[#9db3b4]">Marco Normativo:</span>
                        <span class="text-[#00232c] font-medium">Ley 80 / Dec. 1082 (32 Dptos)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#f2ece0]">
                        <span class="text-[#9db3b4]">Base de Datos:</span>
                        <code class="bg-[#f7f2df] px-1.5 py-0.5 rounded text-[11px] text-[#00232c]">licitaciones_co.sqlite</code>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-[#9db3b4]">Procesos en Base:</span>
                        <span class="text-[#00232c] font-bold text-sm">{{ number_format($colombiaCount) }}</span>
                    </div>
                </div>

            </div>

            <!-- Botón de Selección -->
            <div class="pt-6 mt-6 border-t border-[#f2ece0]">
                <a href="{{ route('market.set', 'CO') }}" 
                    class="w-full py-3 px-4 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded-lg text-xs font-mono font-semibold tracking-wider transition-all flex items-center justify-center gap-2 shadow-xs group-hover:shadow-sm">
                    <span>OPERAR EN COLOMBIA</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <!-- TARJETA PERÚ -->
        <div class="bg-[#fffdf6] border-2 {{ $currentMarket === 'PE' ? 'border-[#00232c] shadow-md' : 'border-[#e3dbc2] hover:border-[#00232c]' }} rounded-xl p-6 sm:p-8 flex flex-col justify-between transition-all group">
            <div class="space-y-6">
                
                <!-- Encabezado de la Tarjeta -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-lg bg-[#00232c] text-[#fbfb75] font-mono font-bold text-lg flex items-center justify-center shadow-xs">
                            PE
                        </span>
                        <div>
                            <h2 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal">
                                Perú
                            </h2>
                            <span class="text-xs font-mono text-[#56686b]">SEACE · Organismo de Contrataciones (OECE)</span>
                        </div>
                    </div>

                    @if($currentMarket === 'PE')
                        <span class="px-2.5 py-1 rounded text-[11px] font-mono font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Activo</span>
                        </span>
                    @endif
                </div>

                <!-- Detalles Técnicos del Mercado -->
                <div class="space-y-3 pt-2 border-t border-[#f2ece0] text-xs font-mono text-[#56686b]">
                    <div class="flex items-center justify-between py-1 border-b border-[#f2ece0]">
                        <span class="text-[#9db3b4]">Fuente Oficial:</span>
                        <span class="text-[#00232c] font-semibold">API OCDS de Contrataciones Abiertas</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#f2ece0]">
                        <span class="text-[#9db3b4]">Moneda Oficial:</span>
                        <span class="text-[#00232c] font-semibold">Soles Peruanos (PEN · S/)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#f2ece0]">
                        <span class="text-[#9db3b4]">Marco Normativo:</span>
                        <span class="text-[#00232c] font-medium">Ley 30225 de Contrataciones (25 Regiones)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#f2ece0]">
                        <span class="text-[#9db3b4]">Base de Datos:</span>
                        <code class="bg-[#f7f2df] px-1.5 py-0.5 rounded text-[11px] text-[#00232c]">licitaciones_pe.sqlite</code>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-[#9db3b4]">Procesos en Base:</span>
                        <span class="text-[#00232c] font-bold text-sm">{{ number_format($peruCount) }}</span>
                    </div>
                </div>

            </div>

            <!-- Botón de Selección -->
            <div class="pt-6 mt-6 border-t border-[#f2ece0]">
                <a href="{{ route('market.set', 'PE') }}" 
                    class="w-full py-3 px-4 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded-lg text-xs font-mono font-semibold tracking-wider transition-all flex items-center justify-center gap-2 shadow-xs group-hover:shadow-sm">
                    <span>OPERAR EN PERÚ</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>

    </div>

    <!-- Nota inferior de contexto -->
    <div class="text-center pt-4">
        <p class="text-xs font-mono text-[#56686b]">
            Puedes alternar entre mercados en cualquier momento desde la barra superior de la plataforma.
        </p>
    </div>

</div>
@endsection

