<?php

namespace App\Services\Ingestion;

use App\Models\Licitacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OecePeruService
{
    protected string $apiBaseUrl = "https://contratacionesabiertas.oece.gob.pe/api/v1/releases";

    /**
     * Importar desde CSV local generado previamente
     */
    public function importFromCsv(string $csvPath): int
    {
        if (!file_exists($csvPath)) {
            return 0;
        }

        $imported = 0;
        if (($handle = fopen($csvPath, "r")) !== false) {
            // Leer cabecera (manejar posible BOM UTF-8)
            $header = fgetcsv($handle);
            if ($header && isset($header[0])) {
                $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
            }

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) < 5) continue;
                
                $row = array_combine(
                    ['ID', 'Entidad', 'RUC', 'Departamento', 'Objeto', 'Presupuesto_PEN', 'Publicacion', 'Cierre_Consultas', 'Modalidad', 'Categoria', 'OCID'],
                    array_pad($data, 11, '')
                );

                $extId = !empty($row['ID']) ? $row['ID'] : $row['OCID'];
                if (!$extId) continue;

                $cuantia = (float)($row['Presupuesto_PEN'] ?? 0);

                Licitacion::on('peru')->updateOrCreate(
                    ['external_id' => $extId],
                    [
                        'referencia' => $row['ID'] ?? $extId,
                        'entidad' => $row['Entidad'] ?? 'Entidad Pública Perú',
                        'adjudicador' => !empty($row['RUC']) ? 'RUC: ' . $row['RUC'] : null,
                        'objeto' => $row['Objeto'] ?? '',
                        'departamento' => $row['Departamento'] ?? '',
                        'cuantia' => $cuantia,
                        'moneda' => 'PEN',
                        'tipo_proceso' => $row['Modalidad'] ?? 'Concurso Público',
                        'categoria' => $row['Categoria'] ?? 'services',
                        'estado' => 'Convocado',
                        'fase' => 'Consultas / Presentación',
                        'fecha_publicacion' => $row['Publicacion'] ?? null,
                        'fecha_cierre' => $row['Cierre_Consultas'] ?? null,
                        'url_proceso' => !empty($row['OCID']) ? "https://contratacionesabiertas.oece.gob.pe/licitacion/{$row['OCID']}" : null,
                    ]
                );
                $imported++;
            }
            fclose($handle);
        }

        return $imported;
    }

    /**
     * Sincronizar desde la API OCDS de OECE / SEACE Perú
     */
    public function syncFromApi(int $maxPages = 3): int
    {
        $count = 0;

        for ($page = 1; $page <= $maxPages; $page++) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json'
                ])->timeout(15)->get("{$this->apiBaseUrl}?page={$page}");

                if (!$response->successful()) {
                    Log::warning("OECE API Perú error status: " . $response->status());
                    break;
                }

                $json = $response->json();
                $releases = $json['releases'] ?? [];
                if (empty($releases)) {
                    break;
                }

                foreach ($releases as $release) {
                    $tender = $release['tender'] ?? [];
                    $buyer = $release['buyer'] ?? [];
                    $parties = $release['parties'] ?? [];

                    $departamento = '';
                    $ruc = '';
                    foreach ($parties as $party) {
                        $addr = $party['address'] ?? [];
                        if (!empty($addr['region'])) {
                            $departamento = $addr['region'];
                        }
                        foreach ($party['additionalIdentifiers'] ?? [] as $ident) {
                            if (($ident['scheme'] ?? '') === 'PE-RUC') {
                                $ruc = $ident['id'] ?? '';
                            }
                        }
                    }

                    $extId = $tender['title'] ?? $release['ocid'] ?? null;
                    if (!$extId) continue;

                    $valor = $tender['value'] ?? [];
                    $monto = (float)($valor['amount'] ?? 0.0);
                    $enquiry = $tender['enquiryPeriod'] ?? [];
                    $cierre = $enquiry['endDate'] ?? null;

                    Licitacion::on('peru')->updateOrCreate(
                        ['external_id' => $extId],
                        [
                            'referencia' => $tender['title'] ?? $extId,
                            'entidad' => $buyer['name'] ?? 'Entidad Pública',
                            'adjudicador' => $ruc ? "RUC: {$ruc}" : null,
                            'objeto' => $tender['description'] ?? '',
                            'departamento' => $departamento,
                            'cuantia' => $monto,
                            'moneda' => 'PEN',
                            'tipo_proceso' => $tender['procurementMethodDetails'] ?? 'Concurso Público',
                            'categoria' => $tender['mainProcurementCategory'] ?? 'services',
                            'estado' => 'Convocado',
                            'fecha_publicacion' => $tender['datePublished'] ?? null,
                            'fecha_cierre' => $cierre,
                            'url_proceso' => !empty($release['ocid']) ? "https://contratacionesabiertas.oece.gob.pe/licitacion/{$release['ocid']}" : null,
                        ]
                    );
                    $count++;
                }
            } catch (\Exception $e) {
                Log::error("Error consultando OECE Perú página {$page}: " . $e->getMessage());
                break;
            }
        }

        $this->purgeExpired('peru');

        return $count;
    }

    /**
     * Purga automática de propuestas vencidas o fuera de la ventana de 5 meses en Perú.
     * REGLA DE ORO: No borra si está en cartera o analizada con IA.
     */
    public function purgeExpired(string $connection = 'peru'): int
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
            Log::error("Error en purgeExpired Perú: " . $e->getMessage());
            return 0;
        }
    }
}

