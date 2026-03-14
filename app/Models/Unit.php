<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit',
    ];

    // 20260223

    public function pegawaiHistories()
    {
        return $this->hasMany(PegawaiHistory::class, 'id_unit');
    }

    public function subUnits()
    {
        return $this->hasMany(SubUnit::class, 'id_unit');
    }
}
