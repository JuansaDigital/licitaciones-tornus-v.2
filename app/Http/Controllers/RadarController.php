<?php

namespace App\Http\Controllers;

use App\Models\Licitacion;
use App\Services\Ingestion\OecePeruService;
use App\Services\Ingestion\SecopColombiaService;
use App\Services\MarketService;
use App\Services\RadarFilterService;
use Illuminate\Http\Request;

class RadarController extends Controller
{
    public function index(Request $request)
    {
        $connection = MarketService::connection();
        $currentMarket = MarketService::current();
        $query = Licitacion::on($connection);

        // 0. Cargar palabras clave y exclusiones activas
        $filters = RadarFilterService::getData($currentMarket);
        $keywords = $filters['keywords'];
        $exclusions = $filters['exclusions'];

        // 1. REGLA ESTRICTA DE VIGENCIA: Excluir propuestas vencidas y canceladas
        $today = now()->toDateString();
        $query->where(function ($q) use ($today) {
            $q->whereNull('fecha_cierre')
              ->orWhereRaw("substr(fecha_cierre, 1, 10) >= ?", [$today]);
        })->whereNotIn('estado', ['Cancelado', 'Cerrado', 'Desierto', 'Adjudicado']);

        // 2. Aplicar filtro de búsqueda textual
        $search = trim($request->input('q', ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('objeto', 'like', "%{$search}%")
                  ->orWhere('entidad', 'like', "%{$search}%")
                  ->orWhere('referencia', 'like', "%{$search}%");
            });

            // Regla anti-falso positivo si busca "marca"
            if (stripos($search, 'marca') !== false) {
                $query->where('objeto', 'not like', '%Cundinamarca%');
            }
            // Regla anti-falso positivo si busca "campaña"
            if (stripos($search, 'campaña') !== false) {
                $query->where('objeto', 'not like', '%militar%')
                      ->where('objeto', 'not like', '%vacunación%');
            }
        }

        // 3. Aplicar filtro de exclusiones automáticas (si no está buscando explícitamente esa palabra)
        foreach ($exclusions as $ex) {
            $exTrim = trim($ex);
            if (!empty($exTrim) && (empty($search) || stripos($search, $exTrim) === false)) {
                $query->where('objeto', 'not like', "%{$exTrim}%")
                      ->where(function ($sub) use ($exTrim) {
                          $sub->whereNull('referencia')
                              ->orWhere('referencia', 'not like', "%{$exTrim}%");
                      });
            }
        }

        // 4. Filtro territorial
        $selectedRegion = trim($request->input('region', ''));
        if (!empty($selectedRegion)) {
            $query->where('departamento', 'like', "%{$selectedRegion}%");
        }

