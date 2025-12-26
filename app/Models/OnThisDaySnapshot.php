<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnThisDaySnapshot extends Model
{
    protected $fillable = [
        'lang', 'year', 'month', 'day', 'type', 'text', 'payload', 'event_datetime'
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
