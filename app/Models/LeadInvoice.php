<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadInvoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['sent_at' => 'datetime', 'paid_at' => 'datetime', 'due_on' => 'date'];

    public function bestrijder(): BelongsTo { return $this->belongsTo(Organisation::class, 'bestrijder_id'); }
    public function leads(): HasMany { return $this->hasMany(Lead::class); }
}
