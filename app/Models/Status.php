<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $connection = 'TEMP';
    protected $table = 'status';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'desc',
        'day',
        'in',
        'out',
        'mid',
        'enable'
    ];

    /* ================= SCOPES ================= */

    public function scopeEnabled($q)
    {
        return $q->where('enable', 1);
    }

    public function scopeForDay($q)
    {
        return $q->where('day', 1);
    }

    public function scopeForIn($q)
    {
        return $q->where('in', 1);
    }

    public function scopeForOut($q)
    {
        return $q->where('out', 1);
    }
}
