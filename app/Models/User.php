<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return strtolower($this->rol ?? '') === 'administrador';
    }

    public function isColaborador(): bool
    {
        return strtolower($this->rol ?? '') === 'colaborador';
    }

    public function isActivo(): bool
    {
        return (bool) $this->activo;
    }

    /**
     * Obtener iniciales para el avatar (ej: "Admin Tornus" -> "AT")
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split("/\s+/", trim($this->name ?? 'Usuario'));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $initials .= mb_substr($w, 0, 1, 'UTF-8');
            }
            if (mb_strlen($initials, 'UTF-8') >= 2) break;
        }
        return mb_strtoupper($initials ?: 'US', 'UTF-8');
    }
}
