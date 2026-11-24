<?php

namespace App\Models;

use App\Support\NestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Nest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['urgent' => 'boolean', 'lat' => 'float', 'lng' => 'float', 'height_m' => 'float'];

    public function gemeente(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'gemeente_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class)->orderBy('id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class)->orderBy('id');
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)->whereIn('status', ['open', 'ingepland'])->latestOfMany();
    }

    public function events(): HasMany
    {
        return $this->hasMany(StatusEvent::class)->orderBy('id');
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Nest::class, 'merged_into_id');
    }

    public function statusLabel(): string
    {
        return NestStatus::label($this->status);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, NestStatus::OPEN, true);
    }

    public function leads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** Geen deelnemende gemeente: de inwoner regelt zelf een bestrijder via MeldHoornaar. */
    public function isParticulier(): bool
    {
        return $this->plek === 'eigen';
    }
}
