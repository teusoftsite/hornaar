<?php

namespace App\Models;

use App\Support\NestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusEvent extends Model
{
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return ($this->from ? NestStatus::label($this->from) . ' → ' : '') . NestStatus::label($this->to);
    }
}
