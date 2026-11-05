<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Equipo | Tornus Procurement Platform</title>
    <link rel="icon" type="image/webp" href="/assets/favicon-tornus.webp">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f2df] text-[#00232c] font-['Archivo',sans-serif] flex flex-col justify-between selection:bg-[#fbfb75] selection:text-[#00232c]">

    <!-- Encabezado Superior -->
    <header class="bg-[#00232c] text-[#f7f2df] border-b border-[#15424d] sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo y Navegación de Retorno -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('market.select') }}" class="flex items-center gap-3">
                        <img src="/assets/logo-tornus-white-1.svg" alt="Tornus" class="h-6 w-auto" onerror="this.src='/assets/logo-tornus.png'">
                    </a>

                    <nav class="flex items-center gap-2 font-mono text-xs">
                        <a href="{{ route('market.select') }}" 
                            class="px-3 py-1.5 rounded transition-colors text-[#9db3b4] hover:text-[#f7f2df] hover:bg-[#0b4a57]/40 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            <span>SELECCIÓN DE PAÍS</span>
                        </a>

                        @if(App\Services\MarketService::hasSelected())
                            <span class="text-[#15424d]">|</span>
                            <a href="{{ route('radar') }}" 
                                class="px-3 py-1.5 rounded transition-colors text-[#9db3b4] hover:text-[#f7f2df] hover:bg-[#0b4a57]/40">
                                VOLVER AL RADAR ({{ App\Services\MarketService::marketName() }})
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- Usuario y Logout -->
                <div class="flex items-center gap-3 font-mono text-xs">
                    <span class="hidden sm:inline-block text-[#9db3b4]">
                        {{ Auth::user()->name }}
                    </span>
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
    </header>

    <!-- Contenido Principal -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        @if (session('status'))
            <div class="p-3.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 rounded bg-rose-50 border border-rose-200 text-rose-800 text-xs font-mono">
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Tarjetas de Métricas Superiores -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">TOTAL MIEMBROS</span>
                <div class="font-['Instrument_Serif',serif] text-4xl font-normal text-[#00232c] mt-1">{{ $totalMiembros }}</div>
                <p class="text-[11px] font-mono text-[#56686b] mt-1">Cuentas creadas en el sistema</p>
            </div>

            <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">USUARIOS ACTIVOS</span>
                <div class="font-['Instrument_Serif',serif] text-4xl font-normal text-emerald-800 mt-1">{{ $usuariosActivos }}</div>
                <p class="text-[11px] font-mono text-[#56686b] mt-1">Con acceso habilitado</p>
            </div>

            <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">ADMINISTRADORES</span>
                <div class="font-['Instrument_Serif',serif] text-4xl font-normal text-[#00232c] mt-1">{{ $administradores }}</div>
                <p class="text-[11px] font-mono text-[#56686b] mt-1">Control total de la plataforma</p>
            </div>

            <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-5 shadow-xs">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#56686b]">ALERTAS POR EMAIL</span>
                <div class="font-['Instrument_Serif',serif] text-4xl font-normal text-blue-900 mt-1">{{ $conAlertasEmail }}</div>
                <p class="text-[11px] font-mono text-[#56686b] mt-1">Suscritos a avisos automáticos</p>
            </div>

        </div>

        <!-- Tarjeta Principal: EQUIPO CON ACCESO AUTORIZADO -->
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-6 shadow-xs">
            
            <!-- Encabezado de la Tarjeta -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-[#f2ece0] mb-5">
                <div>
                    <h2 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal tracking-tight uppercase">
                        EQUIPO CON ACCESO AUTORIZADO
                    </h2>
                    <p class="text-xs font-mono text-[#56686b] mt-0.5">
                        Solo las personas listadas aquí pueden ingresar a las herramientas de Tornus.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    @if(Auth::user()->isAdmin())
                        <button type="button" onclick="openAddUserModal()" 
                            class="px-3.5 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>AGREGAR MIEMBRO</span>
                        </button>
                    @endif

                    <a href="{{ route('configuracion') }}" 
                        class="px-3.5 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.3"/>
                        </svg>
                        <span>ACTUALIZAR</span>
                    </a>
                </div>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[11px] font-mono text-[#56686b] uppercase border-b border-[#e3dbc2]">
                            <th class="py-3 px-3 font-semibold">USUARIO</th>
                            <th class="py-3 px-3 font-semibold">ROL</th>
                            <th class="py-3 px-3 font-semibold">ESTADO</th>
                            <th class="py-3 px-3 font-semibold">AVISOS EMAIL</th>
                            <th class="py-3 px-3 font-semibold">ÚLTIMO ACCESO</th>
                            <th class="py-3 px-3 font-semibold text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f2ece0] text-xs">
                        @foreach($users as $u)
                            <tr class="hover:bg-[#f7f2df]/50 transition-colors">
                                
                                <!-- Usuario (Avatar con iniciales + Nombre + Email) -->
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-mono font-bold text-xs {{ $u->isAdmin() ? 'bg-[#00232c] text-[#fbfb75]' : 'bg-[#e3dbc2] text-[#00232c]' }}">
                                            {{ $u->initials }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-[#00232c]">{{ $u->name }}</span>
                                                @if(Auth::id() === $u->id)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-slate-200 text-slate-700">Tú</span>
                                                @endif
                                            </div>
                                            <div class="font-mono text-[11px] text-[#56686b]">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Rol -->
                                <td class="py-3.5 px-3">
                                    @if($u->isAdmin())
                                        <span class="px-2.5 py-1 rounded text-[11px] font-mono bg-purple-50 text-purple-700 border border-purple-200 font-semibold">
                                            Administrador
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded text-[11px] font-mono bg-amber-50 text-amber-800 border border-amber-200 font-semibold">
                                            Colaborador
                                        </span>
                                    @endif
                                </td>

                                <!-- Estado -->
                                <td class="py-3.5 px-3 font-mono text-[11px]">
                                    @if($u->isActivo())
                                        <span class="inline-flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>Activo</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-rose-700 font-medium">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            <span>Inactivo</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Avisos Email -->
                                <td class="py-3.5 px-3 font-mono text-[11px]">
                                    <div class="flex items-center gap-2">
                                        @if($u->notificar_email)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                <span>Activo ({{ $u->notificar_mercado ?? 'TODOS' }})</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                <span>Silenciado</span>
                                            </span>
                                        @endif

                                        @if(Auth::user()->isAdmin() || Auth::id() === $u->id)
                                            <form action="{{ route('configuracion.usuarios.toggle_notification', $u->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-[10px] text-[#56686b] hover:text-[#00232c] underline cursor-pointer">
                                                    {{ $u->notificar_email ? 'Pausar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>

                                <!-- Último Acceso -->
                                <td class="py-3.5 px-3 font-mono text-[11px] text-[#56686b]">
                                    {{ $u->ultimo_acceso ? $u->ultimo_acceso->format('Y-m-d H:i:s') : 'Sin ingresos' }}
                                </td>

                                <!-- Acciones -->
                                <td class="py-3.5 px-3 text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        
                                        <!-- Botón Clave -->
                                        @if(Auth::user()->isAdmin() || Auth::id() === $u->id)
                                            <button type="button" onclick="openPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                                class="px-2.5 py-1 bg-[#00232c] hover:bg-[#0b4a57] text-[#fbfb75] rounded text-[11px] font-mono font-semibold transition-colors cursor-pointer">
                                                CLAVE
                                            </button>
                                        @endif

                                        <!-- Botón Desactivar / Activar (Solo Admin sobre otros) -->
                                        @if(Auth::user()->isAdmin() && Auth::id() !== $u->id)
                                            <form action="{{ route('configuracion.usuarios.toggle_status', $u->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                     class="px-2.5 py-1 border border-[#e3dbc2] bg-white hover:bg-[#f7f2df] text-[#00232c] rounded text-[11px] font-mono font-semibold transition-colors cursor-pointer">
                                                    {{ $u->activo ? 'DESACTIVAR' : 'ACTIVAR' }}
                                                </button>
                                            </form>

                                            <!-- Botón Eliminar -->
                                            <form action="{{ route('configuracion.usuarios.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirmas que deseas eliminar a {{ addslashes($u->name) }} del sistema?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar miembro"
                                                    class="p-1 text-[#56686b] hover:text-rose-700 rounded border border-[#e3dbc2] bg-white hover:bg-rose-50 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tarjeta de Configuración de Alertas por Correo -->
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-lg p-6 shadow-xs space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#f2ece0]">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded font-bold">
                            Servicio SMTP Activo
                        </span>
                        <span class="text-[10px] font-mono text-[#56686b]">
                            smtp.gmail.com:587 (TLS) · tornuswebmaster@gmail.com
                        </span>
                    </div>
                    <h2 class="font-['Instrument_Serif',serif] text-2xl sm:text-3xl text-[#00232c] font-normal tracking-tight uppercase mt-1">
                        AVISOS AUTOMÁTICOS POR CORREO
                    </h2>
                    <p class="text-xs font-mono text-[#56686b] mt-0.5">
                        Configura cómo y cuándo deseas recibir las alertas tempranas de nuevas oportunidades detectadas por el Radar.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <form action="{{ route('configuracion.alertas.test') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 bg-white border border-[#e3dbc2] hover:bg-[#f7f2df] text-[#00232c] rounded text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-2xs cursor-pointer"
                            title="Enviar un correo de muestra con propuestas a tu dirección actual">
                            <svg class="w-3.5 h-3.5 text-blue-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <span>ENVIAR CORREO DE PRUEBA</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Formulario de Preferencias Personales del Usuario -->
            <form action="{{ route('configuracion.alertas.update') }}" method="POST" class="space-y-5">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    
                    <!-- 1. Estado de Alertas -->
                    <div class="bg-white border border-[#e3dbc2] rounded-lg p-4 space-y-3">
                        <span class="text-[10px] font-mono font-bold uppercase text-[#56686b] block">1. ESTADO DE SUSCRIPCIÓN</span>
                        <label class="flex items-start gap-3 cursor-pointer pt-1">
                            <input type="checkbox" name="notificar_email" value="1" {{ Auth::user()->notificar_email ? 'checked' : '' }}
                                class="mt-0.5 rounded border-[#e3dbc2] text-[#00232c] focus:ring-[#00232c]">
                            <div>
                                <span class="text-xs font-bold font-mono text-[#00232c] block">Recibir Avisos por Correo</span>
                                <span class="text-[11px] font-mono text-[#56686b] block mt-0.5">
                                    Enviar alertas automáticas a {{ Auth::user()->email }} cuando el radar ingeste nuevas licitaciones.
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- 2. Criterio de Selección -->
                    <div class="bg-white border border-[#e3dbc2] rounded-lg p-4 space-y-3">
                        <span class="text-[10px] font-mono font-bold uppercase text-[#56686b] block">2. CRITERIO DE DISPARO</span>
                        <select name="notificar_criterio" class="w-full px-3 py-2 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                            <option value="afinidad_alta" {{ Auth::user()->notificar_criterio === 'afinidad_alta' ? 'selected' : '' }}>
                                Solo Afinidad Alta con IA (Score >= 70 o Prioritarias) [Recomendado]
                            </option>
                            <option value="prioritarias" {{ Auth::user()->notificar_criterio === 'prioritarias' ? 'selected' : '' }}>
                                Solo Entidades Prioritarias (FONTUR, MINCIT, IDT, PROMPERÚ...)
                            </option>
                            <option value="todas" {{ Auth::user()->notificar_criterio === 'todas' ? 'selected' : '' }}>
                                Todas las propuestas que superen los filtros de Tornus
                            </option>
                        </select>
                        <span class="text-[11px] font-mono text-[#56686b] block">
                            Filtra el nivel de oportunidad antes de generar el aviso por correo.
                        </span>
                    </div>

                    <!-- 3. Mercado de Interés -->
                    <div class="bg-white border border-[#e3dbc2] rounded-lg p-4 space-y-3">
                        <span class="text-[10px] font-mono font-bold uppercase text-[#56686b] block">3. MERCADO DE INTERÉS</span>
                        <select name="notificar_mercado" class="w-full px-3 py-2 bg-[#fbf9f1] border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                            <option value="TODOS" {{ (Auth::user()->notificar_mercado ?? 'TODOS') === 'TODOS' ? 'selected' : '' }}>
                                Ambos Mercados (Colombia · SECOP II y Perú · OECE)
                            </option>
                            <option value="CO" {{ Auth::user()->notificar_mercado === 'CO' ? 'selected' : '' }}>
                                Solo Colombia (SECOP II)
                            </option>
                            <option value="PE" {{ Auth::user()->notificar_mercado === 'PE' ? 'selected' : '' }}>
                                Solo Perú (SEACE / OECE)
                            </option>
                        </select>
                        <span class="text-[11px] font-mono text-[#56686b] block">
                            Recibe únicamente convocatorias del país seleccionado.
                        </span>
                    </div>

                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit" class="px-4 py-2 bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded text-xs font-mono font-semibold transition-colors shadow-xs cursor-pointer">
                        GUARDAR PREFERENCIAS DE AVISO
                    </button>
                </div>

            </form>

        </div>

        </div>

    </main>

    <!-- MODAL 1: AGREGAR MIEMBRO -->
    <div id="modal-add-user" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between pb-3 border-b border-[#f2ece0] mb-4">
                <h3 class="font-['Instrument_Serif',serif] text-2xl text-[#00232c]">Agregar Miembro al Equipo</h3>
                <button type="button" onclick="closeAddUserModal()" class="text-[#56686b] hover:text-[#00232c]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form action="{{ route('configuracion.usuarios.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-mono font-semibold uppercase text-[#56686b] mb-1">Nombre Completo</label>
                    <input type="text" name="name" required placeholder="Ej: Alejandro Rodríguez"
                        class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c]">
                </div>

                <div>
                    <label class="block text-xs font-mono font-semibold uppercase text-[#56686b] mb-1">Correo Corporativo</label>
                    <input type="email" name="email" required placeholder="ejemplo@tornus.agency"
                        class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c]">
                </div>

                <div>
                    <label class="block text-xs font-mono font-semibold uppercase text-[#56686b] mb-1">Rol de Acceso</label>
                    <select name="rol" class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-xs font-mono text-[#00232c] focus:outline-none focus:border-[#00232c]">
                        <option value="colaborador" selected>Colaborador (Acceso a Radar y Cartera)</option>
                        <option value="administrador">Administrador (Control total del sistema)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-mono font-semibold uppercase text-[#56686b] mb-1">Contraseña Inicial Asignada</label>
                    <input type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres"
                        class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c]">
                    <span class="text-[10px] font-mono text-[#9db3b4] mt-0.5 block">El usuario podrá cambiar esta clave posteriormente.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f2ece0]">
                    <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 text-xs font-mono border border-[#e3dbc2] bg-white rounded text-[#56686b] hover:bg-[#f7f2df]">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-mono font-semibold bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded shadow-xs">
                        Guardar Miembro
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: ASIGNAR / CAMBIAR CLAVE -->
    <div id="modal-password" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-[#fffdf6] border border-[#e3dbc2] rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between pb-3 border-b border-[#f2ece0] mb-4">
                <div>
                    <h3 class="font-['Instrument_Serif',serif] text-2xl text-[#00232c]">Asignar Nueva Clave</h3>
                    <p class="text-xs font-mono text-[#56686b]" id="password-modal-user-name">Usuario</p>
                </div>
                <button type="button" onclick="closePasswordModal()" class="text-[#56686b] hover:text-[#00232c]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="form-update-password" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-mono font-semibold uppercase text-[#56686b] mb-1">Nueva Contraseña</label>
                    <input type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres"
                        class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c]">
                </div>

                <div>
                    <label class="block text-xs font-mono font-semibold uppercase text-[#56686b] mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" required minlength="6" placeholder="Repite la contraseña"
                        class="w-full px-3 py-2 bg-white border border-[#e3dbc2] rounded text-sm text-[#00232c] focus:outline-none focus:border-[#00232c]">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f2ece0]">
                    <button type="button" onclick="closePasswordModal()" class="px-4 py-2 text-xs font-mono border border-[#e3dbc2] bg-white rounded text-[#56686b] hover:bg-[#f7f2df]">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-mono font-semibold bg-[#00232c] text-[#fbfb75] hover:bg-[#0b4a57] rounded shadow-xs">
                        Actualizar Clave
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pie inferior -->
    <footer class="bg-[#00232c] text-[#9db3b4] border-t border-[#15424d] py-6 text-xs font-mono mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                Tornus Agency · Todos los derechos reservados copyright © 2026
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

    <script>
        function openAddUserModal() {
            document.getElementById('modal-add-user').classList.remove('hidden');
        }
        function closeAddUserModal() {
            document.getElementById('modal-add-user').classList.add('hidden');
        }

        function openPasswordModal(userId, userName) {
            const form = document.getElementById('form-update-password');
            form.action = `/configuracion/usuarios/${userId}/password`;
            document.getElementById('password-modal-user-name').textContent = userName;
            document.getElementById('modal-password').classList.remove('hidden');
        }
        function closePasswordModal() {
            document.getElementById('modal-password').classList.add('hidden');
        }
    </script>

</body>
</html>

