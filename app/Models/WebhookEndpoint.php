<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    protected $fillable = [
        'type',
        'url',
        'secret',
        'last_status',
        'last_response_at',
    ];

    protected $casts = [
        'last_response_at' => 'datetime',
    ];
}
