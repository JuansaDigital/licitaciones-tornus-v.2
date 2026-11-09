<?php

namespace App\Services;

class RadarFilterService
{
    public const DEFAULT_KEYWORDS_CO = [
        'TURISMO',
        'FAMTRIP',
        'MARKETING',
        'PUBLICIDAD',
        'CAMPAÑA',
        'MARCA',
        'PORTAL WEB',
        'PAGINA WEB',
        'promoción',
        'Plan de medios',
        'Estrategia de turismo',
        'Difusión del destino',
        'OPERACION LOGISTICA',
        'LOGISTICA',
        'FERIA',
        'FESTIVAL',
        'EVENTO',
        'TURISTICA',
        'TURISTICO'
    ];

    public const DEFAULT_EXCLUSIONS_CO = [
        'VIGILANCIA',
        'POLIZA',
        'SEGUROS',
        'SOAT',
        'VACUN',
        'COMBUSTIBLE',
        'ASEO',
        'PAPELERIA',
        'FUMIGA',
        'AVALUO',
        'REPUESTOS',
        'PARQUE AUTOMOTOR',
        'OBRA CIVIL',
        'PAVIMENTACION',
        'MANTENIMIENTO VIAL',
        'ALIMENTACION ESCOLAR',
        'PAE',
        'UNIFORMES',
        'DOTACION',
        'ARMAMENTO',
        'MUNICIONES',
        'FERRETERIA',
        'MATERIALES DE CONSTRUCCION',
        'ALUMBRADO PUBLICO',
        'DISPOSICION DE RESIDUOS',
        'TRANSPORTE ESCOLAR',
        'OXIGENO MEDICINAL',
        'MEDICAMENTOS',
        'REACTIVOS',
        'INFORMATICA HARDWARE',
        'CONECTIVIDAD WAN',
        'CATERING',
        'ARRENDAMIENTO INMUEBLE',
        'LLANTAS',
        'LUBRICANTES',
        'EXTINTORES',
        'CANINOS',
        'SEGURIDAD PRIVADA',
        'INTERVENTORIA VIAL',
        'INTERVENTORIA',
        'ESCOLAR',
        'COLEGIO',
        'EDUCATIVO',
        'RESTAURANTE ESCOLAR',
        'ESTUDIANTES',
        'OLIMPIADAS',
        'MAQUINARIA',
        'CARRO ROJO',
        'DESINFECCION',
        'LAVANDERIA',
        'MATERIAL QUIRURGICO',
        'AMBULANCIAS',
        'RECOLECCION BASURAS',
        'SUMINISTRO AGUA',
        'BATERIAS',
        'ACUEDUCTO',
        'ALCANTARILLADO'
    ];

    public const DEFAULT_PRIORITY_ENTITIES_CO = [
        'FONTUR',
        'FONDO NACIONAL DEL TURISMO',
        'SECRETARIA DE DESARROLLO ECONOMICO',
        'SECRETARIA DE TURISMO',
        'SECRETARIA DE CULTURA Y TURISMO',
        'INSTITUTO DISTRITAL DE TURISMO',
        'INSTITUTO DE CULTURA Y TURISMO',
        'INSTITUTO MUNICIPAL PARA EL TURISMO',
        'INSTITUTO MUNICIPAL DE TURISMO',
        'IDT',
        'IDECUT',
        'MINCIT',
        'MINISTERIO DE COMERCIO, INDUSTRIA Y TURISMO',
        'PROCOLOMBIA',
        'DIRECCION DE TURISMO',
        'SUBSECRETARIA DE TURISMO'
    ];

    public const DEFAULT_PRIORITY_ENTITIES_PE = [
        'PROMPERÚ',
        'PROMPERU',
        'MINCETUR',
        'MINISTERIO DE COMERCIO EXTERIOR Y TURISMO',
        'DIRCETUR',
        'GERCETUR',
        'CENFOTUR',
        'PLAN COPESCO NACIONAL',
        'COPESCO',
        'SERNANP',
        'GERENCIA DE DESARROLLO ECONOMICO Y TURISMO',
        'SUBGERENCIA DE TURISMO'
    ];

    protected static function getFilePath(string $market): string
    {
        $marketKey = strtolower($market) === 'pe' ? 'pe' : 'co';
        $suffix = app()->environment('testing') ? '_testing' : '';
        return storage_path("app/radar_filters_{$marketKey}{$suffix}.json");
    }

