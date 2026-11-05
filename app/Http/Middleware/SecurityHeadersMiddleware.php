<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Inyecta encabezados de seguridad HTTP estándar de la industria (OWASP)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevenir Clickjacking mediante iframes embebidos
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevenir ataques de confusión de tipos MIME
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Protección XSS para navegadores compatibles
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Política de referencia segura para evitar filtración de URLs en peticiones externas
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}

