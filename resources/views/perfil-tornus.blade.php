@extends('layouts.app')

@section('title', 'Base de Conocimiento · Tornus S.A.S.')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <div class="p-3.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <!-- Contenedor Principal del Perfil -->
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-6 sm:p-8 shadow-xs">
        
        <!-- Encabezado Institucional -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#e3dbc2]">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-10 h-10 rounded-full bg-[#00232c]/5 border border-[#e3dbc2] flex items-center justify-center text-[#00232c] shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-[#56686b] bg-[#fbf9f1] border border-[#e3dbc2] px-2 py-0.5 rounded">
                            Contexto Maestro para Evaluador IA (Gemini)
                        </span>
                        <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                            RUP Vigente #132704
                        </span>
                    </div>
                    <h1 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal tracking-tight mt-1">
                        Perfil Corporativo e Inteligencia Estratégica · Tornus S.A.S.
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-mono font-bold text-[#00232c]">Capital de Trabajo RUP</div>
                    <div class="text-[11px] font-mono text-[#56686b]">$5.058.026.000 COP</div>
                </div>
                <div class="h-8 w-px bg-[#e3dbc2] hidden sm:block"></div>
                <a href="{{ route('radar') }}" class="px-4 py-2 border border-[#e3dbc2] bg-white rounded text-xs font-mono font-semibold text-[#56686b] hover:bg-[#f7f2df] transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    <span>Ir al Radar</span>
                </a>
            </div>
        </div>

        <!-- Barra de Navegación de Pestañas Adaptable (Sin cortes ni desbordamientos) -->
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 border-b border-[#e3dbc2] pb-3 text-xs font-mono" id="profileTabsNav">
            <button type="button" onclick="showProfileTab('juridica')" id="tabBtn-juridica" class="profile-tab-btn px-3.5 py-2 rounded-md border border-[#00232c] bg-[#00232c] text-[#fbfb75] font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="17" y2="12"></line><line x1="7" y1="16" x2="13" y2="16"></line></svg>
                <span>Ficha Jurídica & RUP</span>
            </button>
            <button type="button" onclick="showProfileTab('lineas')" id="tabBtn-lineas" class="profile-tab-btn px-3.5 py-2 rounded-md border border-[#e3dbc2] bg-white text-[#56686b] hover:text-[#00232c] hover:bg-[#f7f2df] transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                <span>9 Líneas de Servicio (A-I)</span>
            </button>
            <button type="button" onclick="showProfileTab('experiencia')" id="tabBtn-experiencia" class="profile-tab-btn px-3.5 py-2 rounded-md border border-[#e3dbc2] bg-white text-[#56686b] hover:text-[#00232c] hover:bg-[#f7f2df] transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Experiencia RUP (11.998 SMMLV)</span>
            </button>
            <button type="button" onclick="showProfileTab('portafolio')" id="tabBtn-portafolio" class="profile-tab-btn px-3.5 py-2 rounded-md border border-[#e3dbc2] bg-white text-[#56686b] hover:text-[#00232c] hover:bg-[#f7f2df] transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                <span>Portafolio & Clientes</span>
            </button>
            <button type="button" onclick="showProfileTab('equipo')" id="tabBtn-equipo" class="profile-tab-btn px-3.5 py-2 rounded-md border border-[#e3dbc2] bg-white text-[#56686b] hover:text-[#00232c] hover:bg-[#f7f2df] transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Equipo Senior</span>
            </button>
            <button type="button" onclick="showProfileTab('filtros')" id="tabBtn-filtros" class="profile-tab-btn px-3.5 py-2 rounded-md border border-[#e3dbc2] bg-white text-[#56686b] hover:text-[#00232c] hover:bg-[#f7f2df] transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <span class="font-bold">Filtros de Rastreo (Por País)</span>
            </button>
            <button type="button" onclick="showProfileTab('evaluador')" id="tabBtn-evaluador" class="profile-tab-btn px-3.5 py-2 rounded-md border border-[#e3dbc2] bg-white text-[#56686b] hover:text-[#00232c] hover:bg-[#f7f2df] transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <span>Metodología & Rúbrica IA</span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: FICHA JURÍDICA & CAPACIDAD FINANCIERA -->
        <!-- ========================================== -->
        <div id="tabContent-juridica" class="profile-tab-pane pt-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Ficha Jurídica -->
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-5">
                    <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-[#00232c] flex items-center gap-2 mb-4 pb-2 border-b border-[#e3dbc2]">
                        <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Datos Societarios y Registro RUP
                    </h3>
                    <dl class="space-y-2.5 text-xs font-mono">
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Razón Social:</dt>
                            <dd class="font-bold text-[#00232c]">{{ $profile['nombre_agencia'] ?? 'TORNUS S.A.S.' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Marca Comercial:</dt>
                            <dd class="font-bold text-[#00232c]">{{ $profile['marca_comercial'] ?? 'Tornus Agency' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">NIT:</dt>
                            <dd class="font-semibold text-[#00232c]">{{ $profile['nit'] ?? '901.328.143-2' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Registro RUP:</dt>
                            <dd class="font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                No. {{ $profile['rup']['numero'] ?? '132704' }} ({{ $profile['rup']['camara'] ?? 'CC Cali' }})
                            </dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Constitución:</dt>
                            <dd class="text-[#00232c]">02 de octubre de 2019</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Tamaño Empresarial:</dt>
                            <dd class="text-[#00232c]">Mediana Empresa</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Régimen Sectorial:</dt>
                            <dd class="text-[#00232c] font-semibold">Economía Naranja (Industrias Creativas)</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Representación Legal:</dt>
                            <dd class="text-[#00232c] text-right">Dagoberto Ospina Ospina<br><span class="text-[10px] text-[#56686b]">Suplente: Dagoberto Ospina Jaramillo</span></dd>
                        </div>
                        <div class="flex justify-between py-1">
                            <dt class="text-[#56686b]">Domicilio:</dt>
                            <dd class="text-[#00232c]">Cali, Valle del Cauca (Kr 68 # 13B-61 Of. 903A)</dd>
                        </div>
                    </dl>
                </div>

                <!-- Capacidad Financiera RUP -->
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-5">
                    <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-[#00232c] flex items-center gap-2 mb-4 pb-2 border-b border-[#e3dbc2]">
                        <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        Capacidad Financiera RUP (Corte 31/12/2025)
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-3">
                            <div class="text-[10px] font-mono text-[#56686b] uppercase">Capital de Trabajo</div>
                            <div class="text-sm font-mono font-bold text-[#00232c] mt-0.5">$5.058.026.000</div>
                            <div class="text-[10px] font-mono text-emerald-700 mt-1">Holgura de liquidez</div>
                        </div>
                        <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-3">
                            <div class="text-[10px] font-mono text-[#56686b] uppercase">Patrimonio Neto</div>
                            <div class="text-sm font-mono font-bold text-[#00232c] mt-0.5">$5.708.026.000</div>
                            <div class="text-[10px] font-mono text-[#56686b] mt-1">Activo Total: $6.413M COP</div>
                        </div>
                    </div>

                    <dl class="space-y-2 text-xs font-mono">
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Índice de Liquidez:</dt>
                            <dd class="font-bold text-emerald-800">8,17 <span class="font-normal text-[10px] text-[#56686b]">(Exigido en pliegos: >= 1,2 a 1,5)</span></dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Índice de Endeudamiento:</dt>
                            <dd class="font-bold text-emerald-800">0,11 (11%) <span class="font-normal text-[10px] text-[#56686b]">(Exigido: <= 0,60 a 0,70)</span></dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Cobertura de Intereses:</dt>
                            <dd class="font-bold text-[#00232c]">76,70 veces <span class="font-normal text-[10px] text-[#56686b]">(Exigido: >= 1,5 a 2,5)</span></dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#f0ebd8]">
                            <dt class="text-[#56686b]">Rentabilidad Patrimonio (ROE):</dt>
                            <dd class="font-semibold text-[#00232c]">0,67 (67%)</dd>
                        </div>
                        <div class="flex justify-between py-1">
                            <dt class="text-[#56686b]">Rentabilidad Activo (ROA):</dt>
                            <dd class="font-semibold text-[#00232c]">0,59 (59%)</dd>
                        </div>
                    </dl>

                    <div class="mt-4 p-3 rounded bg-blue-50 border border-blue-200 text-[11px] font-mono text-blue-900 leading-relaxed">
                        <strong>Respaldo de Contratación:</strong> Con un capital de trabajo de $5.058M COP, Tornus respalda procesos de hasta <strong>$12.645M COP</strong> con exigencia del 40% y hasta <strong>$25.290M COP</strong> al 20%.
                    </div>
                </div>
            </div>

            <!-- Resumen de Clasificación UNSPSC Registrada -->
            <div class="bg-white border border-[#e3dbc2] rounded-lg p-5">
                <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-[#00232c] flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    Códigos UNSPSC Acreditados en el RUP
                </h3>
                <div class="flex flex-wrap gap-2 text-xs font-mono">
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>80141600:</strong> Gestión de ventas y mercadeo</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>80141500:</strong> Investigación de mercado</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>82101500:</strong> Publicidad impresa</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>82101800:</strong> Publicidad exterior y BTL</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>82131600:</strong> Producción de video</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>82131500:</strong> Fotografía</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>82141500:</strong> Diseño gráfico</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>90151800:</strong> Organización de eventos</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>93141800:</strong> Servicios de turismo</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>81111500:</strong> Páginas web y apps</span>
                    <span class="px-2.5 py-1 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-[#00232c]"><strong>86101600:</strong> Capacitación profesional</span>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: 9 LÍNEAS DE SERVICIO (A - I)       -->
        <!-- ========================================== -->
        <div id="tabContent-lineas" class="profile-tab-pane pt-6 space-y-4 hidden">
            <div class="p-3 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#56686b] leading-relaxed">
                Estas 9 líneas constituyen la matriz de emparejamiento con la que Gemini contrasta el objeto y alcance de cada licitación (Ponderación: 35% objeto + 35% entregables).
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php $lineas = $profile['lineas_servicio'] ?? []; @endphp

                @foreach($lineas as $key => $linea)
                    <div class="bg-white border border-[#e3dbc2] rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="w-6 h-6 rounded-full bg-[#00232c] text-[#fbfb75] font-mono text-xs font-bold flex items-center justify-center">
                                    {{ $key }}
                                </span>
                                <div class="text-[10px] font-mono text-[#56686b] bg-[#fbf9f1] px-1.5 py-0.5 rounded border border-[#e3dbc2]">
                                    Línea Estratégica
                                </div>
                            </div>
                            <h4 class="font-mono text-xs font-bold text-[#00232c] mb-1.5 leading-snug">
                                {{ $linea['nombre'] ?? '' }}
                            </h4>
                            <p class="text-[11px] font-sans text-[#56686b] leading-relaxed mb-3">
                                {{ $linea['descripcion'] ?? '' }}
                            </p>

                            <!-- Entregables Típicos -->
                            <div class="mb-3">
                                <div class="text-[10px] font-mono font-semibold uppercase text-[#00232c] mb-1">Entregables clave:</div>
                                <ul class="text-[11px] font-mono text-[#56686b] space-y-0.5 pl-3 list-disc">
                                    @foreach(array_slice($linea['entregables'] ?? [], 0, 3) as $entregable)
                                        <li>{{ $entregable }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- UNSPSC -->
                        <div class="pt-2 border-t border-[#f0ebd8] flex flex-wrap gap-1">
                            @foreach($linea['codigos_unspsc'] ?? [] as $code)
                                <span class="text-[9px] font-mono bg-[#fbf9f1] border border-[#e3dbc2] text-[#56686b] px-1 py-0.5 rounded">
                                    {{ $code }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: EXPERIENCIA ACREDITABLE RUP        -->
        <!-- ========================================== -->
        <div id="tabContent-experiencia" class="profile-tab-pane pt-6 space-y-6 hidden">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-4">
                    <div class="text-[10px] font-mono text-[#56686b] uppercase">Experiencia Inscrita en RUP</div>
                    <div class="text-xl font-mono font-bold text-[#00232c] mt-1">11.998 SMMLV</div>
                    <div class="text-[11px] font-mono text-emerald-700 mt-0.5">20 contratos formalmente acreditados</div>
                </div>
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-4">
                    <div class="text-[10px] font-mono text-[#56686b] uppercase">Contratos de Gran Cuantía</div>
                    <div class="text-xl font-mono font-bold text-[#00232c] mt-1">6 Procesos > 700 SMMLV</div>
                    <div class="text-[11px] font-mono text-[#56686b] mt-0.5">Acreditan capacidad técnica mayor</div>
                </div>
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-4">
                    <div class="text-[10px] font-mono text-[#56686b] uppercase">Mayor Contrato Liquidado</div>
                    <div class="text-xl font-mono font-bold text-[#00232c] mt-1">3.629,80 SMMLV</div>
                    <div class="text-[11px] font-mono text-[#56686b] mt-0.5">$6.355M COP (Gob. La Guajira)</div>
                </div>
            </div>

            <!-- Tabla de Contratos RUP -->
            <div class="bg-white border border-[#e3dbc2] rounded-lg overflow-hidden">
                <div class="px-5 py-3.5 bg-[#fbf9f1] border-b border-[#e3dbc2] flex items-center justify-between">
                    <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-[#00232c]">
                        Contratos Principales Acreditados en Certificado RUP
                    </h3>
                    <span class="text-[11px] font-mono text-[#56686b]">Cámara de Comercio de Cali #132704</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-mono">
                        <thead class="bg-[#fbf9f1]/60 text-[#56686b] uppercase border-b border-[#e3dbc2]">
                            <tr>
                                <th class="px-4 py-2.5">Proceso / Contratante</th>
                                <th class="px-4 py-2.5">Cuantía (COP)</th>
                                <th class="px-4 py-2.5">SMMLV</th>
                                <th class="px-4 py-2.5">Objeto Contractual</th>
                                <th class="px-4 py-2.5">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0ebd8]">
                            @foreach($profile['experiencia_rup'] ?? [] as $exp)
                                <tr class="hover:bg-[#fbf9f1]/50">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-[#00232c]">{{ $exp['contrato'] }}</div>
                                        <div class="text-[10px] text-[#56686b]">{{ $exp['entidad'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-[#00232c] whitespace-nowrap">
                                        ${{ number_format($exp['cuantia_cop'] ?? 0, 0, ',', '.') }} COP
                                    </td>
                                    <td class="px-4 py-3 font-bold text-emerald-800 whitespace-nowrap">
                                        {{ number_format($exp['smmlv'] ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-[#56686b] max-w-xs font-sans text-xs leading-snug">
                                        {{ $exp['objeto'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $exp['estado'] ?? 'Liquidado' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Convenios de Gran Envergadura -->
            <div class="bg-white border border-[#e3dbc2] rounded-lg p-5">
                <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-[#00232c] mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                    Grandes Convenios en Ejecución / Proceso de Certificación
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($profile['convenios_en_ejecucion'] ?? [] as $conv)
                        <div class="p-3.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-mono text-xs font-bold text-[#00232c]">{{ $conv['entidad'] }}</span>
                                <span class="font-mono text-xs font-bold text-emerald-800">${{ number_format($conv['cuantia_cop'], 0, ',', '.') }} COP</span>
                            </div>
                            <p class="text-[11px] font-sans text-[#56686b] leading-relaxed">
                                {{ $conv['objeto'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: CASOS DEL PORTAFOLIO & CLIENTES     -->
        <!-- ========================================== -->
        <div id="tabContent-portafolio" class="profile-tab-pane pt-6 space-y-4 hidden">
            <div class="p-3 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#56686b] leading-relaxed">
                Casos y marcas de referencia que sustentan el 20% de la ponderación de afinidad para el modelo de IA.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($profile['portafolio_referencia'] ?? [] as $caso)
                    <div class="bg-white border border-[#e3dbc2] rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-1.5 text-[10px] font-mono text-[#56686b] uppercase mb-1">
                                <svg class="w-3 h-3 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span>{{ $caso['cliente'] ?? '' }}</span>
                            </div>
                            <h4 class="font-mono text-xs font-bold text-[#00232c] mb-2">
                                {{ $caso['caso'] ?? '' }}
                            </h4>
                            <p class="text-[11px] font-sans text-[#56686b] leading-relaxed">
                                {{ $caso['alcance'] ?? '' }}
                            </p>
                        </div>
                        <div class="mt-3 pt-2 border-t border-[#f0ebd8] flex justify-end">
                            <span class="text-[9px] font-mono uppercase bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded">
                                Caso de Éxito
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 5: EQUIPO PROFESIONAL SENIOR           -->
        <!-- ========================================== -->
        <div id="tabContent-equipo" class="profile-tab-pane pt-6 space-y-4 hidden">
            <div class="p-3 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#56686b] leading-relaxed">
                Directores y líderes senior que soportan los requerimientos de idoneidad técnica en pliegos de contratación pública nacional e internacional.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($profile['equipo_senior'] ?? [] as $miembro)
                    <div class="bg-white border border-[#e3dbc2] rounded-lg p-4 flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#00232c] text-[#fbfb75] font-mono text-xs font-bold flex items-center justify-center shrink-0">
                            {{ substr($miembro['nombre'] ?? 'T', 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-mono text-xs font-bold text-[#00232c]">{{ $miembro['nombre'] }}</h4>
                            </div>
                            <div class="text-[11px] font-mono text-blue-700 font-semibold mb-1">
                                {{ $miembro['cargo'] }}
                            </div>
                            <p class="text-[11px] font-sans text-[#56686b] leading-relaxed">
                                {{ $miembro['perfil'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 6: FILTROS DE RASTREO POR PAÍS (COLOMBIA VS PERÚ - MULTI-TENANT)     -->
        <!-- ========================================================================= -->
        <div id="tabContent-filtros" class="profile-tab-pane pt-6 space-y-6 hidden">
            
            @if(!Auth::user()->isAdmin())
            <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs font-mono text-blue-900">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span><strong>Modo Consulta (Solo Lectura):</strong> Como colaborador puedes revisar las palabras clave, exclusiones y entidades prioritarias del motor de rastreo. La edición y ajuste de estos parámetros está reservada al Administrador.</span>
                </div>
                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-bold uppercase border border-blue-200 shrink-0 self-start sm:self-auto">Colaborador</span>
            </div>
            @endif

            <div class="p-4 bg-white border border-[#e3dbc2] rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-[#00232c] flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        Filtros de Rastreo y Exclusión Automatizada por Mercado
                    </h3>
                    <p class="text-xs font-mono text-[#56686b] mt-1">
                        Cada país cuenta con sus propias reglas, palabras clave, exclusiones y entidades prioritarias. No se mezclan entre sí.
                    </p>
                </div>

                <!-- Selector de País para Edición de Filtros -->
                <div class="inline-flex rounded border border-[#e3dbc2] p-1 bg-[#fbf9f1] shrink-0 font-mono text-xs">
                    <button type="button" onclick="switchCountryFilters('CO')" id="countryBtn-CO" class="px-3.5 py-1.5 rounded transition-colors font-bold bg-[#00232c] text-[#fbfb75] cursor-pointer">
                        COLOMBIA (SECOP II)
                    </button>
                    <button type="button" onclick="switchCountryFilters('PE')" id="countryBtn-PE" class="px-3.5 py-1.5 rounded transition-colors text-[#56686b] hover:text-[#00232c] cursor-pointer">
                        PERÚ (SEACE)
                    </button>
                </div>
            </div>

            <!-- =================================== -->
            <!-- BLOQUE DE FILTROS: COLOMBIA (CO)   -->
            <!-- =================================== -->
            <div id="countryBlock-CO" class="space-y-6">
                <div class="flex items-center justify-between pb-2 border-b border-[#e3dbc2]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <h4 class="font-mono text-sm font-bold text-[#00232c] uppercase">Configuración Activa: Colombia (SECOP II)</h4>
                    </div>
                    <span class="text-xs font-mono text-[#56686b]">storage/app/radar_filters_co.json</span>
                </div>

                <!-- 1. Entidades Prioritarias Colombia -->
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-5 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[#f0ebd8]">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21h18M3 7v14M21 7v14M6 7V3h12v4M9 11h2M9 15h2M13 11h2M13 15h2"></path></svg>
                            <span class="font-mono text-xs font-bold text-[#00232c] uppercase">Entidades Prioritarias (Colombia)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#fbf9f1] text-[#00232c] border border-[#e3dbc2] font-semibold">
                                {{ count($filtersCO['priority_entities'] ?? []) }} entidades
                            </span>
                        </div>
                        @if(Auth::user()->isAdmin())
                        <form action="{{ route('radar.priority_entities.reset') }}" method="POST">
                            @csrf
                            <input type="hidden" name="market" value="CO">
                            <button type="submit" class="text-[10px] font-mono text-[#56686b] hover:text-[#00232c] underline cursor-pointer">
                                Restablecer predeterminadas CO
                            </button>
                        </form>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($filtersCO['priority_entities'] ?? [] as $ent)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono">
                                <span class="font-bold text-[#00232c] uppercase">{{ $ent }}</span>
                                @if(Auth::user()->isAdmin())
                                <form action="{{ route('radar.priority_entities.remove') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="market" value="CO">
                                    <input type="hidden" name="entity" value="{{ $ent }}">
                                    <button type="submit" class="text-[#9db3b4] hover:text-rose-700 text-xs cursor-pointer" title="Eliminar">✕</button>
                                </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(Auth::user()->isAdmin())
                    <form action="{{ route('radar.priority_entities.add') }}" method="POST" class="pt-2 flex flex-col sm:flex-row items-center gap-2 max-w-2xl">
                        @csrf
                        <input type="hidden" name="market" value="CO">
                        <input type="text" name="entity" required placeholder="Agregar entidad en Colombia (ej. FONTUR, IDT, IDECUT)"
                            class="w-full px-3 py-2 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold whitespace-nowrap cursor-pointer">
                            + AGREGAR EN CO
                        </button>
                    </form>
                    @endif
                </div>

                <!-- 2. Palabras Clave Colombia -->
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-5 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[#f0ebd8]">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <span class="font-mono text-xs font-bold text-[#00232c] uppercase">Palabras Clave de Búsqueda (Colombia)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#fbf9f1] text-[#00232c] border border-[#e3dbc2] font-semibold">
                                {{ count($filtersCO['keywords'] ?? []) }} activas
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5">
                        @foreach($filtersCO['keywords'] ?? [] as $kw)
                            <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-2.5 flex items-center justify-between gap-2 text-xs font-mono">
                                <span class="font-bold text-[#00232c] truncate">{{ $kw }}</span>
                                @if(Auth::user()->isAdmin())
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" onclick="openEditKeywordModal('{{ addslashes($kw) }}', 'CO')" class="px-1.5 py-0.5 rounded text-[10px] text-[#56686b] hover:text-[#00232c] cursor-pointer">
                                        Editar
                                    </button>
                                    <form action="{{ route('radar.keywords.remove') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="market" value="CO">
                                        <input type="hidden" name="keyword" value="{{ $kw }}">
                                        <button type="submit" class="px-1.5 py-0.5 rounded text-[10px] text-rose-700 hover:text-rose-900 cursor-pointer">✕</button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(Auth::user()->isAdmin())
                    <form action="{{ route('radar.keywords.add') }}" method="POST" class="pt-2 flex flex-col sm:flex-row items-center gap-2 max-w-2xl">
                        @csrf
                        <input type="hidden" name="market" value="CO">
                        <input type="text" name="keyword" required placeholder="Nueva palabra clave en Colombia (ej. aviturismo, famtrip)"
                            class="w-full px-3 py-2 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold whitespace-nowrap cursor-pointer">
                            + AGREGAR PALABRA CO
                        </button>
                    </form>
                    @endif
                </div>

                <!-- 3. Palabras a Excluir Colombia -->
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-5 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[#f0ebd8]">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                            <span class="font-mono text-xs font-bold text-[#00232c] uppercase">Términos de Exclusión Automática (Colombia)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-rose-50 text-rose-700 border border-rose-200 font-semibold">
                                {{ count($filtersCO['exclusions'] ?? []) }} términos
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                        @foreach($filtersCO['exclusions'] ?? [] as $ex)
                            <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-2 flex items-center justify-between gap-1 text-[11px] font-mono">
                                <span class="text-[#00232c] truncate">{{ $ex }}</span>
                                @if(Auth::user()->isAdmin())
                                <form action="{{ route('radar.exclusions.remove') }}" method="POST" class="inline shrink-0">
                                    @csrf
                                    <input type="hidden" name="market" value="CO">
                                    <input type="hidden" name="exclusion" value="{{ $ex }}">
                                    <button type="submit" class="text-rose-700 hover:text-rose-900 cursor-pointer">✕</button>
                                </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(Auth::user()->isAdmin())
                    <form action="{{ route('radar.exclusions.add') }}" method="POST" class="pt-2 flex flex-col sm:flex-row items-center gap-2 max-w-2xl">
                        @csrf
                        <input type="hidden" name="market" value="CO">
                        <input type="text" name="exclusion" required placeholder="Nuevo término a excluir en Colombia (ej. vigilancia, pae)"
                            class="w-full px-3 py-2 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-700 text-white hover:bg-rose-800 rounded text-xs font-mono font-semibold whitespace-nowrap cursor-pointer">
                            + AGREGAR EXCLUSIÓN CO
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- =================================== -->
            <!-- BLOQUE DE FILTROS: PERÚ (PE)       -->
            <!-- =================================== -->
            <div id="countryBlock-PE" class="space-y-6 hidden">
                <div class="flex items-center justify-between pb-2 border-b border-[#e3dbc2]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <h4 class="font-mono text-sm font-bold text-[#00232c] uppercase">Configuración Activa: Perú (SEACE / OECE)</h4>
                    </div>
                    <span class="text-xs font-mono text-[#56686b]">storage/app/radar_filters_pe.json</span>
                </div>

                <!-- 1. Entidades Prioritarias Perú -->
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-5 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[#f0ebd8]">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21h18M3 7v14M21 7v14M6 7V3h12v4M9 11h2M9 15h2M13 11h2M13 15h2"></path></svg>
                            <span class="font-mono text-xs font-bold text-[#00232c] uppercase">Entidades Prioritarias (Perú)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#fbf9f1] text-[#00232c] border border-[#e3dbc2] font-semibold">
                                {{ count($filtersPE['priority_entities'] ?? []) }} entidades
                            </span>
                        </div>
                        @if(Auth::user()->isAdmin())
                        <form action="{{ route('radar.priority_entities.reset') }}" method="POST">
                            @csrf
                            <input type="hidden" name="market" value="PE">
                            <button type="submit" class="text-[10px] font-mono text-[#56686b] hover:text-[#00232c] underline cursor-pointer">
                                Restablecer predeterminadas PE
                            </button>
                        </form>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($filtersPE['priority_entities'] ?? [] as $ent)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono">
                                <span class="font-bold text-[#00232c] uppercase">{{ $ent }}</span>
                                @if(Auth::user()->isAdmin())
                                <form action="{{ route('radar.priority_entities.remove') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="market" value="PE">
                                    <input type="hidden" name="entity" value="{{ $ent }}">
                                    <button type="submit" class="text-[#9db3b4] hover:text-rose-700 text-xs cursor-pointer" title="Eliminar">✕</button>
                                </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(Auth::user()->isAdmin())
                    <form action="{{ route('radar.priority_entities.add') }}" method="POST" class="pt-2 flex flex-col sm:flex-row items-center gap-2 max-w-2xl">
                        @csrf
                        <input type="hidden" name="market" value="PE">
                        <input type="text" name="entity" required placeholder="Agregar entidad en Perú (ej. PROMPERÚ, MINCETUR, DIRCETUR)"
                            class="w-full px-3 py-2 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold whitespace-nowrap cursor-pointer">
                            + AGREGAR EN PE
                        </button>
                    </form>
                    @endif
                </div>

                <!-- 2. Palabras Clave Perú -->
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-5 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[#f0ebd8]">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <span class="font-mono text-xs font-bold text-[#00232c] uppercase">Palabras Clave de Búsqueda (Perú)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#fbf9f1] text-[#00232c] border border-[#e3dbc2] font-semibold">
                                {{ count($filtersPE['keywords'] ?? []) }} activas
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5">
                        @foreach($filtersPE['keywords'] ?? [] as $kw)
                            <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-2.5 flex items-center justify-between gap-2 text-xs font-mono">
                                <span class="font-bold text-[#00232c] truncate">{{ $kw }}</span>
                                @if(Auth::user()->isAdmin())
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" onclick="openEditKeywordModal('{{ addslashes($kw) }}', 'PE')" class="px-1.5 py-0.5 rounded text-[10px] text-[#56686b] hover:text-[#00232c] cursor-pointer">
                                        Editar
                                    </button>
                                    <form action="{{ route('radar.keywords.remove') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="market" value="PE">
                                        <input type="hidden" name="keyword" value="{{ $kw }}">
                                        <button type="submit" class="px-1.5 py-0.5 rounded text-[10px] text-rose-700 hover:text-rose-900 cursor-pointer">✕</button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(Auth::user()->isAdmin())
                    <form action="{{ route('radar.keywords.add') }}" method="POST" class="pt-2 flex flex-col sm:flex-row items-center gap-2 max-w-2xl">
                        @csrf
                        <input type="hidden" name="market" value="PE">
                        <input type="text" name="keyword" required placeholder="Nueva palabra clave en Perú (ej. turismo receptivo, intiraymi)"
                            class="w-full px-3 py-2 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold whitespace-nowrap cursor-pointer">
                            + AGREGAR PALABRA PE
                        </button>
                    </form>
                    @endif
                </div>

                <!-- 3. Palabras a Excluir Perú -->
                <div class="bg-white border border-[#e3dbc2] rounded-lg p-5 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[#f0ebd8]">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                            <span class="font-mono text-xs font-bold text-[#00232c] uppercase">Términos de Exclusión Automática (Perú)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-rose-50 text-rose-700 border border-rose-200 font-semibold">
                                {{ count($filtersPE['exclusions'] ?? []) }} términos
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                        @foreach($filtersPE['exclusions'] ?? [] as $ex)
                            <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-2 flex items-center justify-between gap-1 text-[11px] font-mono">
                                <span class="text-[#00232c] truncate">{{ $ex }}</span>
                                @if(Auth::user()->isAdmin())
                                <form action="{{ route('radar.exclusions.remove') }}" method="POST" class="inline shrink-0">
                                    @csrf
                                    <input type="hidden" name="market" value="PE">
                                    <input type="hidden" name="exclusion" value="{{ $ex }}">
                                    <button type="submit" class="text-rose-700 hover:text-rose-900 cursor-pointer">✕</button>
                                </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(Auth::user()->isAdmin())
                    <form action="{{ route('radar.exclusions.add') }}" method="POST" class="pt-2 flex flex-col sm:flex-row items-center gap-2 max-w-2xl">
                        @csrf
                        <input type="hidden" name="market" value="PE">
                        <input type="text" name="exclusion" required placeholder="Nuevo término a excluir en Perú (ej. serenazgo, saneamiento)"
                            class="w-full px-3 py-2 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-700 text-white hover:bg-rose-800 rounded text-xs font-mono font-semibold whitespace-nowrap cursor-pointer">
                            + AGREGAR EXCLUSIÓN PE
                        </button>
                    </form>
                    @endif
                </div>

            </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 7: METODOLOGÍA & RÚBRICA DEL EVALUADOR -->
        <!-- ========================================== -->
        <div id="tabContent-evaluador" class="profile-tab-pane pt-6 space-y-6 hidden">
            
            <!-- Bloque 1: Framework Metodológico Institucional -->
            <div class="bg-white border border-[#e3dbc2] rounded-lg p-5 sm:p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#e3dbc2]">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-wider text-[#56686b] block">FRAMEWORK METODOLÓGICO INSTITUCIONAL</span>
                        <h3 class="font-['Instrument_Serif',serif] text-2xl text-[#00232c] font-normal tracking-tight">
                            Metodología Operativa de Detección, Evaluación y Postulación
                        </h3>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-[#0b4a57] text-[#fbfb75] text-[11px] font-mono font-semibold self-start sm:self-auto">
                        Ciclo de 4 Fases Metodológicas
                    </span>
                </div>

                <p class="text-xs font-mono text-[#56686b] leading-relaxed">
                    Tornus opera bajo una metodología analítica de 4 etapas para transformar el flujo masivo de datos contractuales abiertos de SECOP II (Colombia) y SEACE/OECE (Perú) en decisiones comerciales estructuradas de alta rentabilidad.
                </p>

                <!-- 4 Fases Metodológicas en Rejilla -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
                    
                    <!-- Fase 1 -->
                    <div class="bg-[#fcfaf2] border border-[#e3dbc2] rounded-lg p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] font-bold text-[#00232c] px-2 py-0.5 rounded bg-[#f7f2df] border border-[#e3dbc2]">
                                FASE 1
                            </span>
                            <span class="font-mono text-[10px] text-[#56686b]">Ingesta</span>
                        </div>
                        <h4 class="font-mono text-xs font-bold text-[#00232c] uppercase">Detección & Anti-Ruido</h4>
                        <p class="text-[11px] font-mono text-[#56686b] leading-normal">
                            Monitoreo automatizado con filtrado temático por palabras clave y exclusión algorítmica de falsos positivos ajenos al RUP (obras civiles, alimentación, insumos médicos).
                        </p>
                    </div>

                    <!-- Fase 2 -->
                    <div class="bg-[#fcfaf2] border border-[#e3dbc2] rounded-lg p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] font-bold text-[#00232c] px-2 py-0.5 rounded bg-[#f7f2df] border border-[#e3dbc2]">
                                FASE 2
                            </span>
                            <span class="font-mono text-[10px] text-[#56686b]">Triage IA</span>
                        </div>
                        <h4 class="font-mono text-xs font-bold text-[#00232c] uppercase">Evaluación Gemini 3.8</h4>
                        <p class="text-[11px] font-mono text-[#56686b] leading-normal">
                            Cálculo de afinidad (0-100) contra el portafolio institucional bajo la rúbrica ponderada (35/35/20/10), emitiendo dictamen estratégico de postulación o consorcio.
                        </p>
                    </div>

                    <!-- Fase 3 -->
                    <div class="bg-[#fcfaf2] border border-[#e3dbc2] rounded-lg p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] font-bold text-[#00232c] px-2 py-0.5 rounded bg-[#f7f2df] border border-[#e3dbc2]">
                                FASE 3
                            </span>
                            <span class="font-mono text-[10px] text-[#56686b]">Control</span>
                        </div>
                        <h4 class="font-mono text-xs font-bold text-[#00232c] uppercase">Semáforo de Plazos</h4>
                        <p class="text-[11px] font-mono text-[#56686b] leading-normal">
                            Monitoreo en tiempo real de tiempos de cierre. Clasificación operativa: Tiempos Críticos (≤ 7 días hacia 0h para cierre urgente) y Alertas Tempranas (7 días a 5 meses con tiempo de antelación).
                        </p>
                    </div>

                    <!-- Fase 4 -->
                    <div class="bg-[#fcfaf2] border border-[#e3dbc2] rounded-lg p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] font-bold text-[#00232c] px-2 py-0.5 rounded bg-[#f7f2df] border border-[#e3dbc2]">
                                FASE 4
                            </span>
                            <span class="font-mono text-[10px] text-[#56686b]">Comercial</span>
                        </div>
                        <h4 class="font-mono text-xs font-bold text-[#00232c] uppercase">Gestión & Alertas</h4>
                        <p class="text-[11px] font-mono text-[#56686b] leading-normal">
                            Seguimiento en embudo Kanban de 5 etapas (Identificada &rarr; En Preparación &rarr; Presentada &rarr; Adjudicada) y boletines por correo según preferencias de mercado.
                        </p>
                    </div>

                </div>
            </div>

            <!-- Rúbrica Oficial de Calificación -->
            <div class="bg-white border border-[#e3dbc2] rounded-lg p-5">
                <h3 class="font-mono text-xs font-bold uppercase tracking-wider text-[#00232c] mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#56686b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                    Rúbrica Oficial de Calificación de Afinidad (Pesos Mandatorios)
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-4">
                    <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-3 text-center">
                        <div class="text-xl font-mono font-bold text-[#00232c]">35%</div>
                        <div class="text-[10px] font-mono text-[#56686b] mt-1">Objeto y Alcance vs Líneas A-I</div>
                    </div>
                    <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-3 text-center">
                        <div class="text-xl font-mono font-bold text-[#00232c]">35%</div>
                        <div class="text-[10px] font-mono text-[#56686b] mt-1">Actividades y Entregables</div>
                    </div>
                    <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-3 text-center">
                        <div class="text-xl font-mono font-bold text-[#00232c]">20%</div>
                        <div class="text-[10px] font-mono text-[#56686b] mt-1">Similitud con Portafolio</div>
                    </div>
                    <div class="bg-[#fbf9f1] border border-[#e3dbc2] rounded p-3 text-center">
                        <div class="text-xl font-mono font-bold text-[#00232c]">10%</div>
                        <div class="text-[10px] font-mono text-[#56686b] mt-1">Sector y Entidades Foco</div>
                    </div>
                </div>

                <div class="space-y-1.5 text-xs font-mono text-[#56686b]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <strong class="text-[#00232c]">85 - 100: Afinidad Muy Alta:</strong> Prioridad comercial inmediata. Convocar reunión técnica de formulación.
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <strong class="text-[#00232c]">70 - 84: Afinidad Alta:</strong> Oportunidad viable. Evaluar plazos y capacidad operativa.
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <strong class="text-[#00232c]">50 - 69: Afinidad Media:</strong> Requiere consorcio / aliados o tiene componentes mixtos.
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                        <strong class="text-[#00232c]">0 - 49: Afinidad Baja / Descarte:</strong> Fuera del radar comercial activo.
                    </div>
                </div>
            </div>

            @if(!Auth::user()->isAdmin())
            <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs font-mono text-blue-900">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span><strong>Modo Consulta (Solo Lectura):</strong> La metodología de 4 fases y la configuración estratégica para la IA están disponibles para consulta. La modificación de directrices está reservada al Administrador.</span>
                </div>
                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-bold uppercase border border-blue-200 shrink-0 self-start sm:self-auto">Colaborador</span>
            </div>
            @endif

            <!-- Formulario de Configuración de Criterios Editables -->
            <form action="{{ route('perfil.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Fila 1: Datos Básicos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                            Razón Social
                        </label>
                        <input type="text" name="nombre_agencia" value="{{ old('nombre_agencia', $profile['nombre_agencia'] ?? 'TORNUS S.A.S.') }}" required
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans">
                    </div>

                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                            Marca Comercial
                        </label>
                        <input type="text" name="marca_comercial" value="{{ old('marca_comercial', $profile['marca_comercial'] ?? 'Tornus Agency') }}" required
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans">
                    </div>

                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                            Sitio Web Oficial
                        </label>
                        <input type="url" name="sitio_web" value="{{ old('sitio_web', $profile['sitio_web'] ?? 'https://tornus.agency') }}"
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans">
                    </div>
                </div>

                <!-- Fila 2: Redes Sociales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                            Instagram Corporativo
                        </label>
                        <input type="text" name="instagram" value="{{ old('instagram', $profile['redes_sociales']['instagram'] ?? 'https://instagram.com/tornusagency') }}"
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans">
                    </div>

                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                            LinkedIn Corporativo
                        </label>
                        <input type="text" name="linkedin" value="{{ old('linkedin', $profile['redes_sociales']['linkedin'] ?? 'https://linkedin.com/company/tornusagency') }}"
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans">
                    </div>
                </div>

                <!-- Fila 3: Propuesta de Valor y Posicionamiento Sintético -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#00232c] mb-1.5">
                            Propuesta de Valor Integral (Contexto Maestro)
                        </label>
                        <textarea name="propuesta_valor" rows="3" required
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans leading-relaxed">{{ old('propuesta_valor', $profile['propuesta_valor'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#00232c] mb-1.5">
                            Posicionamiento Sintético para la IA (Instrucción de Enfoque)
                        </label>
                        <textarea name="posicionamiento_sintetico" rows="2"
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans leading-relaxed">{{ old('posicionamiento_sintetico', $profile['posicionamiento_sintetico'] ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Fila 4: Servicios Principales -->
                <div>
                    <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#00232c] mb-1.5">
                        Servicios Principales (Uno por línea)
                    </label>
                    <textarea name="servicios_principales" rows="6" required
                        @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                        class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-mono text-xs leading-relaxed">{{ old('servicios_principales', isset($profile['servicios_principales']) ? implode("\n", $profile['servicios_principales']) : '') }}</textarea>
                </div>

                <!-- Fila 5: Entidades Objetivo & Exclusiones (2 Columnas) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#00232c] mb-1.5">
                            Entidades Objetivo / Foco Estratégico (Una por línea)
                        </label>
                        <textarea name="entidades_objetivo" rows="6"
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-mono text-xs leading-relaxed">{{ old('entidades_objetivo', isset($profile['entidades_objetivo']) ? implode("\n", $profile['entidades_objetivo']) : '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-rose-800 mb-1.5">
                            Criterios de Exclusión Total (Descarte Inmediato - Score 0)
                        </label>
                        <textarea name="criterios_exclusion" rows="6"
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-rose-600 font-mono text-xs leading-relaxed">{{ old('criterios_exclusion', isset($profile['criterios_exclusion']) ? implode("\n", $profile['criterios_exclusion']) : '') }}</textarea>
                    </div>
                </div>

                <!-- Fila 6: Presupuesto y Notas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#00232c] mb-1.5">
                            Rango de Presupuesto Ideal y Capacidad
                        </label>
                        <input type="text" name="rango_presupuesto_ideal" value="{{ old('rango_presupuesto_ideal', $profile['rango_presupuesto_ideal'] ?? '') }}"
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans">
                    </div>

                    <div>
                        <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#00232c] mb-1.5">
                            Notas Estratégicas Adicionales
                        </label>
                        <input type="text" name="notas_adicionales" value="{{ old('notas_adicionales', $profile['notas_adicionales'] ?? '') }}"
                            @disabled(!Auth::user()->isAdmin()) @readonly(!Auth::user()->isAdmin())
                            class="w-full px-3.5 py-2.5 {{ !Auth::user()->isAdmin() ? 'bg-slate-100/70 text-slate-700 cursor-not-allowed' : 'bg-[#fbf9f1] text-[#00232c] focus:bg-white' }} border border-[#e3dbc2] rounded text-sm focus:outline-none focus:border-[#00232c] font-sans">
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#e3dbc2]">
                    <a href="{{ route('radar') }}" class="px-5 py-2.5 border border-[#e3dbc2] bg-white rounded text-xs font-mono font-bold tracking-wider text-[#56686b] hover:bg-[#f7f2df] transition-colors">
                        {{ Auth::user()->isAdmin() ? 'CANCELAR' : 'VOLVER AL RADAR' }}
                    </a>
                    @if(Auth::user()->isAdmin())
                    <button type="submit" class="px-6 py-2.5 bg-[#00232c] hover:bg-[#0b4a57] text-[#fbfb75] rounded text-xs font-mono font-bold tracking-wider flex items-center gap-2 shadow-xs transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>GUARDAR PERFIL ESTRATÉGICO</span>
                    </button>
                    @else
                    <div class="px-4 py-2 bg-slate-100 border border-slate-200 rounded text-xs font-mono text-slate-500 font-semibold">
                        Modo Solo Lectura · Edición reservada al Administrador
                    </div>
                    @endif
                </div>
            </form>
        </div>

    </div>

</div>

@if(Auth::user()->isAdmin())
<!-- Modal para Editar Palabra Clave -->
<div id="modal-edit-keyword" class="fixed inset-0 z-50 flex items-center justify-center bg-[#00232c]/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#e3dbc2]">
            <h3 class="font-['Instrument_Serif',serif] text-xl text-[#00232c]">Editar Palabra Clave</h3>
            <button type="button" onclick="closeEditKeywordModal()" class="text-[#56686b] hover:text-[#00232c] text-sm font-mono cursor-pointer">✕</button>
        </div>
        
        <form action="{{ route('radar.keywords.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="market" id="edit-market" value="CO">
            <input type="hidden" name="old_keyword" id="edit-old-keyword">
            
            <div>
                <label class="block text-[11px] font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                    Término o Frase
                </label>
                <input type="text" name="keyword" id="edit-new-keyword" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c] font-sans">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#e3dbc2]">
                <button type="button" onclick="closeEditKeywordModal()" class="px-3.5 py-2 rounded border border-[#e3dbc2] bg-white text-xs font-mono text-[#56686b] hover:bg-[#f7f2df] cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded bg-[#00232c] text-[#fbfb75] text-xs font-mono font-semibold hover:bg-[#0b4a57] cursor-pointer">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function showProfileTab(tabName) {
    let target = tabName;
    let activePane = document.getElementById('tabContent-' + target);
    if (!activePane) {
        target = 'juridica';
        activePane = document.getElementById('tabContent-juridica');
    }
    sessionStorage.setItem('activeProfileTab', target);
    document.querySelectorAll('.profile-tab-pane').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.profile-tab-btn').forEach(btn => {
        btn.classList.remove('bg-[#00232c]', 'text-[#fbfb75]', 'border-[#00232c]', 'font-bold', 'shadow-2xs');
        btn.classList.add('bg-white', 'text-[#56686b]', 'border-[#e3dbc2]');
    });

    if (activePane) activePane.classList.remove('hidden');

    const activeBtn = document.getElementById('tabBtn-' + target);
    if (activeBtn) {
        activeBtn.classList.remove('bg-white', 'text-[#56686b]', 'border-[#e3dbc2]');
        activeBtn.classList.add('bg-[#00232c]', 'text-[#fbfb75]', 'border-[#00232c]', 'font-bold', 'shadow-2xs');
    }
}

function switchCountryFilters(countryCode) {
    sessionStorage.setItem('activeCountryFilter', countryCode);
    const isCO = countryCode === 'CO';
    const blockCO = document.getElementById('countryBlock-CO');
    const blockPE = document.getElementById('countryBlock-PE');
    const btnCO = document.getElementById('countryBtn-CO');
    const btnPE = document.getElementById('countryBtn-PE');

    if (isCO) {
        if (blockCO) blockCO.classList.remove('hidden');
        if (blockPE) blockPE.classList.add('hidden');
        if (btnCO) {
            btnCO.classList.add('bg-[#00232c]', 'text-[#fbfb75]', 'font-bold');
            btnCO.classList.remove('text-[#56686b]');
        }
        if (btnPE) {
            btnPE.classList.remove('bg-[#00232c]', 'text-[#fbfb75]', 'font-bold');
            btnPE.classList.add('text-[#56686b]');
        }
    } else {
        if (blockCO) blockCO.classList.add('hidden');
        if (blockPE) blockPE.classList.remove('hidden');
        if (btnPE) {
            btnPE.classList.add('bg-[#00232c]', 'text-[#fbfb75]', 'font-bold');
            btnPE.classList.remove('text-[#56686b]');
        }
        if (btnCO) {
            btnCO.classList.remove('bg-[#00232c]', 'text-[#fbfb75]', 'font-bold');
            btnCO.classList.add('text-[#56686b]');
        }
    }
}

function openEditKeywordModal(keyword, market = 'CO') {
    document.getElementById('edit-market').value = market;
    document.getElementById('edit-old-keyword').value = keyword;
    document.getElementById('edit-new-keyword').value = keyword;
    document.getElementById('modal-edit-keyword').classList.remove('hidden');
    document.getElementById('edit-new-keyword').focus();
}

function closeEditKeywordModal() {
    document.getElementById('modal-edit-keyword').classList.add('hidden');
}

// Abrir pestaña si viene por parámetro URL (?tab=filtros&country=PE) o por sessionStorage
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || sessionStorage.getItem('activeProfileTab');
    const country = urlParams.get('country') || sessionStorage.getItem('activeCountryFilter');

    if (tab) {
        showProfileTab(tab);
    }
    if (country) {
        switchCountryFilters(country.toUpperCase());
    }
});
</script>
@endsection
