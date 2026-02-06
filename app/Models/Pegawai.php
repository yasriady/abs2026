<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    // TIDAK DIPAKAI
    protected $fillable = [
        'nip',
        'nik',
        'nama',
        'status_kepegawaian',
        'id_unit',
        'id_sub_unit',
        'id_struktur_organisasi',
        'begin_date',
        'end_date',
        'x_now_x',
        'lokasi_kerja',
        'order',
    ];

    protected $casts = [
        'begin_date' => 'date',
        'end_date' => 'date',
        'x_now_x' => 'boolean',
    ];
}
