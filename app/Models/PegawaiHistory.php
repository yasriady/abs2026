<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiHistory extends Model
{
    protected $fillable = [
        'master_pegawai_id',
        'status_kepegawaian',
        'id_unit',
        'id_sub_unit',
        'id_struktur_organisasi',
        'begin_date',
        'end_date',
        'is_active',
        'lokasi_kerja',
        'order',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function subUnit()
    {
        return $this->belongsTo(SubUnit::class, 'id_sub_unit');
    }
}
