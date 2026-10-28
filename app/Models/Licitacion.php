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
     * Helper para formatear moneda acorde al mercado
     */
    public function getFormattedCuantiaAttribute(): string
    {
        $symbol = $this->moneda === 'PEN' ? 'S/' : '$';
        return $symbol . ' ' . number_format($this->cuantia, 2, '.', ',');
    }

    /**
     * Helper de urgencia / tiempo restante hasta fecha de cierre
     */
    public function getUrgencyInfoAttribute(): array
    {
        if (empty($this->fecha_cierre)) {
            return [
                'badge' => 'Sin fecha',
                'class' => 'bg-slate-100 text-slate-600 border-slate-200',
                'is_critical' => false,
                'hours_left' => null
            ];
        }

        try {
            $cierre = new \DateTime($this->fecha_cierre);
            $now = new \DateTime();
            
            if ($now > $cierre) {
                return [
                    'badge' => 'Cerrada',
                    'class' => 'bg-slate-200 text-slate-700 border-slate-300',
                    'is_critical' => false,
                    'hours_left' => 0
                ];
            }

            $diff = $now->diff($cierre);
            $hours = ($diff->days * 24) + $diff->h;

            if ($hours < 48) {
                return [
                    'badge' => '< 48h (' . $hours . 'h)',
                    'class' => 'bg-rose-50 text-rose-700 border-rose-200 font-semibold',
                    'is_critical' => true,
                    'hours_left' => $hours
                ];
            } elseif ($hours <= 168) { // 7 dias
                return [
                    'badge' => $diff->days . 'd restantes',
                    'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'is_critical' => false,
                    'hours_left' => $hours
                ];
            } else {
                return [
                    'badge' => $diff->days . 'd restantes',
                    'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'is_critical' => false,
                    'hours_left' => $hours
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
