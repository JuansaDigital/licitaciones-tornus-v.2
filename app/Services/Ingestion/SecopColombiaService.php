<?php

namespace App\Services\Ingestion;

use App\Models\Licitacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecopColombiaService
{
    protected string $datasetId;
    protected ?string $keyId;
    protected ?string $secret;

    public function __construct()
    {
        $this->datasetId = config('services.socrata.dataset_id', env('SOCRATA_DATASET_ID', 'p6dx-8zbt'));
        $this->keyId = config('services.socrata.key_id', env('SOCRATA_KEY_ID'));
        $this->secret = config('services.socrata.secret', env('SOCRATA_SECRET'));
    }

    /**
     * Normaliza y preserva fecha y hora exacta de SECOP II (YYYY-MM-DD HH:mm:ss)
     * Resuelve con precisión el formato de fecha colombiano (d/m/Y Día/Mes/Año)
     * para evitar que días y meses se inviertan (ej: 09/10/2026 => 9 de octubre, no septiembre).
     */
    public function parseSecopDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $trimmed = trim($value);

        // Si contiene barras '/', se trata del formato estándar colombiano: d/m/Y (Día / Mes / Año)
        if (str_contains($trimmed, '/')) {
            $formats = [
                '!d/m/Y h:i:s A',
                '!j/n/Y h:i:s A',
                '!d/m/Y g:i:s A',
                '!j/n/Y g:i:s A',
                '!d/m/Y H:i:s',
                '!j/n/Y H:i:s',
                '!d/m/Y H:i',
                '!j/n/Y H:i',
                '!d/m/Y',
                '!j/n/Y',
            ];
            foreach ($formats as $fmt) {
                $d = \DateTime::createFromFormat($fmt, $trimmed);
                if ($d !== false) {
                    return $d->format('Y-m-d H:i:s');
                }
            }
        }

        try {
            $dt = new \DateTime($trimmed);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return strlen($trimmed) >= 19 ? str_replace('T', ' ', substr($trimmed, 0, 19)) : substr($trimmed, 0, 10);
        }
    }

    /**
     * Importar registros existentes de la base histórica si existe
     */
    public function importFromLegacyDatabase(string $legacyDbPath, int $limit = 500): int
    {
        if (!file_exists($legacyDbPath)) {
            return 0;
        }

        try {
            $pdo = new \PDO("sqlite:" . $legacyDbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            // Traer los procesos con fecha de cierre más reciente o vigentes
            $stmt = $pdo->prepare("
                SELECT * FROM procesos 
                ORDER BY CASE WHEN fecha_cierre IS NOT NULL AND fecha_cierre != '' THEN fecha_cierre ELSE fecha_publicacion END DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $imported = 0;
            foreach ($rows as $row) {
                $extId = $row['id_del_proceso'] ?? $row['referencia'] ?? null;
                if (!$extId) continue;

                $cuantia = (float)($row['cuantia'] ?? 0);

                Licitacion::on('colombia')->updateOrCreate(
                    ['external_id' => $extId],
                    [
                        'referencia' => $row['referencia'] ?? $extId,
                        'entidad' => $row['entidad'] ?? 'Entidad Pública',
                        'adjudicador' => $row['adjudicador'] ?? null,
                        'objeto' => $row['descripcion'] ?? $row['objeto'] ?? '',
                        'departamento' => $row['departamento'] ?? '',
                        'municipio' => $row['municipio'] ?? '',
                        'cuantia' => $cuantia,
                        'moneda' => 'COP',
                        'tipo_proceso' => $row['tipo_proceso'] ?? 'Licitación',
                        'categoria' => $row['categoria'] ?? 'servicios',
                        'estado' => $row['estado'] ?? 'Abierto',
                        'fase' => $row['fase'] ?? 'Presentación de ofertas',
                        'fecha_publicacion' => $this->parseSecopDate($row['fecha_publicacion'] ?? null),
                        'fecha_cierre' => $this->parseSecopDate($row['fecha_cierre'] ?? null),
                        'url_proceso' => is_array($row['url_proceso'] ?? null) ? ($row['url_proceso']['url'] ?? null) : ($row['url_proceso'] ?? null),
                        'prov_unicos' => (int)($row['prov_unicos'] ?? 0),
                        'fase2_fecha' => $this->parseSecopDate($row['fase2_fecha'] ?? null),
                        'adenda_fecha' => $this->parseSecopDate($row['adenda_fecha'] ?? null),
                    ]
                );
                $imported++;
            }

            return $imported;
        } catch (\Exception $e) {
            Log::error("Error importando datos legacy Colombia: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Purga automática de propuestas vencidas o fuera de la ventana de 5 meses.
     * REGLA DE PERSISTENCIA:
     * Únicamente se conservan históricamente las propuestas guardadas en Cartera (en_cartera = true).
     * Toda propuesta cerrada o vencida que NO esté en Cartera se elimina de la base de datos,
     * incluso si fue analizada con IA, para mantener el radar y afinidad 100% limpios y vigentes.
     */
    public function purgeExpired(string $connection = 'colombia'): int
    {
        $nowDateTime = now()->format('Y-m-d H:i:s');
        $today = now()->toDateString();
        $fiveMonthsAgo = now()->subMonths(5)->toDateString();

        try {
            // 1. Marcar como 'Cerrado' los procesos vencidos que se conservan por estar en Cartera
            Licitacion::on($connection)
                ->where('en_cartera', true)
                ->where(function ($sub) use ($nowDateTime, $today) {
                    $sub->where(function ($d) use ($nowDateTime, $today) {
                        $d->whereNotNull('fecha_cierre')
                          ->where('fecha_cierre', '!=', '')
                          ->where(function ($ts) use ($nowDateTime, $today) {
                              $ts->whereRaw("length(fecha_cierre) > 10 AND fecha_cierre < ?", [$nowDateTime])
                                 ->orWhereRaw("length(fecha_cierre) <= 10 AND substr(fecha_cierre, 1, 10) < ?", [$today]);
                          });
                    })->orWhereIn('estado', ['Evaluación', 'En evaluación']);
                })
                ->whereNotIn('estado', ['Cerrado', 'Adjudicado', 'Cancelado', 'Desierto'])
                ->update(['estado' => 'Cerrado']);

            // 2. Eliminar de la base activa todas las que ya cerraron, están en evaluación/adjudicadas o superan 5 meses y NO están en cartera
            return Licitacion::on($connection)
                ->where('en_cartera', false)
                ->where(function ($q) use ($nowDateTime, $today, $fiveMonthsAgo) {
                    $q->where(function ($sq) use ($nowDateTime, $today) {
                        $sq->whereNotNull('fecha_cierre')
                           ->where(function ($ts) use ($nowDateTime, $today) {
                               $ts->whereRaw("length(fecha_cierre) > 10 AND fecha_cierre < ?", [$nowDateTime])
                                  ->orWhereRaw("length(fecha_cierre) <= 10 AND substr(fecha_cierre, 1, 10) < ?", [$today]);
                           });
                    })->orWhereIn('estado', Licitacion::NON_ACTIVE_STATES)
                      ->orWhere(function ($sq) use ($fiveMonthsAgo) {
                        $sq->whereNotNull('fecha_publicacion')
                           ->whereRaw("substr(fecha_publicacion, 1, 10) < ?", [$fiveMonthsAgo]);
                    });
                })
                ->delete();
        } catch (\Exception $e) {
            Log::error("Error en purgeExpired ({$connection}): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Sincronizar desde la API Socrata / SECOP II con ventana móvil de 5 meses
     */
    public function syncFromSocrata(int $limit = 100): array
    {
        $today = now()->toDateString();
        $fiveMonthsAgo = now()->subMonths(5)->toDateString();

        // Palabras clave de interés y entidades prioritarias dinámicas según configuración Tornus
        $filters = \App\Services\RadarFilterService::getData('CO');
        $keywords = !empty($filters['keywords']) ? $filters['keywords'] : \App\Services\RadarFilterService::DEFAULT_KEYWORDS_CO;
        $priorityEntities = !empty($filters['priority_entities']) ? $filters['priority_entities'] : \App\Services\RadarFilterService::DEFAULT_PRIORITY_ENTITIES_CO;

        $criteriaClauses = [];
        foreach (array_slice($keywords, 0, 15) as $kw) {
            $kwEscaped = str_replace("'", "''", trim($kw));
            if (!empty($kwEscaped)) {
                $criteriaClauses[] = "upper(nombre_del_procedimiento) LIKE '%{$kwEscaped}%'";
            }
        }

        foreach (array_slice($priorityEntities, 0, 15) as $pe) {
            $peEscaped = str_replace("'", "''", trim($pe));
            if (!empty($peEscaped)) {
                $criteriaClauses[] = "upper(entidad) LIKE '%{$peEscaped}%'";
            }
        }

        $whereCriteria = !empty($criteriaClauses) ? '(' . implode(' OR ', $criteriaClauses) . ')' : "(upper(nombre_del_procedimiento) LIKE '%TURISMO%')";

        // Procesos con fecha de cierre vigente y en estado abierto / convocados para ofertar
        $whereDate = "(fecha_de_publicacion_del >= '{$fiveMonthsAgo}' AND fecha_de_recepcion_de >= '{$today}' AND estado_del_procedimiento NOT IN ('Cerrado', 'Evaluación', 'En evaluación', 'Adjudicado', 'Cancelado', 'Desierto', 'Terminado', 'Celebrado'))";
        $whereSql = "{$whereCriteria} AND {$whereDate}";

        $query = "SELECT id_del_proceso, referencia_del_proceso, entidad, nit_entidad, ordenentidad, ciudad_entidad, departamento_entidad, nombre_del_procedimiento, precio_base, fecha_de_recepcion_de, fecha_de_publicacion_del, fecha_de_apertura_efectiva, fecha_de_apertura_de_respuesta, fecha_de_adjudicacion, fecha_de_ultima_publicacion, fase, urlproceso, modalidad_de_contratacion, tipo_de_contrato, duracion, unidad_de_duracion, codigo_principal_de_categoria, estado_del_procedimiento WHERE {$whereSql} ORDER BY fecha_de_recepcion_de DESC LIMIT {$limit}";

        $count = 0;
        try {
            $client = Http::withoutVerifying()->timeout(25);
            if ($this->keyId && $this->secret) {
                $client = $client->withBasicAuth($this->keyId, $this->secret);
            }
            if ($this->keyId) {
                $client = $client->withHeaders(['X-App-Token' => $this->keyId]);
            }

            // Intentar endpoint v3
            $v3Url = "https://www.datos.gov.co/api/v3/views/{$this->datasetId}/query.json";
            $response = $client->get($v3Url, ['query' => $query]);

            // Si no responde v3, consultar endpoint SODA 2.1
            if (!$response->successful()) {
                $sodaUrl = "https://www.datos.gov.co/resource/{$this->datasetId}.json";
                $response = $client->get($sodaUrl, [
                    '$where' => $whereSql,
                    '$limit' => $limit,
                    '$order' => 'fecha_de_recepcion_de DESC'
                ]);
            }

            if ($response->successful()) {
                $data = $response->json();
                $records = $data['rows'] ?? $data ?? [];
                if (is_array($records)) {
                    foreach ($records as $row) {
                        $extId = $row['id_del_proceso'] ?? $row['referencia_del_proceso'] ?? $row['referencia'] ?? null;
                        if (!$extId) continue;

                        $cuantia = (float)($row['precio_base'] ?? 0);
                        $hasAdjudicacion = !empty($row['fecha_de_adjudicacion']);

                        $lic = Licitacion::on('colombia')->updateOrCreate(
                            ['external_id' => $extId],
                            [
                                'referencia' => $row['referencia_del_proceso'] ?? $row['referencia'] ?? $extId,
                                'entidad' => $row['entidad'] ?? 'Entidad Pública',
                                'nit_entidad' => $row['nit_entidad'] ?? null,
                                'orden_entidad' => $row['ordenentidad'] ?? null,
                                'objeto' => $row['nombre_del_procedimiento'] ?? '',
                                'departamento' => $row['departamento_entidad'] ?? '',
                                'municipio' => $row['ciudad_entidad'] ?? '',
                                'cuantia' => $cuantia,
                                'moneda' => 'COP',
                                'tipo_proceso' => $row['modalidad_de_contratacion'] ?? 'Convocatoria Pública',
                                'tipo_contrato' => $row['tipo_de_contrato'] ?? null,
                                'duracion' => !empty($row['duracion']) ? (string)$row['duracion'] : null,
                                'unidad_duracion' => $row['unidad_de_duracion'] ?? null,
                                'codigo_unspsc' => $row['codigo_principal_de_categoria'] ?? null,
                                'estado' => $hasAdjudicacion ? 'Adjudicado' : ($row['estado_del_procedimiento'] ?? 'Abierto'),
                                'fase' => $row['fase'] ?? ($hasAdjudicacion ? 'Adjudicado' : 'Presentación de ofertas'),
                                'fecha_publicacion' => $this->parseSecopDate($row['fecha_de_publicacion_del'] ?? $row['fecha_de_publicacion_fase_3'] ?? $row['fecha_de_publicacion_fase_2'] ?? null),
                                'fecha_cierre' => $this->parseSecopDate($row['fecha_de_recepcion_de'] ?? $row['fecha_de_apertura_de_respuesta'] ?? null),
                                'fecha_apertura_ofertas' => $this->parseSecopDate($row['fecha_de_apertura_efectiva'] ?? $row['fecha_de_apertura_de_respuesta'] ?? null),
                                'adenda_fecha' => $this->parseSecopDate($row['fecha_de_ultima_publicacion'] ?? null),
                                'fase2_fecha' => $this->parseSecopDate($row['fecha_de_adjudicacion'] ?? null),
                                'url_proceso' => is_array($row['urlproceso'] ?? null) ? ($row['urlproceso']['url'] ?? null) : ($row['urlproceso'] ?? null),
                            ]
                        );

                        // Auto-evaluación inmediata con IA: ninguna propuesta queda sin score
                        if ($lic->score === null) {
                            try {
                                app(\App\Services\GeminiAffinityService::class)->evaluate($lic);
                            } catch (\Throwable $e) {
                                Log::warning("Error en auto-evaluación inmediata #{$lic->id}: " . $e->getMessage());
                            }
                        }

                        $count++;
                    }
                }
            } else {
                Log::warning("SECOP II API status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Error sincronizando SECOP II: " . $e->getMessage());
        }

        // Ejecutar purga de vencidas fuera de la ventana de 5 meses (respetando cartera y análisis IA)
        $purged = $this->purgeExpired('colombia');

        return [
            'synced' => $count,
            'purged' => $purged
        ];
    }
}

