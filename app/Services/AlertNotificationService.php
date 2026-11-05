<?php

namespace App\Services;

use App\Mail\NuevasLicitacionesMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertNotificationService
{
    /**
     * Despacha alertas por correo a los miembros suscritos del equipo
     */
    public static function sendNewTendersAlert(array $licitaciones, string $market = 'CO'): int
    {
        if (empty($licitaciones)) {
            return 0;
        }

        $market = strtoupper($market);
        
        // Usuarios activos con alertas por correo habilitadas
        $users = User::where('activo', true)
            ->where('notificar_email', true)
            ->get();

        if ($users->isEmpty()) {
            return 0;
        }

        $sentCount = 0;

        foreach ($users as $user) {
            // 1. Filtrar por preferencia de mercado
            $userMarket = strtoupper($user->notificar_mercado ?? 'TODOS');
            if ($userMarket !== 'TODOS' && $userMarket !== $market) {
                continue;
            }

            // 2. Filtrar por criterio de afinidad o entidad si aplica
            $filteredTenders = self::filterTendersForUser($licitaciones, $user, $market);

            if (empty($filteredTenders)) {
                continue;
            }

            try {
                Mail::to($user->email)->send(
                    new NuevasLicitacionesMail(
                        licitaciones: $filteredTenders,
                        market: $market,
                        totalCount: count($filteredTenders),
                        recipientName: $user->name
                    )
                );
                $sentCount++;
                Log::info("Alerta de licitaciones enviada a {$user->email} ({$market}, " . count($filteredTenders) . " items)");
            } catch (\Throwable $e) {
                Log::error("Error enviando alerta por correo a {$user->email}: " . $e->getMessage());
            }
        }

        return $sentCount;
    }

    /**
     * Enviar un correo de prueba al usuario actual
     */
    public static function sendTestEmail(User $user): bool
    {
        $mockTenders = [
            [
                'referencia' => 'FONTUR-LP-012-2026',
                'entidad' => 'FONDO NACIONAL DEL TURISMO (FONTUR)',
                'objeto' => 'Diseño, ejecución y evaluación de la campaña de promoción y posicionamiento turístico internacional de Colombia como destino biocultural de alto valor.',
                'formatted_cuantia' => '$ 2.850.000.000 COP',
                'fecha_cierre' => now()->addDays(18)->format('Y-m-d H:i:s'),
                'departamento' => 'Bogotá D.C.',
                'url_proceso' => 'https://community.secop.gov.co',
                'score' => 92,
            ],
            [
                'referencia' => 'OECE-CP-045-2026',
                'entidad' => 'PROMPERÚ - COMISIÓN DE PROMOCIÓN DEL PERÚ',
                'objeto' => 'Servicio de agencia creativa 360 y producción multimedia para el desarrollo de contenidos promocionales en mercados emisores prioritarios.',
                'formatted_cuantia' => 'S/ 1.450.000 PEN',
                'fecha_cierre' => now()->addDays(24)->format('Y-m-d H:i:s'),
                'departamento' => 'Lima',
                'url_proceso' => 'https://prodapp2.seace.gob.pe',
                'score' => 88,
            ],
        ];

        try {
            Mail::to($user->email)->send(
                new NuevasLicitacionesMail(
                    licitaciones: $mockTenders,
                    market: 'TODOS',
                    totalCount: count($mockTenders),
                    recipientName: $user->name
                )
            );
            return true;
        } catch (\Throwable $e) {
            Log::error("Error en sendTestEmail para {$user->email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene propuestas recién convocadas con ventana amplia de preparación y conformes a los filtros de Tornus.
     * - Excluye ofertas vencidas o por vencer en menos de $minDaysToClose días (por defecto mínimo 3 días / 72 horas).
     * - Filtra únicamente oportunidades que salen por primera vez (publicación o ingestión reciente).
     * - Respeta al 100% las exclusiones negativas y pertinencia de palabras clave / entidades prioritarias.
     */
    public static function getEligibleNewTendersForAlerts(string $market = 'CO', int $hours = 48, int $minDaysToClose = 3, int $limit = 20): array
    {
        $connection = strtolower($market) === 'pe' ? 'peru' : 'colombia';
        $minCloseDate = now()->addDays($minDaysToClose)->format('Y-m-d H:i:s');
        $minCloseDay = now()->addDays($minDaysToClose)->toDateString();
        $sinceDateTime = now()->subHours($hours)->format('Y-m-d H:i:s');
        $sinceDate = now()->subHours($hours)->toDateString();

        $filters = RadarFilterService::getData($market);
        $keywords = $filters['keywords'] ?? [];
        $exclusions = $filters['exclusions'] ?? [];
        $priorityEntities = $filters['priority_entities'] ?? [];

        $query = \App\Models\Licitacion::on($connection);

        // 1. REGLA ESTRICTA DE VIGENCIA Y VENTANA AMPLIA (Mínimo $minDaysToClose días restantes)
        // Se descartan propuestas vencidas o con plazo crítico inminente (< 48h - 72h)
        $query->whereNotNull('fecha_cierre')
              ->where('fecha_cierre', '!=', '')
              ->where(function ($sub) use ($minCloseDate, $minCloseDay) {
                  $sub->whereRaw("length(fecha_cierre) > 10 AND fecha_cierre >= ?", [$minCloseDate])
                      ->orWhereRaw("length(fecha_cierre) <= 10 AND substr(fecha_cierre, 1, 10) >= ?", [$minCloseDay]);
              })
              ->whereNotIn('estado', ['Cancelado', 'Cerrado', 'Desierto', 'Adjudicado']);

        // 2. REGLA DE NOVEDAD: Propuestas recién convocadas / publicadas por primera vez
        $query->where(function ($q) use ($sinceDateTime, $sinceDate) {
            $q->where('created_at', '>=', $sinceDateTime)
              ->orWhereRaw("substr(fecha_publicacion, 1, 10) >= ?", [$sinceDate]);
        });

        // 3. REGLA DE EXCLUSIONES ACTIVAS DE TORNUS (Anti falsos positivos)
        foreach ($exclusions as $ex) {
            $exTrim = trim($ex);
            if (!empty($exTrim)) {
                $query->where('objeto', 'not like', "%{$exTrim}%")
                      ->where(function ($sub) use ($exTrim) {
                          $sub->whereNull('referencia')
                              ->orWhere('referencia', 'not like', "%{$exTrim}%");
                      });
            }
        }

        // 4. REGLA DE PERTINENCIA: Al menos una palabra clave afín o entidad prioritaria
        $query->where(function ($q) use ($keywords, $priorityEntities) {
            $hasCondition = false;
            foreach (array_slice($keywords, 0, 20) as $kw) {
                $kwTrim = trim($kw);
                if (!empty($kwTrim)) {
                    if (!$hasCondition) {
                        $q->where('objeto', 'like', "%{$kwTrim}%");
                        $hasCondition = true;
                    } else {
                        $q->orWhere('objeto', 'like', "%{$kwTrim}%");
                    }
                }
            }

            foreach ($priorityEntities as $pe) {
                $peTrim = trim($pe);
                if (!empty($peTrim)) {
                    if (!$hasCondition) {
                        $q->where('entidad', 'like', "%{$peTrim}%");
                        $hasCondition = true;
                    } else {
                        $q->orWhere('entidad', 'like', "%{$peTrim}%");
                    }
                }
            }
        });

        // 5. Ordenar dando prioridad a recién publicadas y mayor cuantía/relevancia
        return $query->orderBy('fecha_publicacion', 'desc')
                     ->orderBy('cuantia', 'desc')
                     ->limit($limit)
                     ->get()
                     ->all();
    }

    /**
     * Aplica los filtros de preferencia del usuario sobre la lista de licitaciones
     */
    protected static function filterTendersForUser(array $licitaciones, User $user, string $market): array
    {
        $criterio = $user->notificar_criterio ?? 'todas';

        if ($criterio === 'todas') {
            return $licitaciones;
        }

        $filters = RadarFilterService::getFilters($market);
        $priorityEntities = $filters['priority_entities'] ?? [];

        return array_values(array_filter($licitaciones, function ($lic) use ($criterio, $priorityEntities) {
            $score = is_object($lic) ? ($lic->score ?? null) : ($lic['score'] ?? null);
            $entidad = is_object($lic) ? ($lic->entidad ?? '') : ($lic['entidad'] ?? '');

            if ($criterio === 'afinidad_alta') {
                if (!is_null($score)) {
                    return $score >= 70;
                }
                // Si aún no está evaluada con IA, verificar si es de entidad prioritaria
                foreach ($priorityEntities as $pe) {
                    if (!empty($pe) && mb_stripos($entidad, $pe, 0, 'UTF-8') !== false) {
                        return true;
                    }
                }
                return false;
            }

            if ($criterio === 'prioritarias') {
                foreach ($priorityEntities as $pe) {
                    if (!empty($pe) && mb_stripos($entidad, $pe, 0, 'UTF-8') !== false) {
                        return true;
                    }
                }
                return false;
            }

            return true;
        }));
    }
}

