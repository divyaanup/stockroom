<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingEvent extends Model
{
    protected $fillable = [
        'order_id',
        'carrier',
        'tracking_number',
        'shipped_at',
        'raw_payload',
        'signature',
    ];
    protected $casts = [
        'shipped_at'   => 'datetime',
        'raw_payload'  => 'array',
    ];


}
