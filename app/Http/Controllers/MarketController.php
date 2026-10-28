<?php

namespace App\Http\Controllers;

use App\Models\Licitacion;
use App\Services\MarketService;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    /**
     * Pantalla interna dentro del panel para seleccionar el país / mercado
     */
    public function showSelect()
    {
        $colombiaCount = Licitacion::on('colombia')->count();
        $peruCount = Licitacion::on('peru')->count();
        $currentMarket = session('market');

        return view('market.select', compact('colombiaCount', 'peruCount', 'currentMarket'));
    }

    /**
     * Asignar país / mercado activo y redirigir al radar
     */
    public function selectMarket(Request $request, string $market)
    {
        $marketUpper = strtoupper($market);
        if (in_array($marketUpper, [MarketService::MARKET_COLOMBIA, MarketService::MARKET_PERU])) {
            MarketService::set($marketUpper);
            return redirect()->route('radar');
        }

        return redirect()->route('market.select');
    }
}

