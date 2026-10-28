<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Seguro | Tornus Procurement Platform</title>
    <link rel="icon" type="image/webp" href="/assets/favicon-tornus.webp">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#00232c] text-[#00232c] font-['Archivo',sans-serif] flex flex-col justify-between selection:bg-[#fbfb75] selection:text-[#00232c] relative">

    <!-- Fondo de Pantalla (Wallpaper Tornus) con Overlay para Legibilidad -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat" style="background-image: url('/assets/login-wallpaper.webp');">
        <!-- Overlay con gradiente que resalta el wallpaper a la izquierda y da contraste al formulario a la derecha -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/20 via-black/40 to-[#00232c]/85"></div>
    </div>

    <!-- Encabezado superior sobre el Wallpaper -->
    <header class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="/assets/logo-tornus-white-1.svg" alt="Tornus" class="h-7 w-auto" onerror="this.src='/assets/logo-tornus.png'">
            <span class="text-xs uppercase tracking-widest font-mono text-[#f7f2df]/80 font-medium hidden sm:inline-block">Procurement Intelligence</span>
        </div>
        <div class="text-xs font-mono text-[#f7f2df]/80 bg-black/30 backdrop-blur-xs px-3 py-1 rounded border border-white/15">
            v2.0 · Plataforma de Licitaciones
        </div>
    </header>

    <!-- Contenedor Principal: Disposición equilibrada que exhibe el wallpaper -->
    <main class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-auto py-8 flex flex-col lg:flex-row items-center justify-between gap-12">
        
        <!-- Bloque de Identidad Institucional (Visible en Desktop) -->
        <div class="hidden lg:block max-w-lg text-[#f7f2df] space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-[#00232c]/60 backdrop-blur-xs border border-white/15 text-xs font-mono text-[#fbfb75]">
                <span class="w-2 h-2 rounded-full bg-[#fbfb75]"></span>
                <span>RADAR DE COMPRAS PÚBLICAS</span>
            </div>
            <h2 class="font-['Instrument_Serif',serif] text-4xl xl:text-5xl font-normal leading-tight">
                Inteligencia de mercado para contratación estatal.
            </h2>
            <p class="text-xs font-mono text-[#f7f2df]/80 leading-relaxed">
                Monitoreo continuo en contrataciones públicas.
            </p>
        </div>

        <!-- Tarjeta de Login Aparte -->
        <div class="w-full max-w-md bg-[#fffdf6]/95 backdrop-blur-md border border-[#e3dbc2] rounded-xl shadow-2xl p-7 sm:p-9">
            
            <div class="text-center mb-6">
                <h1 class="font-['Instrument_Serif',serif] text-3xl sm:text-4xl text-[#00232c] mb-2 font-normal">Iniciar Sesión</h1>
                <p class="text-xs sm:text-sm text-[#56686b]">Ingresa tus credenciales corporativas para acceder al panel.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 p-3 rounded bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Correo Electrónico -->
                <div>
                    <label for="email" class="block text-xs font-mono font-semibold uppercase tracking-wider text-[#56686b] mb-1.5">
                        Correo Corporativo
                    </label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ old('email', 'admin@tornus.agency') }}" required autocomplete="email" autofocus
                            class="w-full px-3.5 py-2.5 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] placeholder-[#9db3b4] focus:outline-none focus:border-[#00232c] focus:ring-1 focus:ring-[#00232c] transition-colors">
                    </div>
                </div>

                <!-- Contraseña -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-mono font-semibold uppercase tracking-wider text-[#56686b]">
                            Contraseña
                        </label>
                    </div>
                    <div class="relative">
                        <input type="password" id="password" name="password" value="password" required autocomplete="current-password"
                            class="w-full px-3.5 py-2.5 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] placeholder-[#9db3b4] focus:outline-none focus:border-[#00232c] focus:ring-1 focus:ring-[#00232c] transition-colors">
                    </div>
                </div>

                <!-- Recordar sesión -->
                <div class="flex items-center justify-between text-xs text-[#56686b]">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-[#e3dbc2] text-[#00232c] focus:ring-0">
                        <span>Mantener sesión iniciada</span>
                    </label>
                </div>

                <!-- Botón de Acceso -->
                <button type="submit"
                    class="w-full py-2.5 px-4 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-sm font-semibold tracking-wide transition-colors flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                    <span>Acceder al Panel</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </form>
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
