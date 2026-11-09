<?php

namespace App\Models;

use App\Services\MarketService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licitacion extends Model
{
    use HasFactory;

    protected $table = 'licitaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'cuantia' => 'float',
        'en_cartera' => 'boolean',
        'servicios_sugeridos' => 'array',
        'score' => 'integer',
        'evaluado_en' => 'datetime',
    ];

    protected $appends = [
        'formatted_cuantia',
        'codigo_proceso',
        'dias_convocatoria',
        'plazo_ejecucion_texto',
        'urgency_info',
    ];

    public function getConnectionName()
    {
        return $this->connection ?: MarketService::connection();
    }

    /**
     * Helper para formatear moneda acorde al mercado y manejar procesos sin cuantía fija (RFI / Cotización)
     */
    public function getFormattedCuantiaAttribute(): string
    {
        if (empty($this->cuantia) || (float)$this->cuantia <= 0) {
            return $this->moneda === 'PEN'
                ? 'A definir (En Bases SEACE)'
                : 'A definir (RFI / Cotización)';
        }
        $symbol = $this->moneda === 'PEN' ? 'S/' : '$';
        return $symbol . ' ' . number_format($this->cuantia, 2, '.', ',');
    }

    /**
     * Helper para garantizar que nunca se muestre 'No definido' si la descripción real
     * vino en el campo de referencia por parte de la entidad contratante.
     */
    public function getObjetoAttribute($value): string
    {
        $valTrim = trim((string)$value);
        if ((empty($valTrim) || strcasecmp($valTrim, 'No definido') === 0) && !empty($this->attributes['referencia'])) {
            return trim($this->attributes['referencia']);
        }
        return $valTrim ?: 'Objeto contractual en proceso de publicación';
    }

    /**
     * Normalizar códigos cortos de categoría ('evt', 'plan', 'serv', etc.) en etiquetas legibles
     */
    public function getCategoriaAttribute($value): string
    {
        $val = strtolower(trim((string)$value));
        $objUpper = mb_strtoupper($this->attributes['objeto'] ?? '', 'UTF-8');

        // Filtro de integridad: evitar etiquetar como 'Planeación y Turismo' a licitaciones de mantenimiento, manufactura u obras
        if ($val === 'plan' && !empty($objUpper)) {
            $isTourism = preg_match('/(TURIS|TURÍST|DESTINO|MARCA CIUDAD|MARCA REGIÓN|PLACE BRANDING|RUTA TURÍSTICA|PROCOLOMBIA|FONTUR|MINCIT|ATRACTIVO|CULTURA|ARTESAN)/u', $objUpper);
            if (!$isTourism) {
                if (preg_match('/(MANTENIMIENTO|OBRA|CONSTRUCCI|INFRAESTRUCTURA|EQUIPO|MAQUINARIA|PUENTE|VÍA|VIA|VIVIENDA|CALZADO|CUERO|MANUFACTURA)/u', $objUpper)) {
                    return 'Infraestructura y Dotación';
                }
                return 'Servicios Profesionales';
            }
        }

        $map = [
            'evt'       => 'Eventos y Logística',
            'plan'      => 'Planeación y Turismo',
            'pub'       => 'Publicidad y Medios',
            'estrat'    => 'Estrategia y Consultoría',
            'audio'     => 'Producción Audiovisual',
            'serv'      => 'Servicios Profesionales',
            'servicios' => 'Servicios Profesionales',
            'services'  => 'Servicios Profesionales',
            'tech'      => 'Tecnología y Digital',
            'infra'     => 'Infraestructura y Dotación',
            'works'     => 'Obras y Adecuaciones',
            'goods'     => 'Suministro de Bienes',
        ];

        return $map[$val] ?? ($value ? ucfirst(trim($value)) : 'Servicios Generales');
    }

    /**
     * Helper para obtener el código o número de proceso limpio (sin textos largos que desborden)
     */
    public function getCodigoProcesoAttribute(): string
    {
        // 1. Si la referencia es un código corto oficial (hasta 35 caracteres), usarla
        if (!empty($this->attributes['referencia'])) {
            $ref = trim($this->attributes['referencia']);
            if (strlen($ref) <= 35 && strcasecmp($ref, 'No definido') !== 0) {
                return $ref;
            }
        }

        // 2. Si no, usar external_id (ej. CO1.REQ.11225725)
        if (!empty($this->external_id)) {
            return trim($this->external_id);
        }

        return 'SIN REF';
    }

    /**
     * Helper para calcular los días de convocatoria reales entre publicación y cierre
     */
    public function getDiasConvocatoriaAttribute(): ?int
    {
        if (!empty($this->fecha_publicacion) && !empty($this->fecha_cierre)) {
            try {
                $pub = \Carbon\Carbon::parse(substr($this->fecha_publicacion, 0, 10));
                $cie = \Carbon\Carbon::parse(substr($this->fecha_cierre, 0, 10));
                return max(1, (int)$pub->diffInDays($cie));
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Plazo de ejecución verificado (según duración oficial de SECOP II o pliego)
     */
    public function getPlazoEjecucionTextoAttribute(): string
    {
        if (!empty($this->duracion) && !empty($this->unidad_duracion)) {
            return trim($this->duracion) . ' ' . trim($this->unidad_duracion);
        }
        if (!empty($this->duracion)) {
            return trim($this->duracion) . ' día(s)';
        }
        if (($this->moneda ?? '') === 'PEN') {
            return 'Según Bases Administrativas (SEACE)';
        }
        return 'Según pliego de condiciones';
    }

    /**
     * Helper de urgencia / tiempo restante hasta fecha de cierre
     */
    public function getUrgencyInfoAttribute(): array
    {
        if (empty($this->fecha_cierre)) {
            return [
                'badge' => 'Sin fecha fijada',
                'class' => 'bg-slate-100 text-slate-700 border-slate-200',
                'is_critical' => false,
                'hours_left' => null,
            ];
        }

        try {
            $now = new \DateTime();
            $cierreStr = trim($this->fecha_cierre);
            // Si viene solo YYYY-MM-DD sin hora, asumimos el fin del día (23:59:59)
            $cierre = strlen($cierreStr) <= 10
                ? new \DateTime($cierreStr . ' 23:59:59')
                : new \DateTime($cierreStr);

            $hasExplicitTime = strlen($cierreStr) > 10;
            $timeLabel = $hasExplicitTime ? ' · ' . $cierre->format('H:i') : '';

            // Ya venció
            if ($cierre < $now) {
                return [
                    'badge' => 'Cerrada' . $timeLabel,
                    'class' => 'bg-slate-200 text-slate-700 border-slate-300',
                    'is_critical' => false,
                    'hours_left' => 0,
                ];
            }

            // Horas y días exactos restantes
            $diff = $now->diff($cierre);
            $totalHours = ($diff->days * 24) + $diff->h;
            $days = $diff->days;

            // 1. Crítico: Menos de 48 horas
            if ($totalHours <= 48) {
                if ($days === 0) {
                    $badge = $totalHours <= 6
                        ? "¡Cierra en {$totalHours}h{$timeLabel}!"
                        : "Cierra hoy{$timeLabel} ({$totalHours}h)";
                } elseif ($days === 1) {
                    $badge = "Cierra mañana{$timeLabel} ({$totalHours}h)";
                } else {
                    $badge = "Cierra en {$totalHours} horas";
                }

                return [
                    'badge' => $badge,
                    'class' => 'bg-rose-100 text-rose-800 border-rose-300 font-bold shadow-2xs',
                    'is_critical' => true,
                    'hours_left' => $totalHours,
                ];
            }

            // 2. Esta semana: 3 a 7 días
            if ($days <= 7) {
                return [
                    'badge' => "Cierra en {$days} días{$timeLabel}",
                    'class' => 'bg-amber-100 text-amber-900 border-amber-300 font-semibold',
                    'is_critical' => false,
                    'hours_left' => $totalHours,
                ];
            }

            // 3. Con tiempo: 15 a 30 días
            if ($days <= 30) {
                return [
                    'badge' => "Cierra en {$days} días",
                    'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200 font-medium',
                    'is_critical' => false,
                    'hours_left' => $totalHours,
                ];
            }

            // 4. Con tiempo: más de 30 días
            $months = round($days / 30, 1);

            return [
                'badge' => "Con tiempo: {$days}d (~{$months} m)",
                'class' => 'bg-blue-50 text-blue-800 border-blue-200 font-medium',
                'is_critical' => false,
                'hours_left' => $totalHours,
            ];
        } catch (\Exception $e) {
            return [
                'badge' => substr($this->fecha_cierre, 0, 10),
                'class' => 'bg-slate-100 text-slate-600 border-slate-200',
                'is_critical' => false,
                'hours_left' => null,
            ];
        }
    }
}