        // 4b. Filtro por Categoría / Línea de Negocio (Enfoque Turismo, Eventos, Publicidad, etc.)
        $selectedCategory = trim($request->input('category', 'all'));
        if ($selectedCategory === 'turismo') {
            $query->where(function ($q) {
                $q->where('categoria', 'plan')
                  ->orWhere('objeto', 'like', '%turis%')
                  ->orWhere('objeto', 'like', '%fontur%')
                  ->orWhere('objeto', 'like', '%famtrip%')
                  ->orWhere('objeto', 'like', '%destino%')
                  ->orWhere('entidad', 'like', '%turis%')
                  ->orWhere('entidad', 'like', '%fontur%');
            });
        } elseif ($selectedCategory === 'eventos') {
            $query->where(function ($q) {
                $q->where('categoria', 'evt')
                  ->orWhere('objeto', 'like', '%evento%')
                  ->orWhere('objeto', 'like', '%logístic%')
                  ->orWhere('objeto', 'like', '%logistic%')
                  ->orWhere('objeto', 'like', '%feria%')
                  ->orWhere('objeto', 'like', '%congreso%');
            });
        } elseif ($selectedCategory === 'publicidad') {
            $query->where(function ($q) {
                $q->where('categoria', 'pub')
                  ->orWhere('objeto', 'like', '%publicidad%')
                  ->orWhere('objeto', 'like', '%campaña%')
                  ->orWhere('objeto', 'like', '%pauta%')
                  ->orWhere('objeto', 'like', '%medios%')
                  ->orWhere('objeto', 'like', '%marca%');
            });
        } elseif ($selectedCategory === 'audiovisual') {
            $query->where(function ($q) {
                $q->where('categoria', 'audio')
                  ->orWhere('objeto', 'like', '%audiovisual%')
                  ->orWhere('objeto', 'like', '%producción%')
                  ->orWhere('objeto', 'like', '%fotograf%');
            });
        } elseif ($selectedCategory === 'estrategia') {
            $query->where(function ($q) {
                $q->where('categoria', 'estrat')
                  ->orWhere('objeto', 'like', '%estrategia%')
                  ->orWhere('objeto', 'like', '%consultor%')
                  ->orWhere('objeto', 'like', '%estudio de mercado%');
            });
        } elseif ($selectedCategory === 'digital') {
            $query->where(function ($q) {
                $q->where('categoria', 'tech')
                  ->orWhere('objeto', 'like', '%portal web%')
                  ->orWhere('objeto', 'like', '%página web%')
                  ->orWhere('objeto', 'like', '%pagina web%')
                  ->orWhere('objeto', 'like', '%plataforma%');
            });
        }

        // 5. Regiones disponibles para el filtro
        $regions = Licitacion::on($connection)
            ->whereNotNull('departamento')
            ->where('departamento', '!=', '')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento')
            ->toArray();

        // 6. Conteo de propuestas vigentes en el mercado (aplicando exclusiones activas)
        $totalQuery = Licitacion::on($connection)
            ->where(function ($q) use ($today) {
                $q->whereNull('fecha_cierre')
                  ->orWhereRaw("substr(fecha_cierre, 1, 10) >= ?", [$today]);
            })
            ->whereNotIn('estado', ['Cancelado', 'Cerrado', 'Desierto', 'Adjudicado']);

        foreach ($exclusions as $ex) {
            $exTrim = trim($ex);
            if (!empty($exTrim)) {
                $totalQuery->where('objeto', 'not like', "%{$exTrim}%")
                           ->where(function ($sub) use ($exTrim) {
                               $sub->whereNull('referencia')
                                   ->orWhere('referencia', 'not like', "%{$exTrim}%");
                           });
            }
        }
        $totalCount = $totalQuery->count();

        // 7. Filtro de Plazo de Cierre (Vencimiento)
        $selectedUrgency = $request->input('urgency', 'all');
        $dateCritical = now()->addDays(2)->toDateString();
        $dateWeek = now()->addDays(7)->toDateString();
        $dateMonthStart = now()->addDays(15)->toDateString();
        $dateMonthEnd = now()->addDays(30)->toDateString();
        $dateQuarterEnd = now()->addDays(90)->toDateString();

        if ($selectedUrgency === 'critical') {
            $query->whereNotNull('fecha_cierre')
                  ->whereRaw("substr(fecha_cierre, 1, 10) >= ?", [$today])
                  ->whereRaw("substr(fecha_cierre, 1, 10) <= ?", [$dateCritical]);
        } elseif ($selectedUrgency === 'week') {
            $query->whereNotNull('fecha_cierre')
                  ->whereRaw("substr(fecha_cierre, 1, 10) > ?", [$dateCritical])
                  ->whereRaw("substr(fecha_cierre, 1, 10) <= ?", [$dateWeek]);
        } elseif ($selectedUrgency === 'month') {
            $query->whereNotNull('fecha_cierre')
                  ->whereRaw("substr(fecha_cierre, 1, 10) >= ?", [$dateMonthStart])
                  ->whereRaw("substr(fecha_cierre, 1, 10) <= ?", [$dateMonthEnd]);
        } elseif ($selectedUrgency === 'quarter') {
            $query->whereNotNull('fecha_cierre')
                  ->whereRaw("substr(fecha_cierre, 1, 10) > ?", [$dateMonthEnd])
                  ->whereRaw("substr(fecha_cierre, 1, 10) <= ?", [$dateQuarterEnd]);
        }

