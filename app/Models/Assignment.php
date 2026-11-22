<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['planned_on' => 'date'];

    public function nest(): BelongsTo
    {
        return $this->belongsTo(Nest::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /** Alleen opdrachten van deze bestrijder. */
    public function scopeForBestrijder($q, int $bestrijderId)
    {
        return $q->whereHas('contract', fn ($c) => $c->where('bestrijder_id', $bestrijderId));
    }
}
