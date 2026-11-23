<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    public const STATUSES = [
        'zoeken' => 'Bestrijder zoeken',
        'aangeboden' => 'Aangeboden',
        'geaccepteerd' => 'Geaccepteerd',
        'afgerond' => 'Afgerond',
        'geen_bestrijder' => 'Geen bestrijder gevonden',
        'geannuleerd' => 'Geannuleerd',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'declined_ids' => 'array', 'offered_at' => 'datetime', 'expires_at' => 'datetime',
        'accepted_at' => 'datetime', 'closed_at' => 'datetime',
    ];

    public function nest(): BelongsTo { return $this->belongsTo(Nest::class); }
    public function report(): BelongsTo { return $this->belongsTo(Report::class); }
    public function offeredTo(): BelongsTo { return $this->belongsTo(Organisation::class, 'offered_to_id'); }
    public function bestrijder(): BelongsTo { return $this->belongsTo(Organisation::class, 'bestrijder_id'); }
    public function invoice(): BelongsTo { return $this->belongsTo(LeadInvoice::class, 'lead_invoice_id'); }

    public function statusLabel(): string { return self::STATUSES[$this->status] ?? $this->status; }
}
