<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['surcharges' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];

    public const RESULTS = [
        'bestreden' => 'Bestreden',
        'geen_toegang' => 'Geen toegang',
        'geen_nest' => 'Geen nest aangetroffen',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceLine(): HasOne
    {
        return $this->hasOne(InvoiceLine::class);
    }
}
