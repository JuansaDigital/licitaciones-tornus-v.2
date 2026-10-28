<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Mercado | Tornus Procurement Platform</title>
    <link rel="icon" type="image/webp" href="/assets/favicon-tornus.webp">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#00232c] text-[#00232c] font-['Archivo',sans-serif] flex flex-col justify-between selection:bg-[#fbfb75] selection:text-[#00232c] relative">

    <!-- Fondo de Pantalla (Wallpaper Exclusivo de Selección de Mercado) -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat" style="background-image: url('/assets/select-wallpaper.webp');">
        <!-- Velo oscuro institucional para contraste y sofisticación visual -->
        <div class="absolute inset-0 bg-[#00232c]/80 backdrop-blur-xs"></div>
    </div>

    <!-- Barra de Navegación Superior (Sin enlaces operativos, solo logo, usuario, configuración y salida) -->
    <header class="relative z-10 w-full bg-[#00232c]/90 border-b border-[#15424d] backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo Oficial Único en el Extremo Izquierdo -->
                <div class="flex items-center gap-4">
                    <img src="/assets/logo-tornus-white-1.svg" alt="Tornus" class="h-6 w-auto" onerror="this.src='/assets/logo-tornus.png'">
                    <span class="text-xs uppercase tracking-widest font-mono text-[#9db3b4] font-medium hidden sm:inline-block border-l border-[#15424d] pl-4">
                        Selección de Territorio
                    </span>
                </div>

                <!-- Extremo Derecho: Nombre de Usuario, Configuración y Botón de Salida -->
                <div class="flex items-center gap-3 sm:gap-4 font-mono text-xs">
                    
                    <!-- Nombre de Usuario en Sesión -->
                    <div class="flex items-center gap-2 text-[#f7f2df] bg-[#0b4a57]/50 px-3 py-1.5 rounded border border-[#15424d]">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="font-medium tracking-tight">{{ Auth::user()->name ?? 'Usuario' }}</span>
                        @if(Auth::user()->isAdmin())
                            <span class="text-[10px] bg-[#fbfb75] text-[#00232c] px-1.5 py-0.2 rounded font-bold uppercase">Admin</span>
                        @endif
                    </div>

                    <!-- Botón de Configuración -->
                    <a href="{{ route('configuracion') }}" 
                        class="px-3 py-1.5 rounded transition-all flex items-center gap-1.5 text-[#f7f2df] bg-[#0b4a57] hover:bg-[#15424d] border border-[#15424d] shadow-xs cursor-pointer"
                        title="Configuración de Usuarios y Sistema">
                        <svg class="w-4 h-4 text-[#fbfb75]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span class="hidden sm:inline">Configuración</span>
                    </a>

                    <!-- Botón de Salida (Cerrar Sesión) -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Cerrar Sesión" 
                            class="p-1.5 text-[#9db3b4] hover:text-[#fbfb75] hover:bg-[#0b4a57] rounded transition-colors border border-transparent hover:border-[#15424d] cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </header>

    <!-- Contenedor Principal: Selección de País -->
    <main class="relative z-10 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 my-auto py-8 space-y-8">

        <!-- Encabezado de la pantalla -->
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-[#00232c]/70 backdrop-blur-xs border border-[#15424d] text-xs font-mono text-[#fbfb75]">
                <span class="w-2 h-2 rounded-full bg-[#fbfb75]"></span>
                <span>AISLAMIENTO DE MERCADOS MULTI-TENANT</span>
            </div>
            <h1 class="font-['Instrument_Serif',serif] text-3xl sm:text-5xl text-[#f7f2df] font-normal tracking-tight">
                Selecciona el Mercado Operativo
            </h1>
            <p class="text-xs sm:text-sm font-sans text-[#f7f2df]/80 leading-relaxed max-w-xl mx-auto">
                Cada país opera con una base de datos física independiente, su respectiva moneda local y su normativa de contratación pública para garantizar que nunca ocurra contaminación cruzada.
            </p>
        </div>

        <!-- Rejilla de Selección de Países -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">

            <!-- TARJETA COLOMBIA -->
            <div class="bg-[#fffdf6]/95 backdrop-blur-md border-2 {{ $currentMarket === 'CO' ? 'border-[#fbfb75] shadow-2xl' : 'border-[#e3dbc2] hover:border-[#00232c]' }} rounded-xl p-6 sm:p-8 flex flex-col justify-between transition-all group">
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
                    <div class="space-y-2.5 pt-2 border-t border-[#f2ece0] text-xs font-mono text-[#56686b]">
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
                        class="w-full py-3 px-4 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded-lg text-xs font-mono font-semibold tracking-wider transition-all flex items-center justify-center gap-2 shadow-xs group-hover:shadow-sm cursor-pointer">
                        <span>OPERAR EN COLOMBIA</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- TARJETA PERÚ -->
            <div class="bg-[#fffdf6]/95 backdrop-blur-md border-2 {{ $currentMarket === 'PE' ? 'border-[#fbfb75] shadow-2xl' : 'border-[#e3dbc2] hover:border-[#00232c]' }} rounded-xl p-6 sm:p-8 flex flex-col justify-between transition-all group">
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
                    <div class="space-y-2.5 pt-2 border-t border-[#f2ece0] text-xs font-mono text-[#56686b]">
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
                        class="w-full py-3 px-4 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded-lg text-xs font-mono font-semibold tracking-wider transition-all flex items-center justify-center gap-2 shadow-xs group-hover:shadow-sm cursor-pointer">
                        <span>OPERAR EN PERÚ</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>

        </div>

        <!-- Nota inferior informativa -->
        <div class="text-center pt-2">
            <p class="text-xs font-mono text-[#f7f2df]/60">
                Al seleccionar un país se habilitarán los módulos de Radar en Vivo, Mi Cartera y Afinidad IA para ese mercado.
            </p>
        </div>

    </main>

    <!-- Pie inferior sobre el Wallpaper -->
    <footer class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs font-mono text-[#f7f2df]/80 border-t border-white/10">
        <div>
            Tornus Agency · Todos los derechos reservados copyright © 2026
        </div>
        <div class="flex items-center gap-2">
            <span>Desarrollado por</span>
            <a href="https://juansadigital.com/ia-lab/" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 font-sans hover:opacity-80 transition-opacity">
                <img src="/assets/juansadigital-logo.png" alt="Juansa Digital" class="h-4 w-auto brightness-0 invert opacity-90">
                <span class="font-bold text-white tracking-tight">Juansa Digital</span>
                <span class="text-[#f7f2df]/70 font-normal">by Dijumedia</span>
            </a>
        </div>
    </footer>

</body>
</html>
