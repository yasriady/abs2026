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

    // =========================
    // RELATIONS
    // =========================
    public function histories()
    {
        return $this->hasMany(PegawaiHistory::class, 'master_pegawai_id');
    }

    // public function activeHistory()
    // {
    //     return $this->hasOne(\App\Models\PegawaiHistory::class, 'master_pegawai_id')
    //         ->where('is_active', 1)
    //         ->latest('end_date');
    // }

    public function activeHistory()
    {
        $today = now()->toDateString();

        return $this->hasOne(\App\Models\PegawaiHistory::class, 'master_pegawai_id')
            ->where('begin_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->orderByDesc('begin_date');
    }

    public function activeHistoryAt(string $date)
    {
        return $this->hasOne(\App\Models\PegawaiHistory::class, 'master_pegawai_id')
            ->where('begin_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderByDesc('begin_date');
    }

    // Cara pakai di Controller:
    // $pegawais = MasterPegawai::with([
    //     'activeHistoryAt' => fn ($q) => $q
    // ])->get();


    public function getFotoUrlAttribute()
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : asset('images/default-user.png');
    }
}
