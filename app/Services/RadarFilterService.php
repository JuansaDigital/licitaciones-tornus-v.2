<?php

namespace App\Services;

class RadarFilterService
{
    const DEFAULT_KEYWORDS_CO = [
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
        'Difusión del destino'
    ];

    const DEFAULT_EXCLUSIONS_CO = [
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
        'CARRO ROJO',
        'DESINFECCION',
        'LAVANDERIA',
        'MATERIAL QUIRURGICO',
        'AMBULANCIAS',
        'RECOLECCION BASURAS',
        'SUMINISTRO AGUA',
        'BATERIAS',
        'ACUEDUCTO',
        'ALCANTARILLADO',
        'CUNDINAMARCA'
    ];

    protected static function getFilePath(string $market): string
    {
        $marketKey = strtolower($market) === 'pe' ? 'pe' : 'co';
        $suffix = app()->environment('testing') ? '_testing' : '';
        return storage_path("app/radar_filters_{$marketKey}{$suffix}.json");
    }

    public static function getData(string $market = 'CO'): array
    {
        $path = self::getFilePath($market);
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data) && isset($data['keywords']) && isset($data['exclusions'])) {
                return $data;
            }
        }

        return [
            'keywords' => self::DEFAULT_KEYWORDS_CO,
            'exclusions' => self::DEFAULT_EXCLUSIONS_CO
        ];
    }

    public static function save(string $market, array $keywords, array $exclusions): void
    {
        $path = self::getFilePath($market);
        $data = [
            'keywords' => array_values(array_unique(array_filter(array_map('trim', $keywords)))),
            'exclusions' => array_values(array_unique(array_filter(array_map('trim', $exclusions))))
        ];

        @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function addKeyword(string $market, string $keyword): void
    {
        $current = self::getData($market);
        $keyword = trim($keyword);
        if (!empty($keyword) && !in_array($keyword, $current['keywords'])) {
            $current['keywords'][] = $keyword;
            self::save($market, $current['keywords'], $current['exclusions']);
        }
    }

    public static function removeKeyword(string $market, string $keyword): void
    {
        $current = self::getData($market);
        $current['keywords'] = array_values(array_diff($current['keywords'], [trim($keyword)]));
        self::save($market, $current['keywords'], $current['exclusions']);
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
                self::save($market, $current['keywords'], $current['exclusions']);
            }
        }
    }

    public static function addExclusion(string $market, string $exclusion): void
    {
        $current = self::getData($market);
        $exclusion = trim($exclusion);
        if (!empty($exclusion) && !in_array($exclusion, $current['exclusions'])) {
            $current['exclusions'][] = $exclusion;
            self::save($market, $current['keywords'], $current['exclusions']);
        }
    }

    public static function removeExclusion(string $market, string $exclusion): void
    {
        $current = self::getData($market);
        $current['exclusions'] = array_values(array_diff($current['exclusions'], [trim($exclusion)]));
        self::save($market, $current['keywords'], $current['exclusions']);
    }

    public static function resetDefaults(string $market): void
    {
        self::save($market, self::DEFAULT_KEYWORDS_CO, self::DEFAULT_EXCLUSIONS_CO);
    }
}
