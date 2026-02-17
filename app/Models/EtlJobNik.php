<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtlJobNik extends Model
{
    protected $fillable = [
        'nik',
        'date',
        'status',
        'log'
    ];
}
