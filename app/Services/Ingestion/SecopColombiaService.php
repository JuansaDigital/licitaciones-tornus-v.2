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
     * Sincronizar desde la API Socrata v3 de SECOP II
     */
    public function syncFromSocrata(int $limit = 100): int
    {
        $v3Url = "https://www.datos.gov.co/api/v3/views/{$this->datasetId}/query.json";

        // Palabras clave de interés para Tornus
        $keywords = ['TURISMO', 'BRANDING', 'COMUNICACIONES', 'AUDIOVISUAL', 'MARKETING', 'EVENTOS', 'PUBLICIDAD', 'PAUTA'];
        $whereClauses = [];
        foreach ($keywords as $kw) {
            $whereClauses[] = "upper(nombre_del_procedimiento) LIKE '%{$kw}%'";
        }
        $whereSql = '(' . implode(' OR ', $whereClauses) . ')';
        // Regla anti falso positivo: Si tiene MARCA, excluir CUNDINAMARCA
        $whereSql .= " AND (upper(nombre_del_procedimiento) NOT LIKE '%CUNDINAMARCA%')";

        $query = "SELECT id_del_proceso, referencia, entidad, ciudad_entidad, departamento_entidad, nombre_del_procedimiento, precio_base, fecha_de_recepcion_de, fecha_de_publicacion_del, urlproceso, modalidad_de_contratacion, estado_del_procedimiento WHERE {$whereSql} ORDER BY fecha_de_recepcion_de DESC LIMIT {$limit}";

        try {
            $client = Http::timeout(20);
            if ($this->keyId && $this->secret) {
                $client = $client->withBasicAuth($this->keyId, $this->secret);
            }

            $response = $client->get($v3Url, ['query' => $query]);

            if (!$response->successful()) {
                Log::warning("SECOP II API returned HTTP " . $response->status());
                return 0;
            }

            $data = $response->json();
            $records = $data['rows'] ?? $data ?? [];
            if (!is_array($records)) {
                return 0;
            }

            $count = 0;
            foreach ($records as $row) {
                $extId = $row['id_del_proceso'] ?? $row['referencia'] ?? null;
                if (!$extId) continue;

                $cuantia = (float)($row['precio_base'] ?? 0);

                Licitacion::on('colombia')->updateOrCreate(
                    ['external_id' => $extId],
                    [
                        'referencia' => $row['referencia'] ?? $extId,
                        'entidad' => $row['entidad'] ?? 'Entidad Pública',
                        'objeto' => $row['nombre_del_procedimiento'] ?? '',
                        'departamento' => $row['departamento_entidad'] ?? '',
                        'municipio' => $row['ciudad_entidad'] ?? '',
                        'cuantia' => $cuantia,
                        'moneda' => 'COP',
                        'tipo_proceso' => $row['modalidad_de_contratacion'] ?? 'Convocatoria Pública',
                        'estado' => $row['estado_del_procedimiento'] ?? 'Abierto',
                        'fecha_publicacion' => $row['fecha_de_publicacion_del'] ?? null,
                        'fecha_cierre' => $row['fecha_de_recepcion_de'] ?? null,
                        'url_proceso' => $row['urlproceso'] ?? null,
                    ]
                );
                $count++;
            }

            return $count;
        } catch (\Exception $e) {
            Log::error("Error sincronizando SECOP II: " . $e->getMessage());
            return 0;
        }
    }
}

