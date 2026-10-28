<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Radar') | Tornus Procurement Platform</title>
    <link rel="icon" type="image/webp" href="/assets/favicon-tornus.webp">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f2df] text-[#00232c] font-['Archivo',sans-serif] flex flex-col selection:bg-[#fbfb75] selection:text-[#00232c]">

    <!-- Barra de Navegación Principal -->
    <header class="bg-[#00232c] text-[#f7f2df] border-b border-[#15424d] sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo y Enlaces Principales -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('market.select') }}" class="flex items-center gap-3" title="Panel Principal Tornus">
                        <img src="/assets/logo-tornus-white-1.svg" alt="Tornus" class="h-6 w-auto" onerror="this.src='/assets/logo-tornus.png'">
                    </a>

                    <!-- Enlaces Principales -->
                    <nav class="hidden md:flex items-center gap-1 font-mono text-xs">
                        <a href="{{ route('radar') }}" 
                            class="px-3 py-2 rounded transition-colors {{ request()->routeIs('radar') ? 'bg-[#0b4a57] text-[#fbfb75] font-semibold' : 'text-[#9db3b4] hover:text-[#f7f2df] hover:bg-[#0b4a57]/40' }}">
                            RADAR EN VIVO
                        </a>
                        <a href="{{ route('cartera') }}" 
                            class="px-3 py-2 rounded transition-colors {{ request()->routeIs('cartera') ? 'bg-[#0b4a57] text-[#fbfb75] font-semibold' : 'text-[#9db3b4] hover:text-[#f7f2df] hover:bg-[#0b4a57]/40' }}">
                            MI CARTERA
                        </a>
                        <a href="{{ route('afinidad') }}" 
                            class="px-3 py-2 rounded transition-colors {{ request()->routeIs('afinidad') ? 'bg-[#0b4a57] text-[#fbfb75] font-semibold' : 'text-[#9db3b4] hover:text-[#f7f2df] hover:bg-[#0b4a57]/40' }}">
                            AFINIDAD IA
                        </a>
                    </nav>
                </div>

                <!-- Lado Derecho: Indicador de País Activo, Usuario, Configuración y Logout -->
                <div class="flex items-center gap-4">
                    
                    <!-- INDICADOR DE MERCADO / PAÍS ACTIVO (Sin switch directo) -->
                    <div class="flex items-center gap-2 font-mono text-xs">
                        <span class="px-2.5 py-1 rounded bg-[#0b4a57] border border-[#15424d] font-bold text-[#fbfb75] flex items-center gap-1.5 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full {{ App\Services\MarketService::isColombia() ? 'bg-amber-400' : 'bg-rose-400' }}"></span>
                            <span>{{ App\Services\MarketService::current() }} · {{ App\Services\MarketService::marketName() }}</span>
                        </span>

                        <a href="{{ route('market.select') }}" 
                            class="px-2.5 py-1 rounded border border-[#15424d] hover:border-[#fbfb75] text-[#9db3b4] hover:text-[#fbfb75] hover:bg-[#0b4a57]/60 transition-colors flex items-center gap-1"
                            title="Regresar a seleccionar país">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 3L4 7l4 4"></path>
                                <path d="M4 7h16"></path>
                            </svg>
                            <span class="hidden sm:inline">Cambiar País</span>
                        </a>
                    </div>

                    <!-- Usuario, Configuración y Logout -->
                    <div class="flex items-center gap-2 sm:gap-3 pl-2 border-l border-[#15424d]">
                        <span class="hidden lg:inline-block text-xs font-mono text-[#9db3b4]">
                            {{ Auth::user()->name ?? 'Usuario' }}
                        </span>

                        <a href="{{ route('configuracion') }}" 
                            class="p-1.5 text-[#9db3b4] hover:text-[#fbfb75] hover:bg-[#0b4a57] rounded transition-colors"
                            title="Configuración de Equipo y Usuarios">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0-1.82-.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                        </a>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" title="Cerrar Sesión" class="p-1.5 text-[#9db3b4] hover:text-[#fbfb75] hover:bg-[#0b4a57] rounded transition-colors cursor-pointer">
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
        </div>
    </header>

    <!-- Sub-Barra Informativa de Mercado Activo -->
    <div class="bg-[#f2ece0] border-b border-[#e3dbc2] py-2 px-4 sm:px-6 lg:px-8 text-xs font-mono text-[#56686b] flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full {{ App\Services\MarketService::isColombia() ? 'bg-amber-600' : 'bg-rose-600' }}"></span>
            <span class="font-bold text-[#00232c]">MERCADO ACTIVO: {{ strtoupper(App\Services\MarketService::marketName()) }}</span>
            <span class="text-[#9db3b4]">|</span>
            <span>Fuente: {{ App\Services\MarketService::sourceName() }}</span>
            <span class="text-[#9db3b4]">|</span>
            <span>Moneda: {{ App\Services\MarketService::currency() }} ({{ App\Services\MarketService::currencySymbol() }})</span>
        </div>
        <div class="flex items-center gap-3 text-[11px] text-[#56686b]">
            <span>Base aislada: <code class="bg-[#fffdf6] px-1 py-0.5 rounded border border-[#e3dbc2]">licitaciones_{{ strtolower(App\Services\MarketService::current()) }}.sqlite</code></span>
            <span class="text-[#e3dbc2]">·</span>
            <a href="{{ route('market.select') }}" class="underline hover:text-[#00232c] font-semibold">
                Seleccionar otro país
            </a>
        </div>
    </div>

    <!-- Contenido Principal -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Pie de Página Institucional -->
    <footer class="bg-[#00232c] text-[#9db3b4] border-t border-[#15424d] py-6 text-xs font-mono">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                Tornus Agency · copyright © 2026 Todos los derechos reservados 
            </div>
            <div class="flex items-center gap-2">
                <span>Desarrollado por</span>
                <a href="https://juansadigital.com/ia-lab/" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 font-sans hover:opacity-80 transition-opacity">
                    <img src="/assets/juansadigital-logo.png" alt="Juansa Digital" class="h-4 w-auto brightness-0 invert opacity-90">
                    <strong class="font-bold text-white tracking-tight">Juansa Digital</strong>
                    <span class="text-[#9db3b4]">by Dijumedia</span>
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
