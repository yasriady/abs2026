<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLiburNasional extends Model
{
    protected $fillable = [
        'date',
        'year',
        'category',
        'description',
    ];
}
