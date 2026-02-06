<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'id',
        'device_id',
        'unit_id',
        'desc',
        'enabled',
        'public_key',
        'private_key',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
