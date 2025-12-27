<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnThisDaySnapshot extends Model
{
    protected $fillable = [
        'lang', 'year', 'month', 'day', 'type', 'text', 'payload', 'event_datetime'
    ];

    protected $casts = [
        'event_datetime' => 'datetime',
        'payload' => 'array',
    ];

    public function getEventDatetimeAttribute($value): string
    {
        return $this->asDateTime($value)->toIso8601String();
    }
}
