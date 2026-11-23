<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisation extends Model
{
    use HasFactory;

    public const GEMEENTE = 'gemeente';
    public const BESTRIJDER = 'bestrijder';

    protected $guarded = ['id'];

    protected $casts = ['active' => 'boolean', 'base_lat' => 'float', 'base_lng' => 'float', 'accepts_leads' => 'boolean'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function contractsAsGemeente(): HasMany
    {
        return $this->hasMany(Contract::class, 'gemeente_id');
    }

    public function contractsAsBestrijder(): HasMany
    {
        return $this->hasMany(Contract::class, 'bestrijder_id');
    }

    public function isGemeente(): bool
    {
        return $this->type === self::GEMEENTE;
    }

    public function isBestrijder(): bool
    {
        return $this->type === self::BESTRIJDER;
    }

    public function scopeGemeenten($q)
    {
        return $q->where('type', self::GEMEENTE);
    }

    public function scopeBestrijders($q)
    {
        return $q->where('type', self::BESTRIJDER);
    }
}
