<?php

namespace App\Http\Controllers;

use App\Services\MarketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            if (!MarketService::hasSelected()) {
                return redirect()->route('market.select');
            }
            return redirect()->route('radar');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if (!$user->isActivo()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Esta cuenta se encuentra desactivada. Contacta al administrador.',
                ])->onlyInput('email');
            }

            $user->ultimo_acceso = now();
            $user->save();

            $request->session()->regenerate();
            // La pantalla de selección de país viene después dentro del panel
            return redirect()->route('market.select');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no son correctas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function switchMarket(string $market)
    {
        MarketService::set($market);
        return back();
    }
}
