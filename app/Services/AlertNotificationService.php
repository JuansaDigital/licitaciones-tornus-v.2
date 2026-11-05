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
     * Enviar un correo de prueba al usuario actual respetando estrictamente su mercado seleccionado
     */
    public static function sendTestEmail(User $user): bool
    {
        $userMarket = strtoupper($user->notificar_mercado ?? 'TODOS');

        $coTender = [
            'referencia' => 'FONTUR-LP-012-2026',
            'entidad' => 'FONDO NACIONAL DEL TURISMO (FONTUR)',
            'objeto' => 'Diseño, ejecución y evaluación de la campaña de promoción y posicionamiento turístico internacional de Colombia como destino biocultural de alto valor.',
            'formatted_cuantia' => '$ 2.850.000.000 COP',
            'fecha_cierre' => now()->addDays(18)->format('Y-m-d H:i:s'),
            'departamento' => 'Bogotá D.C.',
            'url_proceso' => 'https://community.secop.gov.co',
            'score' => 92,
        ];

        $peTender = [
            'referencia' => 'OECE-CP-045-2026',
            'entidad' => 'PROMPERÚ - COMISIÓN DE PROMOCIÓN DEL PERÚ',
            'objeto' => 'Servicio de agencia creativa 360 y producción multimedia para el desarrollo de contenidos promocionales en mercados emisores prioritarios.',
            'formatted_cuantia' => 'S/ 1.450.000 PEN',
            'fecha_cierre' => now()->addDays(24)->format('Y-m-d H:i:s'),
            'departamento' => 'Lima',
            'url_proceso' => 'https://prodapp2.seace.gob.pe',
            'score' => 88,
        ];

        $mockTenders = [];
        if ($userMarket === 'CO') {
            $mockTenders = [$coTender];
        } elseif ($userMarket === 'PE') {
            $mockTenders = [$peTender];
        } else {
            $mockTenders = [$coTender, $peTender];
        }

        $marketLabel = $userMarket === 'CO' ? 'Colombia' : ($userMarket === 'PE' ? 'Perú' : 'TODOS');

        try {
            Mail::to($user->email)->send(
                new NuevasLicitacionesMail(
                    licitaciones: $mockTenders,
                    market: $marketLabel,
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

        // 3b. REGLA ESTRICTA DE AFINIDAD CON IA: Excluir ofertas descartadas por Gemini
        $query->where(function ($q) {
            $q->whereNull('score')
              ->orWhere(function ($sub) {
                  $sub->where('score', '>=', 40)
                      ->where(function ($s2) {
                          $s2->whereNull('estrategia_postulacion')
                             ->orWhere('estrategia_postulacion', '!=', 'Descartar');
                      });
              });
        });

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
        $criterio = $user->notificar_criterio ?? 'viables';

        $filters = RadarFilterService::getData($market);
        $priorityEntities = $filters['priority_entities'] ?? [];

        return array_values(array_filter($licitaciones, function ($lic) use ($criterio, $priorityEntities) {
            $score = is_object($lic) ? ($lic->score ?? null) : ($lic['score'] ?? null);
            $estrategia = is_object($lic) ? ($lic->estrategia_postulacion ?? '') : ($lic['estrategia_postulacion'] ?? '');
            $entidad = is_object($lic) ? ($lic->entidad ?? '') : ($lic['entidad'] ?? '');

            // CRITERIO OFICIAL: Solo Oportunidades Viables (Afines o Pendientes) [Activo]
            // Incluye ofertas pendientes de evaluar (score null) y ofertas afines con score >= 40 sin descarte.
            // Excluye tajantemente cualquier oferta descartada por la IA (score < 40 o 'Descartar').
            if ($criterio === 'viables') {
                if (is_null($score)) {
                    return true;
                }
                return ($score >= 40) && strcasecmp($estrategia, 'Descartar') !== 0;
            }

            if ($criterio === 'afinidad_alta') {
                if (!is_null($score)) {
                    return $score >= 70 && strcasecmp($estrategia, 'Descartar') !== 0;
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

            if ($criterio === 'todas') {
                return true;
            }

            return true;
        }));
    }

    /**
     * Obtiene las propuestas analizadas actuales y viables para despacho por correo
     */
    public static function getCurrentAnalyzedTenders(string $market = 'CO', int $limit = 15): array
    {
        $connection = strtolower($market) === 'pe' ? 'peru' : 'colombia';
        $today = now()->toDateString();

        $query = \App\Models\Licitacion::on($connection);

        // Excluir canceladas o cerradas
        $query->whereNotIn('estado', ['Cancelado', 'Cerrado', 'Desierto', 'Adjudicado'])
              ->where(function ($q) use ($today) {
                  $q->whereNull('fecha_cierre')
                    ->orWhere('fecha_cierre', '>=', $today);
              });

        // 1. Obtener propuestas analizadas viables (score >= 40 sin descarte)
        $tenders = (clone $query)
            ->whereNotNull('score')
            ->where('score', '>=', 40)
            ->where(function ($s) {
                $s->whereNull('estrategia_postulacion')
                  ->orWhere('estrategia_postulacion', '!=', 'Descartar');
            })
            ->orderBy('score', 'desc')
            ->orderBy('fecha_publicacion', 'desc')
            ->limit($limit)
            ->get()
            ->all();

        // 2. Si hay menos de 5 analizadas viables, complementar con propuestas activas no descartadas
        if (count($tenders) < 5) {
            $existingIds = array_map(fn($t) => $t->id, $tenders);
            $additional = $query->whereNotIn('id', $existingIds)
                ->where(function ($s) {
                    $s->whereNull('score')
                      ->orWhere('score', '>=', 40);
                })
                ->where(function ($s) {
                    $s->whereNull('estrategia_postulacion')
                      ->orWhere('estrategia_postulacion', '!=', 'Descartar');
                })
                ->orderBy('fecha_publicacion', 'desc')
                ->limit($limit - count($tenders))
                ->get()
                ->all();

            $tenders = array_merge($tenders, $additional);
        }

        return $tenders;
    }

    /**
     * Despacha el boletín con las propuestas analizadas actuales a los miembros suscritos
     * respetando sus preferencias de mercado (CO, PE o TODOS) y criterios configurados.
     */
    public static function sendCurrentAnalyzedTendersAlert(?User $triggerUser = null): array
    {
        // 1. Obtener usuarios suscritos activos
        $users = User::where('activo', true)
            ->where('notificar_email', true)
            ->get();

        // Si el usuario que disparó la acción no estaba suscrito, incluirlo para que reciba una copia
        if ($triggerUser && !$users->contains('id', $triggerUser->id)) {
            $users->push($triggerUser);
        }

        if ($users->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No hay miembros del equipo con alertas por correo activadas.',
                'sent_count' => 0,
                'recipients' => []
            ];
        }

        // Obtener propuestas analizadas actuales por mercado
        $coTenders = self::getCurrentAnalyzedTenders('CO', 15);
        $peTenders = self::getCurrentAnalyzedTenders('PE', 15);

        $sentCount = 0;
        $recipients = [];

        foreach ($users as $user) {
            $userMarket = strtoupper($user->notificar_mercado ?? 'TODOS');

            $tendersToSend = [];
            if ($userMarket === 'CO') {
                $tendersToSend = self::filterTendersForUser($coTenders, $user, 'CO');
                $marketLabel = 'CO';
            } elseif ($userMarket === 'PE') {
                $tendersToSend = self::filterTendersForUser($peTenders, $user, 'PE');
                $marketLabel = 'PE';
            } else {
                $coFiltered = self::filterTendersForUser($coTenders, $user, 'CO');
                $peFiltered = self::filterTendersForUser($peTenders, $user, 'PE');
                $tendersToSend = array_merge($coFiltered, $peFiltered);
                $marketLabel = 'TODOS';
            }

            if (empty($tendersToSend)) {
                continue;
            }

            try {
                Mail::to($user->email)->send(
                    new NuevasLicitacionesMail(
                        licitaciones: $tendersToSend,
                        market: $marketLabel,
                        totalCount: count($tendersToSend),
                        recipientName: $user->name
                    )
                );
                $sentCount++;
                $recipients[] = $user->email;
                Log::info("Boletín de propuestas analizadas enviado a {$user->email} ({$marketLabel}, " . count($tendersToSend) . " propuestas)");
            } catch (\Throwable $e) {
                Log::error("Error despachando propuestas analizadas a {$user->email}: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'sent_count' => $sentCount,
            'recipients' => $recipients,
            'total_users' => $users->count()
        ];
    }
}

