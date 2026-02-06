<?php

// app/Models/JadwalDinas.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalDinas extends Model
{
    protected $table = 'jadwal_dinas';

    protected $fillable = [
        'hari',
        'start_date',
        'end_date',
        'jam_masuk',
        'jam_pulang',
    ];

    public function scopeAktif($query, $date)
    {
        return $query
            ->where(function ($q) use ($date) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }
}
