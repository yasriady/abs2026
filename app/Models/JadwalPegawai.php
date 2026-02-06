<?php

// app/Models/JadwalPegawai.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPegawai extends Model
{
    protected $table = 'jadwal_pegawais';

    protected $fillable = [
        'nik',
        'date',
        'jam_masuk',
        'jam_pulang',
    ];
}
