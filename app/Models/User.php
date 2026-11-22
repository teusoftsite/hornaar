<?php

namespace App\Models;

use App\Support\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = ['name', 'email', 'password', 'role', 'organisation_id', 'active'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function isBeheerder(): bool
    {
        return $this->role === Role::BEHEERDER;
    }

    public function isGemeente(): bool
    {
        return $this->role === Role::GEMEENTE;
    }

    public function isBestrijder(): bool
    {
        return in_array($this->role, [Role::BESTRIJDER_EIGENAAR, Role::BESTRIJDER_MEDEWERKER], true);
    }

    public function isBestrijderEigenaar(): bool
    {
        return $this->role === Role::BESTRIJDER_EIGENAAR;
    }

    public function requiresTwoFactor(): bool
    {
        return in_array($this->role, Role::REQUIRES_2FA, true);
    }

    public function hasConfirmedTwoFactor(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    public function homeRoute(): string
    {
        return match (true) {
            $this->isBeheerder() => route('beheer.index'),
            $this->isGemeente() => route('gemeente.index'),
            $this->isBestrijder() => route('bestrijder.index'),
            default => url('/'),
        };
    }
}
