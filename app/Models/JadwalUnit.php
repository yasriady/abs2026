<?php

// app/Models/JadwalUnit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalUnit extends Model
{
    protected $table = 'jadwal_units';

    protected $fillable = [
        'unit_id',
        'hari',
        'start_date',
        'end_date',
        'jam_masuk',
        'jam_pulang',
    ];

    /**
     * Scope jadwal aktif pada tanggal tertentu
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
