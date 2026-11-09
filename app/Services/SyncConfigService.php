<?php

namespace App\Services;

class SyncConfigService
{
    public const FREQUENCIES = [
        'hourly' => 'Cada 1 hora (Alta frecuencia)',
        'everyTwoHours' => 'Cada 2 horas (Recomendado)',
        'everyFourHours' => 'Cada 4 horas (Moderado)',
        'everySixHours' => 'Cada 6 horas (4 veces al día)',
        'twiceDaily' => 'Cada 12 horas (2 veces al día)',
        'daily' => 'Cada 24 horas (1 vez al día)'
    ];

    public const EMAIL_FREQUENCIES = [
        'realtime' => 'En cada sincronización (Tiempo real al detectar ofertas)',
        'daily' => 'Una vez al día (Resumen Matutino 08:00 COT)',
        'twiceDaily' => 'Dos veces al día (08:00 y 17:00 COT)',
        'disabled' => 'Desactivado (Sin correos automáticos)'
    ];

    protected static function getFilePath(): string
    {
        $suffix = app()->environment('testing') ? '_testing' : '';
        return storage_path("app/sync_config{$suffix}.json");
    }

    public static function getConfig(): array
    {
        $path = self::getFilePath();
        $default = [
            'frequency' => 'everyTwoHours',
            'daily_manual_limit' => 50,
            'email_frequency' => 'daily',
            'today_count' => 0,
            'last_sync_date' => now()->toDateString(),
            'last_sync_at' => null
        ];

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                $merged = array_merge($default, $data);
                // Si cambió de fecha, reiniciar contador diario
                if ($merged['last_sync_date'] !== now()->toDateString()) {
                    $merged['today_count'] = 0;
                    $merged['last_sync_date'] = now()->toDateString();
                }
                return $merged;
            }
        }

        return $default;
    }

    public static function saveConfig(string $frequency, int $dailyManualLimit, string $emailFrequency = 'daily'): void
    {
        $current = self::getConfig();
        $current['frequency'] = array_key_exists($frequency, self::FREQUENCIES) ? $frequency : 'everyTwoHours';
        $current['daily_manual_limit'] = max(1, min(100, $dailyManualLimit));
        $current['email_frequency'] = array_key_exists($emailFrequency, self::EMAIL_FREQUENCIES) ? $emailFrequency : 'daily';

        file_put_contents(self::getFilePath(), json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function getFrequency(): string
    {
        $config = self::getConfig();
        return $config['frequency'] ?? 'everyTwoHours';
    }

    public static function getFrequencyLabel(): string
    {
        $freq = self::getFrequency();
        return self::FREQUENCIES[$freq] ?? self::FREQUENCIES['everyTwoHours'];
    }

    public static function getEmailFrequency(): string
    {
        $config = self::getConfig();
        return $config['email_frequency'] ?? 'daily';
    }

    public static function getEmailFrequencyLabel(): string
    {
        $freq = self::getEmailFrequency();
        return self::EMAIL_FREQUENCIES[$freq] ?? self::EMAIL_FREQUENCIES['daily'];
    }

    public static function getLimit(): int
    {
        $config = self::getConfig();
        return (int) ($config['daily_manual_limit'] ?? 15);
    }

    public static function getTodayCount(): int
    {
        $config = self::getConfig();
        return (int) ($config['today_count'] ?? 0);
    }

    public static function getLastSyncAt(): ?string
    {
        $config = self::getConfig();
        return $config['last_sync_at'] ?? null;
    }

    public static function canManualSync(): bool
    {
        $config = self::getConfig();
        $limit = (int) ($config['daily_manual_limit'] ?? 50);
        $count = (int) ($config['today_count'] ?? 0);

        return $count < $limit;
    }

    public static function resetTodayCount(): void
    {
        $config = self::getConfig();
        $config['today_count'] = 0;
        $config['last_sync_date'] = now()->toDateString();
        file_put_contents(self::getFilePath(), json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function recordSync(): void
    {
        $config = self::getConfig();
        if (($config['last_sync_date'] ?? '') !== now()->toDateString()) {
            $config['today_count'] = 1;
            $config['last_sync_date'] = now()->toDateString();
        } else {
            $config['today_count'] = (int) ($config['today_count'] ?? 0) + 1;
        }

        $config['last_sync_at'] = now()->format('Y-m-d H:i:s');
        file_put_contents(self::getFilePath(), json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