        $licitaciones = $query
            ->orderByRaw("CASE WHEN fecha_cierre IS NOT NULL AND fecha_cierre != '' THEN fecha_cierre ELSE '9999-12-31' END ASC")
            ->paginate(15)
            ->withQueryString();

        return view('radar', compact(
            'licitaciones',
            'regions',
            'selectedRegion',
            'selectedCategory',
            'selectedUrgency',
            'search',
            'totalCount',
            'keywords',
            'exclusions'
        ));
    }

    public function addKeyword(Request $request)
    {
        $request->validate(['keyword' => 'required|string|max:100']);
        RadarFilterService::addKeyword(MarketService::current(), $request->input('keyword'));
        return back()->with('status', "Palabra clave '{$request->input('keyword')}' agregada al Radar.");
    }

    public function updateKeyword(Request $request)
    {
        $request->validate([
            'old_keyword' => 'required|string',
            'keyword' => 'required|string|max:100'
        ]);

        RadarFilterService::updateKeyword(
            MarketService::current(),
            $request->input('old_keyword'),
            $request->input('keyword')
        );

        return back()->with('status', "Palabra clave actualizada correctamente.");
    }

    public function removeKeyword(Request $request)
    {
        $request->validate(['keyword' => 'required|string']);
        RadarFilterService::removeKeyword(MarketService::current(), $request->input('keyword'));
        return back()->with('status', "Palabra clave '{$request->input('keyword')}' eliminada.");
    }

    public function addExclusion(Request $request)
    {
        $request->validate(['exclusion' => 'required|string|max:100']);
        RadarFilterService::addExclusion(MarketService::current(), $request->input('exclusion'));
        return back()->with('status', "Término de exclusión '{$request->input('exclusion')}' agregado.");
    }

    public function removeExclusion(Request $request)
    {
        $request->validate(['exclusion' => 'required|string']);
        RadarFilterService::removeExclusion(MarketService::current(), $request->input('exclusion'));
        return back()->with('status', "Término de exclusión eliminado.");
    }

    public function resetFilters(Request $request)
    {
        RadarFilterService::resetDefaults(MarketService::current());
        return back()->with('status', 'Palabras clave y términos de exclusión restablecidos a los valores predeterminados.');
    }

    public function toggleCartera(Request $request, $id)
    {
        $connection = MarketService::connection();
        $licitacion = Licitacion::on($connection)->findOrFail($id);

        $licitacion->en_cartera = !$licitacion->en_cartera;
        if ($licitacion->en_cartera && (empty($licitacion->cartera_estado) || !in_array($licitacion->cartera_estado, CarteraController::ESTADOS))) {
            $licitacion->cartera_estado = 'Identificada';
        }
        $licitacion->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'en_cartera' => $licitacion->en_cartera,
                'cartera_estado' => $licitacion->cartera_estado
            ]);
        }

        return back()->with('status', $licitacion->en_cartera ? 'Licitación agregada a Mi Cartera.' : 'Licitación retirada de Cartera.');
    }

    public function sync(Request $request, SecopColombiaService $secop, OecePeruService $oece)
    {
        $isPeru = MarketService::isPeru();
        $synced = 0;
        $purged = 0;

        if ($isPeru) {
            $synced = $oece->syncFromApi(2);
            $purged = $oece->purgeExpired('peru');
        } else {
            $res = $secop->syncFromSocrata(100);
            $synced = is_array($res) ? ($res['synced'] ?? 0) : $res;
            $purged = is_array($res) ? ($res['purged'] ?? 0) : $secop->purgeExpired('colombia');
        }

        return back()->with('status', "Radar sincronizado con éxito (ventana de los últimos 5 meses hasta hoy). Se actualizaron {$synced} procesos en vivo y se depuraron {$purged} licitaciones vencidas no guardadas.");
    }
}
