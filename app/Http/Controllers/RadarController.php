<?php

namespace App\Http\Controllers;

use App\Models\Licitacion;
use App\Services\Ingestion\OecePeruService;
use App\Services\Ingestion\SecopColombiaService;
use App\Services\MarketService;
use App\Services\RadarFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RadarController extends Controller
{
    public function index(Request $request)
    {
        $connection = MarketService::connection();
        $currentMarket = MarketService::current();
        $query = Licitacion::on($connection);

        // 0. Cargar palabras clave, exclusiones y entidades prioritarias activas
        $filters = RadarFilterService::getData($currentMarket);
        $keywords = $filters['keywords'];
        $exclusions = $filters['exclusions'];
        $priorityEntities = $filters['priority_entities'] ?? [];

        // 1. REGLA ESTRICTA DE VIGENCIA: Excluir propuestas vencidas y canceladas
        $nowDateTime = now()->format('Y-m-d H:i:s');
        $today = now()->toDateString();
        $query->where(function ($q) use ($nowDateTime, $today) {
            $q->whereNull('fecha_cierre')
              ->orWhere(function ($sub) use ($nowDateTime, $today) {
                  $sub->whereRaw("length(fecha_cierre) > 10 AND fecha_cierre >= ?", [$nowDateTime])
                      ->orWhereRaw("length(fecha_cierre) <= 10 AND substr(fecha_cierre, 1, 10) >= ?", [$today]);
              });
        })->whereNotIn('estado', ['Cancelado', 'Cerrado', 'Desierto', 'Adjudicado']);

        // 2. Aplicar filtro de búsqueda textual
        $search = trim($request->input('q', ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('objeto', 'like', "%{$search}%")
                  ->orWhere('entidad', 'like', "%{$search}%")
                  ->orWhere('referencia', 'like', "%{$search}%")
                  ->orWhere('departamento', 'like', "%{$search}%")
                  ->orWhere('municipio', 'like', "%{$search}%");
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

        // 3b. Filtro de Foco Prioritario (Solo entidades estratégicas)
        $selectedPriorityOnly = $request->boolean('priority_only');
        if ($selectedPriorityOnly && !empty($priorityEntities)) {
            $query->where(function ($q) use ($priorityEntities) {
                foreach ($priorityEntities as $idx => $pEnt) {
                    $pTrim = trim($pEnt);
                    if (!empty($pTrim)) {
                        if ($idx === 0) {
                            $q->where('entidad', 'like', "%{$pTrim}%");
                        } else {
                            $q->orWhere('entidad', 'like', "%{$pTrim}%");
                        }
                    }
                }
            });
        }

        // 4. Filtro territorial (Departamento y Municipio)
        $selectedRegion = trim($request->input('region', ''));
        $selectedMunicipio = trim($request->input('municipio', ''));

        if (!empty($selectedRegion)) {
            $query->where('departamento', 'like', "%{$selectedRegion}%");
        }

        if (!empty($selectedMunicipio)) {
            $query->where('municipio', 'like', "%{$selectedMunicipio}%");
        }

        // 4b. Filtro por Categorías / Líneas de Negocio (Multi-selección)
        $selectedCategories = $request->input('categories', []);
        if (!is_array($selectedCategories)) {
            $selectedCategories = !empty($selectedCategories) ? [$selectedCategories] : [];
        }
        // Soporte de compatibilidad hacia atrás con parámetro singular 'category'
        $selectedCategory = trim($request->input('category', ''));
        if (empty($selectedCategories) && !empty($selectedCategory) && $selectedCategory !== 'all') {
            $selectedCategories = [$selectedCategory];
        }

        if (!empty($selectedCategories) && !in_array('all', $selectedCategories)) {
            $query->where(function ($q) use ($selectedCategories) {
                foreach ($selectedCategories as $idx => $cat) {
                    $applyCondition = function ($sub) use ($cat) {
                        if ($cat === 'turismo') {
                            $sub->where('categoria', 'like', '%Turismo%')
                                ->orWhere('objeto', 'like', '%turis%')
                                ->orWhere('objeto', 'like', '%fontur%')
                                ->orWhere('objeto', 'like', '%promper%')
                                ->orWhere('objeto', 'like', '%mincetur%')
                                ->orWhere('objeto', 'like', '%dircetur%')
                                ->orWhere('objeto', 'like', '%famtrip%')
                                ->orWhere('objeto', 'like', '%destino%')
                                ->orWhere('entidad', 'like', '%turis%')
                                ->orWhere('entidad', 'like', '%fontur%')
                                ->orWhere('entidad', 'like', '%promper%')
                                ->orWhere('entidad', 'like', '%mincetur%');
                        } elseif ($cat === 'eventos') {
                            $sub->where('categoria', 'like', '%Eventos%')
                                ->orWhere('objeto', 'like', '%evento%')
                                ->orWhere('objeto', 'like', '%logístic%')
                                ->orWhere('objeto', 'like', '%logistic%')
                                ->orWhere('objeto', 'like', '%feria%')
                                ->orWhere('objeto', 'like', '%congreso%')
                                ->orWhere('objeto', 'like', '%festival%');
                        } elseif ($cat === 'publicidad') {
                            $sub->where('categoria', 'like', '%Publicidad%')
                                ->orWhere('objeto', 'like', '%publicidad%')
                                ->orWhere('objeto', 'like', '%campaña%')
                                ->orWhere('objeto', 'like', '%pauta%')
                                ->orWhere('objeto', 'like', '%medios%')
                                ->orWhere('objeto', 'like', '%marca%')
                                ->orWhere('objeto', 'like', '%creatividad%');
                        } elseif ($cat === 'audiovisual') {
                            $sub->where('categoria', 'like', '%Audiovisual%')
                                ->orWhere('objeto', 'like', '%audiovisual%')
                                ->orWhere('objeto', 'like', '%producción%')
                                ->orWhere('objeto', 'like', '%produccion%')
                                ->orWhere('objeto', 'like', '%fotograf%')
                                ->orWhere('objeto', 'like', '%video%');
                        } elseif ($cat === 'estrategia') {
                            $sub->where('categoria', 'like', '%Estrategia%')
                                ->orWhere('categoria', 'like', '%Consultoría%')
                                ->orWhere('objeto', 'like', '%estrategia%')
                                ->orWhere('objeto', 'like', '%consultor%')
                                ->orWhere('objeto', 'like', '%estudio de mercado%')
                                ->orWhere('objeto', 'like', '%diagnóstico%')
                                ->orWhere('objeto', 'like', '%diagnostico%');
                        } elseif ($cat === 'digital') {
                            $sub->where('categoria', 'like', '%Digital%')
                                ->orWhere('categoria', 'like', '%Tecnología%')
                                ->orWhere('objeto', 'like', '%portal web%')
                                ->orWhere('objeto', 'like', '%página web%')
                                ->orWhere('objeto', 'like', '%pagina web%')
                                ->orWhere('objeto', 'like', '%plataforma%')
                                ->orWhere('objeto', 'like', '%software%')
                                ->orWhere('objeto', 'like', '%aplicación%');
                        }
                    };

                    if ($idx === 0) {
                        $q->where($applyCondition);
                    } else {
                        $q->orWhere($applyCondition);
                    }
                }
            });
        }

        // 5. Regiones y Municipios disponibles para el filtro territorial
        $regions = Licitacion::on($connection)
            ->whereNotNull('departamento')
            ->where('departamento', '!=', '')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento')
            ->toArray();

        // Obtener municipios estructurados con maestro completo oficial (1.104 municipios)
        $municipios = RadarFilterService::getMunicipios($connection, $selectedRegion);

        // 6. Conteo de propuestas vigentes en el mercado (aplicando exclusiones activas)
        $totalQuery = Licitacion::on($connection)
            ->where(function ($q) use ($nowDateTime, $today) {
                $q->whereNull('fecha_cierre')
                  ->orWhere(function ($sub) use ($nowDateTime, $today) {
                      $sub->whereRaw("length(fecha_cierre) > 10 AND fecha_cierre >= ?", [$nowDateTime])
                          ->orWhereRaw("length(fecha_cierre) <= 10 AND substr(fecha_cierre, 1, 10) >= ?", [$today]);
                  });
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

        // 7. Filtro de Plazo de Cierre (Vencimiento Estratégico)
        // - 'all': Todos los plazos vigentes
        // - 'critical': Tiempos críticos (desde 7 días hacia 0 horas / cierre urgente)
        // - 'early': Alertas tempranas (desde 7 días hasta 5 meses con tiempo de antelación)
        $selectedUrgency = $request->input('urgency', 'all');
        $dateWeek = now()->addDays(7)->toDateString();
        $dateFiveMonths = now()->addMonths(5)->toDateString();

        if ($selectedUrgency === 'critical') {
            $query->whereNotNull('fecha_cierre')
                  ->where(function ($sub) use ($nowDateTime, $today) {
                      $sub->whereRaw("length(fecha_cierre) > 10 AND fecha_cierre >= ?", [$nowDateTime])
                          ->orWhereRaw("length(fecha_cierre) <= 10 AND substr(fecha_cierre, 1, 10) >= ?", [$today]);
                  })
                  ->whereRaw("substr(fecha_cierre, 1, 10) <= ?", [$dateWeek]);
        } elseif ($selectedUrgency === 'early') {
            $query->whereNotNull('fecha_cierre')
                  ->whereRaw("substr(fecha_cierre, 1, 10) > ?", [$dateWeek])
                  ->whereRaw("substr(fecha_cierre, 1, 10) <= ?", [$dateFiveMonths]);
        }

        $criticalCount = (clone $totalQuery)
            ->whereNotNull('fecha_cierre')
            ->where(function ($sub) use ($nowDateTime, $today) {
                $sub->whereRaw("length(fecha_cierre) > 10 AND fecha_cierre >= ?", [$nowDateTime])
                    ->orWhereRaw("length(fecha_cierre) <= 10 AND substr(fecha_cierre, 1, 10) >= ?", [$today]);
            })
            ->whereRaw("substr(fecha_cierre, 1, 10) <= ?", [$dateWeek])
            ->count();

        $earlyCount = (clone $totalQuery)
            ->whereNotNull('fecha_cierre')
            ->whereRaw("substr(fecha_cierre, 1, 10) > ?", [$dateWeek])
            ->whereRaw("substr(fecha_cierre, 1, 10) <= ?", [$dateFiveMonths])
            ->count();

        // 8. FILTRO INTELIGENTE DE AFINIDAD CON IA (AUTO-TRIAGE TORNUS)
        // 'viable' (por defecto): Muestra propuestas afines desde un 20% en adelante (score >= 20) o pendientes de evaluar.
        // 'all': Muestra todas las propuestas sin discriminación por IA.
        // 'discarded': Muestra exclusivamente las propuestas descartadas por IA (score < 20).
        $selectedIaFilter = $request->input('ia_filter', 'viable');
        if ($selectedIaFilter === 'viable') {
            $query->where(function ($q) {
                $q->whereNull('score')
                  ->orWhere('score', '>=', 20);
            });
        } elseif ($selectedIaFilter === 'discarded') {
            $query->whereNotNull('score')
                  ->where('score', '<', 20);
        }

        $discardedCount = (clone $totalQuery)
            ->whereNotNull('score')
            ->where('score', '<', 20)
            ->count();

        $pendingEvaluationCount = (clone $totalQuery)
            ->whereNull('score')
            ->count();

        // ORDENAMIENTO: Cuando el filtro de afinidad está activo ('viable'), ordenar de MAYOR afinidad a MENOR afinidad
        // (desde 20% en adelante), y colocar luego las pendientes de evaluar ordenadas por fecha de cierre más próxima.
        if ($selectedIaFilter === 'viable') {
            $query->orderByRaw("CASE WHEN score IS NOT NULL AND score >= 20 THEN 0 ELSE 1 END ASC")
                  ->orderBy('score', 'desc')
                  ->orderByRaw("CASE WHEN fecha_cierre IS NOT NULL AND fecha_cierre != '' THEN fecha_cierre ELSE '9999-12-31' END ASC");
        } else {
            $query->orderByRaw("CASE WHEN fecha_cierre IS NOT NULL AND fecha_cierre != '' THEN fecha_cierre ELSE '9999-12-31' END ASC");
        }

        $licitaciones = $query
            ->paginate(15)
            ->withQueryString();

        $priorityEntityCounts = RadarFilterService::getPriorityEntityCounts($connection, $priorityEntities);

        return view('radar', compact(
            'licitaciones',
            'regions',
            'selectedRegion',
            'municipios',
            'selectedMunicipio',
            'selectedCategory',
            'selectedCategories',
            'selectedUrgency',
            'criticalCount',
            'earlyCount',
            'selectedIaFilter',
            'discardedCount',
            'pendingEvaluationCount',
            'search',
            'totalCount',
            'keywords',
            'exclusions',
            'priorityEntities',
            'priorityEntityCounts',
            'selectedPriorityOnly'
        ));
    }

    /**
     * Ejecuta Auto-Triage con IA en lote sobre las ofertas pendientes del mercado activo
     */
    public function autoTriage(Request $request, \App\Services\GeminiAffinityService $gemini)
    {
        $connection = MarketService::connection();
        $pendientes = Licitacion::on($connection)
            ->whereNull('score')
            ->whereNotIn('estado', ['Cancelado', 'Cerrado', 'Desierto', 'Adjudicado'])
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        if ($pendientes->isEmpty()) {
            return back()->with('status', 'No hay propuestas pendientes de evaluación con IA en este momento.');
        }

        $evaluadas = 0;
        $descartadas = 0;

        foreach ($pendientes as $lic) {
            try {
                $res = $gemini->evaluate($lic);
                $evaluadas++;
                if (($res['score'] ?? 0) < 20 || strcasecmp($res['estrategia_postulacion'] ?? '', 'Descartar') === 0) {
                    $descartadas++;
                }
            } catch (\Throwable $e) {
                // Continuar evaluando los demás
            }
        }

        $viables = $evaluadas - $descartadas;
        return back()->with('status', "Auto-Triage con IA completado: {$evaluadas} ofertas evaluadas. {$descartadas} descartadas por no ser afines y {$viables} confirmadas viables.");
    }

    public function addKeyword(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $request->validate(['keyword' => 'required|string|max:100']);
        $market = $request->input('market', MarketService::current());
        RadarFilterService::addKeyword($market, $request->input('keyword'));
        return back()->with('status', "Palabra clave '{$request->input('keyword')}' agregada al Radar.");
    }

    public function updateKeyword(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $request->validate([
            'old_keyword' => 'required|string',
            'keyword' => 'required|string|max:100'
        ]);
        $market = $request->input('market', MarketService::current());

        RadarFilterService::updateKeyword(
            $market,
            $request->input('old_keyword'),
            $request->input('keyword')
        );

        return back()->with('status', "Palabra clave actualizada correctamente.");
    }

    public function removeKeyword(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $request->validate(['keyword' => 'required|string']);
        $market = $request->input('market', MarketService::current());
        RadarFilterService::removeKeyword($market, $request->input('keyword'));
        return back()->with('status', "Palabra clave '{$request->input('keyword')}' eliminada.");
    }

    public function addExclusion(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $request->validate(['exclusion' => 'required|string|max:100']);
        $market = $request->input('market', MarketService::current());
        RadarFilterService::addExclusion($market, $request->input('exclusion'));
        return back()->with('status', "Término de exclusión '{$request->input('exclusion')}' agregado.");
    }

    public function removeExclusion(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $request->validate(['exclusion' => 'required|string']);
        $market = $request->input('market', MarketService::current());
        RadarFilterService::removeExclusion($market, $request->input('exclusion'));
        return back()->with('status', "Término de exclusión eliminado.");
    }

    public function addPriorityEntity(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $request->validate(['entity' => 'required|string|max:150']);
        $market = $request->input('market', MarketService::current());
        RadarFilterService::addPriorityEntity($market, $request->input('entity'));
        return back()->with('status', "Entidad prioritaria '{$request->input('entity')}' agregada al Radar.");
    }

    public function removePriorityEntity(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $request->validate(['entity' => 'required|string']);
        $market = $request->input('market', MarketService::current());
        RadarFilterService::removePriorityEntity($market, $request->input('entity'));
        return back()->with('status', "Entidad prioritaria eliminada del Radar.");
    }

    public function resetPriorityEntities(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $market = $request->input('market', MarketService::current());
        RadarFilterService::resetPriorityEntities($market);
        return back()->with('status', "Entidades prioritarias restablecidas a los valores oficiales de " . MarketService::marketName($market) . ".");
    }

    public function resetFilters(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Solo los administradores tienen permiso para modificar los filtros de rastreo.']);
        }
        $market = $request->input('market', MarketService::current());
        RadarFilterService::resetDefaults($market);
        return back()->with('status', "Palabras clave, exclusiones y entidades prioritarias restablecidas a los valores predeterminados de " . MarketService::marketName($market) . ".");
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
        if (!\App\Services\SyncConfigService::canManualSync()) {
            $limit = \App\Services\SyncConfigService::getLimit();
            return back()->withErrors(['error' => "Se ha alcanzado el límite configurado de {$limit} consultas manuales diarias a la API de compras públicas. El sistema continuará actualizándose automáticamente en su frecuencia programada."]);
        }

        \App\Services\SyncConfigService::recordSync();

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

        if ($synced > 0) {
            try {
                \Illuminate\Support\Facades\Artisan::call('tornus:send-alerts', [
                    'market' => $isPeru ? 'PE' : 'CO',
                    '--hours' => 2,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error despachando alertas en sync: ' . $e->getMessage());
            }
        }

        return back()->with('status', "Radar sincronizado con éxito (ventana de los últimos 5 meses hasta hoy). Se actualizaron {$synced} procesos en vivo y se depuraron {$purged} licitaciones vencidas no guardadas.");
    }

    public function exportExcel()
    {
        return \App\Services\Export\ExcelExportService::download('radar');
    }
}
