<?php

// app/Models/AbsensiRaw.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiRaw extends Model
{
    protected $connection = 'devel_db';
    protected $table = 'DB_ATT_tbl_attendance2';

    public $timestamps = false;

    // =========================
    // RELATION → MASTER PEGAWAI
    // =========================
    public function pegawai()
    {
        return $this->belongsTo(
            MasterPegawai::class,
            'nik', // foreign key di tabel raw
            'nik'  // primary key di master_pegawai
        );
    }

    // =========================
    // ACCESSOR NAMA
    // =========================
    public function getNamaAttribute()
    {
        return $this->pegawai?->nama ?? '-';
    }
}
