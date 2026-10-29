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
            return 'A definir (RFI / Cotización)';
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
     * Helper de urgencia / tiempo restante hasta fecha de cierre
     */
    public function getUrgencyInfoAttribute(): array
    {
        if (empty($this->fecha_cierre)) {
            return [
                'badge' => 'Sin fecha fijada',
                'class' => 'bg-slate-100 text-slate-700 border-slate-200',
                'is_critical' => false,
                'hours_left' => null
            ];
        }

        try {
            $todayStr = now()->format('Y-m-d');
            $cierreDateStr = substr($this->fecha_cierre, 0, 10);

            // Si cierra hoy exactamente
            if ($cierreDateStr === $todayStr) {
                return [
                    'badge' => 'Cierra hoy',
                    'class' => 'bg-rose-100 text-rose-800 border-rose-300 font-bold',
                    'is_critical' => true,
                    'hours_left' => 12
                ];
            }

            // Si es anterior a hoy
            if ($cierreDateStr < $todayStr) {
                return [
                    'badge' => 'Cerrada',
                    'class' => 'bg-slate-200 text-slate-700 border-slate-300',
                    'is_critical' => false,
                    'hours_left' => 0
                ];
            }

            $cierre = new \DateTime($cierreDateStr . ' 23:59:59');
            $now = new \DateTime(now()->format('Y-m-d H:i:s'));
            $diff = $now->diff($cierre);
            $days = $diff->days;

            if ($days <= 2) {
                return [
                    'badge' => 'Cierra en ' . ($days == 1 ? '1 día' : $days . ' días'),
                    'class' => 'bg-rose-100 text-rose-800 border-rose-300 font-bold',
                    'is_critical' => true,
                    'hours_left' => $days * 24
                ];
            } elseif ($days <= 7) {
                return [
                    'badge' => 'Cierra en ' . $days . ' días',
                    'class' => 'bg-amber-100 text-amber-900 border-amber-300 font-semibold',
                    'is_critical' => false,
                    'hours_left' => $days * 24
                ];
            } elseif ($days <= 30) {
                return [
                    'badge' => 'Cierra en ' . $days . ' días',
                    'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200 font-medium',
                    'is_critical' => false,
                    'hours_left' => $days * 24
                ];
            } else {
                $months = round($days / 30, 1);
                return [
                    'badge' => 'Con tiempo: ' . $days . 'd (~' . $months . ' m)',
                    'class' => 'bg-blue-50 text-blue-800 border-blue-200 font-medium',
                    'is_critical' => false,
                    'hours_left' => $days * 24
                ];
            }
        } catch (\Exception $e) {
            return [
                'badge' => substr($this->fecha_cierre, 0, 10),
                'class' => 'bg-slate-100 text-slate-600 border-slate-200',
                'is_critical' => false,
                'hours_left' => null
            ];
        }
    }
}
