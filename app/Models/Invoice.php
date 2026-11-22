<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['sent_at' => 'datetime', 'paid_at' => 'datetime', 'due_on' => 'date'];

    public const STATUSES = [
        'concept' => 'Concept',
        'verstuurd' => 'Verstuurd',
        'betaald' => 'Betaald',
        'te_laat' => 'Te laat',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('id');
    }

    public function bestrijder(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'bestrijder_id');
    }

    public function gemeente(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'gemeente_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function isEditable(): bool
    {
        return $this->status === 'concept';
    }

    public function statusLabel(): string
    {
        return $this->credits_invoice_id ? 'Creditfactuur' : (self::STATUSES[$this->status] ?? $this->status);
    }
}
