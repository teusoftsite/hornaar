<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['valid_from' => 'date', 'valid_until' => 'date'];

    public function gemeente(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'gemeente_id');
    }

    public function bestrijder(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'bestrijder_id');
    }

    public function scopeActive($q)
    {
        $today = now()->toDateString();
        return $q->where('valid_from', '<=', $today)
            ->where(fn ($w) => $w->whereNull('valid_until')->orWhere('valid_until', '>=', $today));
    }

    public function label(): string
    {
        return $this->gemeente->name . ' ↔ ' . $this->bestrijder->name;
    }
}
