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
                $entidadNombre = $row['Entidad'] ?? 'Entidad Pública Perú';
                $ruc = trim($row['RUC'] ?? '');
                $objetoTxt = $row['Objeto'] ?? '';

                Licitacion::on('peru')->updateOrCreate(
                    ['external_id' => $extId],
                    [
                        'referencia' => $row['ID'] ?? $extId,
                        'entidad' => $entidadNombre,
                        'nit_entidad' => $ruc ?: null,
                        'orden_entidad' => $this->detectarOrdenEntidadPeru($entidadNombre, 'Fondos Públicos'),
                        'adjudicador' => !empty($row['OCID']) ? 'OCID: ' . $row['OCID'] : null,
                        'objeto' => $objetoTxt,
                        'departamento' => $row['Departamento'] ?? '',
                        'cuantia' => $cuantia,
                        'moneda' => 'PEN',
                        'tipo_proceso' => $row['Modalidad'] ?? 'Concurso Público',
                        'tipo_contrato' => $this->traducirTipoContratoPeru($row['Categoria'] ?? 'services'),
                        'categoria' => $this->detectarCategoriaPeru($objetoTxt, '', $row['Categoria'] ?? 'services'),
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
                $response = Http::withoutVerifying()->withHeaders([
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

                $count += $this->ingestFromReleasesArray($releases);
            } catch (\Exception $e) {
                Log::error("Error consultando OECE Perú página {$page}: " . $e->getMessage());
                break;
            }
        }

        $this->purgeExpired('peru');

        return $count;
    }

    /**
     * Ingesta un arreglo de releases OCDS v1.1 de SEACE v3 / OECE Perú extrayendo el 100% de campos para Ficha 360
     */
    public function ingestFromReleasesArray(array $releases): int
    {
        $count = 0;

        foreach ($releases as $release) {
            $tender = $release['tender'] ?? [];
            $buyer = $release['buyer'] ?? [];
            $parties = $release['parties'] ?? [];
            $planning = $release['planning'] ?? [];

            $extId = $tender['title'] ?? $release['ocid'] ?? null;
            if (!$extId) continue;

            // 1. Extraer datos de la entidad contratante (RUC, Departamento, Provincia, Distrito, Dirección, Teléfono)
            $departamento = '';
            $provincia = '';
            $distrito = '';
            $direccion = '';
            $telefono = '';
            $ruc = '';

            foreach ($parties as $party) {
                $roles = $party['roles'] ?? [];
                if (!empty($roles) && !in_array('buyer', $roles) && !in_array('procuringEntity', $roles)) {
                    continue;
                }

                $addr = $party['address'] ?? [];
                if (!empty($addr['department'])) {
                    $departamento = trim($addr['department']);
                }
                if (!empty($addr['region'])) {
                    $provincia = trim($addr['region']);
                }
                if (!empty($addr['locality'])) {
                    $distrito = trim($addr['locality']);
                }
                if (!empty($addr['streetAddress'])) {
                    $direccion = trim($addr['streetAddress']);
                }

                $tel = trim($party['contactPoint']['telephone'] ?? '');
                if (!empty($tel) && $tel !== '0' && stripos($tel, 'Ver dato') === false) {
                    $telefono = $tel;
                }

                foreach ($party['additionalIdentifiers'] ?? [] as $ident) {
                    if (($ident['scheme'] ?? '') === 'PE-RUC' && !empty($ident['id'])) {
                        $ruc = trim($ident['id']);
                    }
                }
            }

            if (empty($departamento) && !empty($provincia)) {
                $departamento = $provincia;
            }

            // Construir Distrito (Provincia) para municipio
            $municipio = null;
            if (!empty($distrito) && !empty($provincia) && strcasecmp($distrito, $provincia) !== 0) {
                $municipio = "{$distrito} ({$provincia})";
            } elseif (!empty($distrito)) {
                $municipio = $distrito;
            } elseif (!empty($provincia) && strcasecmp($provincia, $departamento) !== 0) {
                $municipio = $provincia;
            }

            // 2. Presupuesto / Fuente de Financiamiento real del JSON (planning.budget.description)
            $entidadNombre = $buyer['name'] ?? ($tender['procuringEntity']['name'] ?? null);
            $ordenEntidad = $planning['budget']['description'] ?? null;

            // 3. Dirección, Teléfono, CONSUCODE y OCID literales del JSON
            $detallesEntidad = [];
            if (!empty($direccion)) {
                $detallesEntidad[] = $direccion;
            }
            if (!empty($telefono)) {
                $detallesEntidad[] = "Tel: {$telefono}";
            }
            $consucodeId = $parties[0]['identifier']['id'] ?? ($buyer['id'] ?? null);
            if (!empty($consucodeId)) {
                $detallesEntidad[] = "ID Entidad: {$consucodeId}";
            }
            if (!empty($release['ocid'])) {
                $detallesEntidad[] = "OCID: {$release['ocid']}";
            }
            $adjudicadorInfo = !empty($detallesEntidad) ? implode(' · ', $detallesEntidad) : null;

            // 4. Clasificación literal de Ítems del JSON (CUBSO + UNSPSC + Unidad/Cantidad + Estado)
            $items = $tender['items'] ?? [];
            $firstItem = $items[0] ?? [];
            $cubsoId = $firstItem['classification']['id'] ?? '';
            $cubsoDesc = $firstItem['classification']['description'] ?? '';
            $unspscId = $firstItem['additionalClassifications'][0]['id'] ?? '';
            $unspscDesc = $firstItem['additionalClassifications'][0]['description'] ?? '';

            $codigosArray = [];
            if (!empty($unspscId)) {
                $codigosArray[] = "UNSPSC {$unspscId}" . (!empty($unspscDesc) ? ": {$unspscDesc}" : '');
            }
            if (!empty($cubsoId)) {
                $codigosArray[] = "CUBSO {$cubsoId}" . (!empty($cubsoDesc) ? ": {$cubsoDesc}" : '');
            }
            $codigoUnspsc = !empty($codigosArray) ? implode(' · ', $codigosArray) : null;

            $unitName = $firstItem['unit']['name'] ?? '';
            $quantity = $firstItem['quantity'] ?? null;
            $baseMainCat = $tender['mainProcurementCategory'] ?? null;

            $tipoContratoParts = [];
            if (!empty($cubsoDesc)) {
                $tipoContratoParts[] = $cubsoDesc;
            } elseif (!empty($baseMainCat)) {
                $tipoContratoParts[] = $baseMainCat;
            }
            if (!empty($quantity) && !empty($unitName)) {
                $tipoContratoParts[] = "{$quantity} {$unitName}";
            }
            $tipoContrato = !empty($tipoContratoParts) ? implode(' · ', $tipoContratoParts) : null;

            // 5. Categoría del JSON (mainProcurementCategory o línea detectada para filtro)
            $objetoTxt = $tender['description'] ?? ($firstItem['description'] ?? '');
            $categoria = $this->detectarCategoriaPeru($objetoTxt, "{$cubsoDesc} {$unspscDesc}", $baseMainCat ?? 'services');

            // 6. Cronograma, Duración, Competencia y Documentos literales del JSON
            $valor = $tender['value'] ?? [];
            $monto = (float)($valor['amount'] ?? 0.0);
            if ($monto <= 0 && !empty($firstItem['totalValue']['amount'])) {
                $monto = (float)$firstItem['totalValue']['amount'];
            }

            $enquiry = $tender['enquiryPeriod'] ?? [];
            $tenderPeriod = $tender['tenderPeriod'] ?? [];
            $contractPeriod = $tender['contractPeriod'] ?? [];

            $fechaPublicacion = $tender['datePublished'] ?? ($release['publishedDate'] ?? null);
            $fechaCierre = $enquiry['endDate'] ?? ($tenderPeriod['endDate'] ?? null);
            $fechaInicioConsultas = $enquiry['startDate'] ?? ($tenderPeriod['startDate'] ?? null);

            $duracion = null;
            $unidadDuracion = null;
            if (!empty($contractPeriod['durationInDays'])) {
                $duracion = (string)$contractPeriod['durationInDays'];
                $unidadDuracion = 'día(s)';
            } elseif (!empty($enquiry['durationInDays'])) {
                $duracion = (string)$enquiry['durationInDays'];
                $unidadDuracion = 'día(s) (enquiryPeriod)';
            }

            $provUnicos = (int)($tender['numberOfTenderers'] ?? count($tender['tenderers'] ?? []));
            $itemStatus = !empty($firstItem['statusDetails']) ? trim($firstItem['statusDetails']) : 'CONVOCADO';
            $tags = $release['tag'] ?? [];
            $faseProceso = !empty($tags) ? implode(' / ', $tags) : null;

            // Enlace directo de descarga de Bases Administrativas del JSON (tender.documents[0].url)
            $docs = $tender['documents'] ?? [];
            $docUrl = $docs[0]['url'] ?? null;

            Licitacion::on('peru')->updateOrCreate(
                ['external_id' => $extId],
                [
                    'referencia' => $tender['title'] ?? $extId,
                    'entidad' => $entidadNombre,
                    'nit_entidad' => $ruc ?: null,
                    'orden_entidad' => $ordenEntidad,
                    'adjudicador' => $adjudicadorInfo,
                    'objeto' => $objetoTxt,
                    'departamento' => $departamento,
                    'municipio' => $municipio,
                    'cuantia' => $monto,
                    'moneda' => $valor['currency'] ?? 'PEN',
                    'tipo_proceso' => $tender['procurementMethodDetails'] ?? null,
                    'tipo_contrato' => $tipoContrato,
                    'codigo_unspsc' => $codigoUnspsc,
                    'categoria' => $categoria,
                    'estado' => $itemStatus,
                    'fase' => $faseProceso,
                    'duracion' => $duracion,
                    'unidad_duracion' => $unidadDuracion,
                    'prov_unicos' => $provUnicos,
                    'fecha_publicacion' => $fechaPublicacion,
                    'fecha_cierre' => $fechaCierre,
                    'fecha_apertura_ofertas' => $fechaInicioConsultas,
                    'fase2_fecha' => $docUrl,
                    'url_proceso' => !empty($release['ocid']) ? "https://contratacionesabiertas.oece.gob.pe/licitacion/{$release['ocid']}" : null,
                ]
            );
            $count++;
        }

        return $count;
    }

    protected function detectarOrdenEntidadPeru(string $entidad, string $fuente = 'Fondos Públicos'): string
    {
        $upper = mb_strtoupper($entidad, 'UTF-8');
        if (str_contains($upper, 'MINISTERIO') || str_contains($upper, 'PROMPERU') || str_contains($upper, 'MINCETUR') || str_contains($upper, 'SUPERINTENDENCIA') || str_contains($upper, 'PROGRAMA NACIONAL') || str_contains($upper, 'INSTITUTO NACIONAL')) {
            return "Gobierno Nacional · {$fuente}";
        }
        if (str_contains($upper, 'GOBIERNO REGIONAL') || str_contains($upper, 'DIRECCION REGIONAL') || str_contains($upper, 'GERENCIA REGIONAL')) {
            return "Gobierno Regional · {$fuente}";
        }
        if (str_contains($upper, 'MUNICIPALIDAD PROVINCIAL')) {
            return "Gobierno Provincial · {$fuente}";
        }
        if (str_contains($upper, 'MUNICIPALIDAD')) {
            return "Gobierno Local · {$fuente}";
        }
        return "Entidad Estatal · {$fuente}";
    }

    protected function traducirTipoContratoPeru(string $mainCategory, $quantity = null, string $unitName = ''): string
    {
        $map = [
            'services' => 'Contratación de Servicios (SEACE)',
            'goods' => 'Adquisición de Bienes (SEACE)',
            'works' => 'Ejecución de Obras (SEACE)',
            'consultingServices' => 'Consultoría (SEACE)',
        ];
        $base = $map[$mainCategory] ?? 'Contratación de Servicios (SEACE)';
        if (!empty($quantity) && !empty($unitName)) {
            return "{$base} · {$quantity} {$unitName}";
        }
        return $base;
    }

    protected function detectarCategoriaPeru(string $objeto, string $extraText = '', string $fallback = 'services'): string
    {
        $texto = mb_strtolower($objeto . ' ' . $extraText, 'UTF-8');

        if (preg_match('/(turism|turístic|turistic|promperu|promperú|mincetur|dircetur|gercetur|destino|famtrip|ruta turística|artesan)/u', $texto)) {
            return 'plan';
        }
        if (preg_match('/(evento|feria|congreso|convención|stand|catering|coffee break|alquiler de sonido|pantalla led|escenario|logística de|activación|btl|protocolo|aniversario|festival)/u', $texto)) {
            return 'evt';
        }
        if (preg_match('/(publicidad|publicitari|campaña|medios|pauta|difusión|merchandising|impresión|imprenta|gigantograf|banner|señalétic|comunicación|relaciones públicas|prensa)/u', $texto)) {
            return 'pub';
        }
        if (preg_match('/(audiovisual|video|spot|fotograf|grabación|transmisión|streaming|documental|postproducción|animación)/u', $texto)) {
            return 'audio';
        }
        if (preg_match('/(consultoría de comunicación|estudio de mercado|estrategia comunicacional|identidad visual|branding|diseño gráfico|marca)/u', $texto)) {
            return 'estrat';
        }
        if (preg_match('/(página web|pagina web|portal web|redes sociales|social media|marketing digital|software|aplicativo móvil|plataforma virtual)/u', $texto)) {
            return 'tech';
        }

        return $fallback ?: 'services';
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

