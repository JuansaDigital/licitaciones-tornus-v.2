<?php

namespace App\Services;

class MarketService
{
    const MARKET_COLOMBIA = 'CO';
    const MARKET_PERU = 'PE';

    public static function hasSelected(): bool
    {
        return session()->has('market') && in_array(session('market'), [self::MARKET_COLOMBIA, self::MARKET_PERU]);
    }

    public static function current(): string
    {
        return session('market', self::MARKET_COLOMBIA);
    }

    public static function set(string $market): void
    {
        if (in_array(strtoupper($market), [self::MARKET_COLOMBIA, self::MARKET_PERU])) {
            session(['market' => strtoupper($market)]);
        }
    }

    public static function isColombia(): bool
    {
        return self::current() === self::MARKET_COLOMBIA;
    }

    public static function isPeru(): bool
    {
        return self::current() === self::MARKET_PERU;
    }

    public static function connection(): string
    {
        return self::isPeru() ? 'peru' : 'colombia';
    }

    public static function currency(): string
    {
        return self::isPeru() ? 'PEN' : 'COP';
    }

    public static function currencySymbol(): string
    {
        return self::isPeru() ? 'S/' : '$';
    }

    public static function marketName(): string
    {
        return self::isPeru() ? 'Perú' : 'Colombia';
    }

    public static function sourceName(): string
    {
        return self::isPeru() ? 'SEACE / OECE' : 'SECOP II';
    }
}