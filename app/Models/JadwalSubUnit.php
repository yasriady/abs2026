<?php

// app/Models/JadwalSubUnit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalSubUnit extends Model
{
    protected $table = 'jadwal_sub_units';

    protected $fillable = [
        'sub_unit_id',
        'hari',
        'start_date',
        'end_date',
        'jam_masuk',
        'jam_pulang',
    ];

    /**
     * Scope jadwal aktif berdasarkan tanggal
     */
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