    public static function getData(string $market = 'CO'): array
    {
        $marketUpper = strtoupper($market);
        $defaultEntities = $marketUpper === 'PE' ? self::DEFAULT_PRIORITY_ENTITIES_PE : self::DEFAULT_PRIORITY_ENTITIES_CO;

        $path = self::getFilePath($market);
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                return [
                    'keywords' => $data['keywords'] ?? self::DEFAULT_KEYWORDS_CO,
                    'exclusions' => $data['exclusions'] ?? self::DEFAULT_EXCLUSIONS_CO,
                    'priority_entities' => $data['priority_entities'] ?? $defaultEntities
                ];
            }
        }

        return [
            'keywords' => self::DEFAULT_KEYWORDS_CO,
            'exclusions' => self::DEFAULT_EXCLUSIONS_CO,
            'priority_entities' => $defaultEntities
        ];
    }

    public static function save(string $market, array $keywords, array $exclusions, ?array $priorityEntities = null): void
    {
        $current = self::getData($market);
        $path = self::getFilePath($market);

        $entities = $priorityEntities !== null ? $priorityEntities : ($current['priority_entities'] ?? []);

        $data = [
            'keywords' => array_values(array_unique(array_filter(array_map('trim', $keywords)))),
            'exclusions' => array_values(array_unique(array_filter(array_map('trim', $exclusions)))),
            'priority_entities' => array_values(array_unique(array_filter(array_map('trim', $entities))))
        ];

        @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function addKeyword(string $market, string $keyword): void
    {
        $current = self::getData($market);
        $keyword = trim($keyword);
        if (!empty($keyword) && !in_array($keyword, $current['keywords'])) {
            $current['keywords'][] = $keyword;
            self::save($market, $current['keywords'], $current['exclusions'], $current['priority_entities']);
        }
    }

    public static function removeKeyword(string $market, string $keyword): void
    {
        $current = self::getData($market);
        $current['keywords'] = array_values(array_diff($current['keywords'], [trim($keyword)]));
        self::save($market, $current['keywords'], $current['exclusions'], $current['priority_entities']);
    }

    public static function updateKeyword(string $market, string $oldKeyword, string $newKeyword): void
    {
        $current = self::getData($market);
        $oldKeyword = trim($oldKeyword);
        $newKeyword = trim($newKeyword);

        if (!empty($newKeyword)) {
            $key = array_search($oldKeyword, $current['keywords']);
            if ($key !== false) {
                $current['keywords'][$key] = $newKeyword;
                self::save($market, $current['keywords'], $current['exclusions'], $current['priority_entities']);
            }
        }
    }

    public static function addExclusion(string $market, string $exclusion): void
    {
        $current = self::getData($market);
        $exclusion = trim($exclusion);
        if (!empty($exclusion) && !in_array($exclusion, $current['exclusions'])) {
            $current['exclusions'][] = $exclusion;
            self::save($market, $current['keywords'], $current['exclusions'], $current['priority_entities']);
        }
    }

    public static function removeExclusion(string $market, string $exclusion): void
    {
        $current = self::getData($market);
        $current['exclusions'] = array_values(array_diff($current['exclusions'], [trim($exclusion)]));
        self::save($market, $current['keywords'], $current['exclusions'], $current['priority_entities']);
    }

    public static function addPriorityEntity(string $market, string $entity): void
    {
        $current = self::getData($market);
        $entity = trim($entity);
        if (!empty($entity) && !in_array($entity, $current['priority_entities'])) {
            $current['priority_entities'][] = $entity;
            self::save($market, $current['keywords'], $current['exclusions'], $current['priority_entities']);
        }
    }

    public static function removePriorityEntity(string $market, string $entity): void
    {
        $current = self::getData($market);
        $current['priority_entities'] = array_values(array_diff($current['priority_entities'], [trim($entity)]));
        self::save($market, $current['keywords'], $current['exclusions'], $current['priority_entities']);
    }

    public static function resetPriorityEntities(string $market): void
    {
        $marketUpper = strtoupper($market);
        $defaultEntities = $marketUpper === 'PE' ? self::DEFAULT_PRIORITY_ENTITIES_PE : self::DEFAULT_PRIORITY_ENTITIES_CO;
        $current = self::getData($market);
        self::save($market, $current['keywords'], $current['exclusions'], $defaultEntities);
    }

    public static function resetDefaults(string $market): void
    {
        $marketUpper = strtoupper($market);
        $defaultEntities = $marketUpper === 'PE' ? self::DEFAULT_PRIORITY_ENTITIES_PE : self::DEFAULT_PRIORITY_ENTITIES_CO;
        self::save($market, self::DEFAULT_KEYWORDS_CO, self::DEFAULT_EXCLUSIONS_CO, $defaultEntities);
    }

    /**
     * Calcula cuántas licitaciones vigentes activas corresponden a cada entidad prioritaria
     */
    public static function getPriorityEntityCounts(string $connection, array $entities): array
    {
        $today = now()->toDateString();
        $counts = [];

        try {
            $entidadesList = \App\Models\Licitacion::on($connection)
                ->where(function ($q) use ($today) {
                    $q->whereNull('fecha_cierre')
                      ->orWhereRaw("substr(fecha_cierre, 1, 10) >= ?", [$today]);
                })
                ->whereNotIn('estado', ['Cancelado', 'Cerrado', 'Desierto', 'Adjudicado'])
                ->pluck('entidad')
                ->toArray();

            foreach ($entities as $ent) {
                $entUpper = mb_strtoupper(trim($ent), 'UTF-8');
                $c = 0;
                foreach ($entidadesList as $dbEnt) {
                    if (mb_stripos($dbEnt, $entUpper, 0, 'UTF-8') !== false) {
                        $c++;
                    }
                }
                $counts[$ent] = $c;
            }
        } catch (\Exception $e) {
            foreach ($entities as $ent) {
                $counts[$ent] = 0;
            }
        }

        return $counts;
    }
}
