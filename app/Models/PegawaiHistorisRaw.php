<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiHistorisRaw extends Model
{
    protected $table = 'pegawai_histories';

    protected $fillable = [
        'begin_date',
        'end_date',
    ];

    protected $casts = [
        'begin_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * Relasi ke master pegawai
     */
    public function masterPegawai()
    {
        return $this->belongsTo(MasterPegawai::class, 'master_pegawai_id');
    }
}
