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
                        'fecha_publicacion' => $row['fecha_publicacion'] ?? null,
                        'fecha_cierre' => $row['fecha_cierre'] ?? null,
                        'url_proceso' => $row['url_proceso'] ?? null,
                        'prov_unicos' => (int)($row['prov_unicos'] ?? 0),
                        'fase2_fecha' => $row['fase2_fecha'] ?? null,
                        'adenda_fecha' => $row['adenda_fecha'] ?? null,
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
     * REGLA DE ORO DE PERSISTENCIA:
     * NUNCA se eliminan las que están en Cartera (en_cartera = true).
     * NUNCA se eliminan las que han sido analizadas con IA (score IS NOT NULL).
     */
    public function purgeExpired(string $connection = 'colombia'): int
    {
        $today = now()->toDateString();
        $fiveMonthsAgo = now()->subMonths(5)->toDateString();

        try {
            return Licitacion::on($connection)
                ->where('en_cartera', false)
                ->whereNull('score')
                ->where(function ($q) use ($today, $fiveMonthsAgo) {
                    $q->where(function ($sq) use ($today) {
                        $sq->whereNotNull('fecha_cierre')
                           ->whereRaw("substr(fecha_cierre, 1, 10) < ?", [$today]);
                    })->orWhere(function ($sq) use ($fiveMonthsAgo) {
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

        // Palabras clave de interés dinámicas según configuración Tornus
        $filters = \App\Services\RadarFilterService::getData('CO');
        $keywords = !empty($filters['keywords']) ? $filters['keywords'] : ['TURISMO', 'BRANDING', 'MARKETING', 'PUBLICIDAD'];

        $whereClauses = [];
        foreach (array_slice($keywords, 0, 15) as $kw) {
            $kwEscaped = str_replace("'", "''", trim($kw));
            if (!empty($kwEscaped)) {
                $whereClauses[] = "upper(nombre_del_procedimiento) LIKE '%{$kwEscaped}%'";
            }
        }
        $whereKeywords = '(' . implode(' OR ', $whereClauses) . ')';

        // Ventana de 5 meses: Procesos publicados en los últimos 5 meses y con cierre vigente
        $whereDate = "(fecha_de_publicacion_del >= '{$fiveMonthsAgo}' AND (fecha_de_recepcion_de >= '{$today}' OR fecha_de_recepcion_de IS NULL))";
        $whereSql = "{$whereKeywords} AND {$whereDate} AND (upper(nombre_del_procedimiento) NOT LIKE '%CUNDINAMARCA%')";

        $query = "SELECT id_del_proceso, referencia, entidad, nit_entidad, ordenentidad, ciudad_entidad, departamento_entidad, nombre_del_procedimiento, precio_base, fecha_de_recepcion_de, fecha_de_publicacion_del, fecha_de_apertura_efectiva, urlproceso, modalidad_de_contratacion, tipo_de_contrato, duracion, unidad_de_duracion, codigo_principal_de_categoria, estado_del_procedimiento WHERE {$whereSql} ORDER BY fecha_de_recepcion_de DESC LIMIT {$limit}";

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
                        $extId = $row['id_del_proceso'] ?? $row['referencia'] ?? null;
                        if (!$extId) continue;

                        $cuantia = (float)($row['precio_base'] ?? 0);

                        Licitacion::on('colombia')->updateOrCreate(
                            ['external_id' => $extId],
                            [
                                'referencia' => $row['referencia'] ?? $extId,
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
                                'estado' => $row['estado_del_procedimiento'] ?? 'Abierto',
                                'fecha_publicacion' => !empty($row['fecha_de_publicacion_del']) ? substr($row['fecha_de_publicacion_del'], 0, 10) : null,
                                'fecha_cierre' => !empty($row['fecha_de_recepcion_de']) ? substr($row['fecha_de_recepcion_de'], 0, 10) : null,
                                'fecha_apertura_ofertas' => !empty($row['fecha_de_apertura_efectiva']) ? substr($row['fecha_de_apertura_efectiva'], 0, 10) : null,
                                'url_proceso' => $row['urlproceso'] ?? null,
                            ]
                        );
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

