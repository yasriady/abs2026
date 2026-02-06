<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class MasterPegawai extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'nik',
        'nip',
        'nama',
        'foto', // ⬅️ WAJIB
    ];

    // public function histories()
    // {
    //     return $this->hasMany(PegawaiHistory::class);
    // }

    // public function activeHistory()
    // {
    //     return $this->hasOne(PegawaiHistory::class)
    //         ->where('is_active', true);
    // }

    // =========================
    // RELATIONS
    // =========================
    public function histories()
    {
        return $this->hasMany(PegawaiHistory::class, 'master_pegawai_id');
    }

    // public function activeHistory()
    // {
    //     return $this->hasOne(PegawaiHistory::class, 'master_pegawai_id')
    //         ->where('is_active', 1)
    //         ->orderByDesc('begin_date');
    // }

    public function activeHistory()
    {
        return $this->hasOne(\App\Models\PegawaiHistory::class, 'master_pegawai_id')
            ->where('is_active', 1)
            ->latest('end_date');
    }

    public function getFotoUrlAttribute()
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : asset('images/default-user.png');
    }
}
