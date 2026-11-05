<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Nuevas Licitaciones · Tornus</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f2df; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #00232c; -webkit-font-smoothing: antialiased;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f7f2df; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 680px; background-color: #ffffff; border: 1px solid #e3dbc2; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    
                    <!-- Encabezado Institucional -->
                    <tr>
                        <td style="background-color: #00232c; padding: 24px 32px; border-bottom: 2px solid #15424d;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <div style="font-size: 20px; font-weight: 800; letter-spacing: 2px; color: #ffffff; text-transform: uppercase;">
                                            TORNUS
                                        </div>
                                        <div style="font-size: 11px; font-family: monospace; color: #9db3b4; letter-spacing: 1px; margin-top: 4px;">
                                            RADAR DE LICITACIONES Y CONTRATACIÓN PÚBLICA
                                        </div>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; padding: 4px 10px; background-color: #0b4a57; border: 1px solid #15424d; border-radius: 4px; font-size: 11px; font-family: monospace; color: #fbfb75; font-weight: bold;">
                                            {{ $market === 'PE' ? 'MERCADO PERÚ · OECE' : ($market === 'CO' ? 'MERCADO COLOMBIA · SECOP II' : 'MERCADO REGIONAL') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Introducción -->
                    <tr>
                        <td style="padding: 28px 32px 16px 32px; background-color: #fffdf6; border-bottom: 1px solid #f2ece0;">
                            <div style="font-size: 13px; font-family: monospace; color: #56686b; text-transform: uppercase; letter-spacing: 0.5px;">
                                Notificación Automática de Oportunidades
                            </div>
                            <h1 style="margin: 6px 0 12px 0; font-size: 22px; font-weight: 700; color: #00232c; line-height: 1.3;">
                                {{ $totalCount }} nuevas oportunidades detectadas en el radar
                            </h1>
                            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #334e55;">
                                Hola, <strong>{{ $recipientName }}</strong>. El motor de ingesta de Tornus ha identificado las siguientes oportunidades que coinciden con las palabras clave y parámetros comerciales definidos para la agencia:
                            </p>
                        </td>
                    </tr>

                    <!-- Listado de Licitaciones -->
                    <tr>
                        <td style="padding: 20px 32px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                @foreach(array_slice($licitaciones, 0, 8) as $lic)
                                    @php
                                        $cuantia = is_object($lic) ? ($lic->formatted_cuantia ?? '$ 0') : ($lic['formatted_cuantia'] ?? '$ 0');
                                        $objeto = is_object($lic) ? ($lic->objeto ?? '') : ($lic['objeto'] ?? '');
                                        $entidad = is_object($lic) ? ($lic->entidad ?? 'Entidad Pública') : ($lic['entidad'] ?? 'Entidad Pública');
                                        $referencia = is_object($lic) ? ($lic->referencia ?? $lic->external_id ?? '') : ($lic['referencia'] ?? $lic['external_id'] ?? '');
                                        $url = is_object($lic) ? ($lic->url_proceso ?? '') : ($lic['url_proceso'] ?? '');
                                        if (empty($url) && !empty($referencia)) {
                                            if ($market === 'CO') {
                                                $url = 'https://community.secop.gov.co/Public/Tendering/ContractNoticeManagement/Index?numConstancia=' . urlencode($referencia);
                                            } elseif ($market === 'PE') {
                                                $url = 'https://prodapp2.seace.gob.pe/seacebus-uiwd-pub/buscadorPublico/buscadorPublico.xhtml';
                                            }
                                        }
                                        $cierre = is_object($lic) ? ($lic->fecha_cierre ?? '') : ($lic['fecha_cierre'] ?? '');
                                        $departamento = is_object($lic) ? ($lic->departamento ?? '') : ($lic['departamento'] ?? '');
                                        $score = is_object($lic) ? ($lic->score ?? null) : ($lic['score'] ?? null);
                                        $estrategia = is_object($lic) ? ($lic->estrategia_postulacion ?? '') : ($lic['estrategia_postulacion'] ?? '');
                                        $resumenIa = is_object($lic) ? ($lic->resumen_ia ?? '') : ($lic['resumen_ia'] ?? '');
                                        $puntoClave = is_object($lic) ? ($lic->punto_clave ?? '') : ($lic['punto_clave'] ?? '');
                                        
                                        $baseUrl = rtrim(config('app.url', 'https://radar.tornus.agency'), '/');
                                        $radarSearchUrl = "{$baseUrl}/radar?buscar=" . urlencode($referencia);
                                        $portalName = ($market === 'PE') ? 'SEACE' : (($market === 'CO') ? 'SECOP II' : 'SECOP / SEACE');
                                    @endphp
                                    <tr>
                                        <td style="padding: 16px; margin-bottom: 12px; background-color: #faf8f0; border: 1px solid #e3dbc2; border-left: 4px solid #00232c; border-radius: 6px;">
                                            
                                            <!-- Barra superior de la tarjeta -->
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 8px;">
                                                <tr>
                                                    <td>
                                                        <span style="font-family: monospace; font-size: 11px; font-weight: bold; color: #00232c; background-color: #ffffff; padding: 2px 6px; border: 1px solid #e3dbc2; border-radius: 3px;">
                                                            {{ $referencia ?: 'PROCESO' }}
                                                        </span>
                                                        @if($departamento)
                                                            <span style="font-family: monospace; font-size: 11px; color: #56686b; margin-left: 6px;">
                                                                {{ $departamento }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td align="right">
                                                        @if(!is_null($score) && $score > 0)
                                                            <span style="font-family: monospace; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px; {{ $score >= 70 ? 'background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;' : 'background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;' }}">
                                                                IA: {{ $score }}/100
                                                            </span>
                                                        @endif
                                                        <span style="font-family: monospace; font-size: 12px; font-weight: bold; color: #00232c; margin-left: 8px;">
                                                            {{ $cuantia }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Entidad -->
                                            <div style="font-size: 12px; font-weight: bold; color: #0b4a57; margin-bottom: 6px;">
                                                {{ $entidad }}
                                            </div>

                                            <!-- Objeto -->
                                            <div style="font-size: 13px; color: #22353a; line-height: 1.45; margin-bottom: 10px;">
                                                {{ mb_strimwidth($objeto, 0, 240, '...') }}
                                            </div>

                                            <!-- Dictamen Estratégico IA (si fue analizada) -->
                                            @if($estrategia || $resumenIa || $puntoClave)
                                                <div style="margin: 8px 0 12px 0; padding: 8px 12px; background-color: #ffffff; border-left: 3px solid #0b4a57; border: 1px solid #e3dbc2; border-left-width: 3px; border-radius: 4px; font-size: 11px; color: #15424d; line-height: 1.4;">
                                                    @if($estrategia)
                                                        <span style="color: #00232c; font-family: monospace; font-weight: bold; text-transform: uppercase;">
                                                            DICTAMEN IA: {{ $estrategia }}
                                                        </span>
                                                        &middot; 
                                                    @endif
                                                    <span>{{ $resumenIa ?: ($puntoClave ?: 'Oportunidad evaluada y validada como viable para el portafolio institucional.') }}</span>
                                                </div>
                                            @endif

                                            <!-- Pie de tarjeta: Fecha y Enlaces duales (Radar Tornus + SECOP/SEACE) -->
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="font-size: 11px; font-family: monospace; color: #56686b; vertical-align: middle;">
                                                        @if($cierre)
                                                            Cierre: <strong>{{ substr($cierre, 0, 16) }}</strong>
                                                        @endif
                                                    </td>
                                                    <td align="right" style="vertical-align: middle; white-space: nowrap;">
                                                        <!-- Botón 1: Ver en Radar Tornus -->
                                                        <a href="{{ $radarSearchUrl }}" target="_blank" style="display: inline-block; font-family: monospace; font-size: 11px; font-weight: bold; color: #fbfb75; text-decoration: none; padding: 5px 11px; background-color: #00232c; border-radius: 3px; border: 1px solid #00232c; margin-right: 6px;">
                                                            Ver en Radar &rarr;
                                                        </a>

                                                        <!-- Botón 2: Ver en Portal Oficial -->
                                                        @if(!empty($url))
                                                            <a href="{{ $url }}" target="_blank" style="display: inline-block; font-family: monospace; font-size: 11px; font-weight: bold; color: #00232c; text-decoration: none; padding: 5px 11px; background-color: #fbfb75; border-radius: 3px; border: 1px solid #d4cba8;">
                                                                Ver en {{ $portalName }} &rarr;
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>

                                        </td>
                                    </tr>
                                    <tr><td height="12"></td></tr>
                                @endforeach
                            </table>

                            @if(count($licitaciones) > 8)
                                <div style="text-align: center; padding: 12px; font-family: monospace; font-size: 12px; color: #56686b;">
                                    ... y {{ count($licitaciones) - 8 }} propuestas adicionales disponibles en la plataforma.
                                </div>
                            @endif
                        </td>
                    </tr>

                    <!-- Llamado a la Acción -->
                    <tr>
                        <td align="center" style="padding: 16px 32px 32px 32px; background-color: #fffdf6; border-top: 1px solid #f2ece0;">
                            <a href="{{ config('app.url', 'http://localhost:8000') }}/radar" 
                                style="display: inline-block; padding: 12px 28px; background-color: #00232c; color: #fbfb75; font-family: monospace; font-size: 13px; font-weight: bold; text-decoration: none; border-radius: 6px; letter-spacing: 0.5px;">
                                INGRESAR AL RADAR EN VIVO &rarr;
                            </a>
                            <div style="margin-top: 16px; font-size: 11px; font-family: monospace; color: #88989b;">
                                Puedes modificar o silenciar estas alertas en cualquier momento desde Configuración de Equipo.
                            </div>
                        </td>
                    </tr>

                    <!-- Pie Institucional -->
                    <tr>
                        <td style="background-color: #00232c; padding: 20px 32px; text-align: center; border-top: 1px solid #15424d;">
                            <div style="font-size: 11px; font-family: monospace; color: #9db3b4;">
                                Tornus Agency · Plataforma de Licitaciones y Contratación Pública
                            </div>
                            <div style="font-size: 10px; font-family: monospace; color: #698285; margin-top: 4px;">
                                Mensaje generado automáticamente por el sistema de alertas tempranas.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

