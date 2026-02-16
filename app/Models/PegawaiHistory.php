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

    protected $appends = [
        'nik',
        'nip',
        'nama',
        'unit_id',
        'sub_unit_id',
        'struktur_organisasi_id',
    ];

    protected $hidden = [
        'id_unit',
        'id_sub_unit',
        'id_struktur_organisasi',
        'master_pegawai_id',
        'created_at',
        'updated_at',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function subUnit()
    {
        return $this->belongsTo(SubUnit::class, 'id_sub_unit');
    }

    public function masterPegawai()
    {
        return $this->belongsTo(MasterPegawai::class, 'master_pegawai_id');
    }

    // Accessor

    public function getNikAttribute()
    {
        return $this->masterPegawai?->nik;
    }

    public function getNipAttribute()
    {
        return $this->masterPegawai?->nip;
    }

    public function getNamaAttribute()
    {
        return $this->masterPegawai?->nama;
    }

    /**
     * Alias konsisten untuk sub unit
     */
    public function getSubUnitIdAttribute()
    {
        return $this->id_sub_unit;
    }

    /**
     * Alias konsisten untuk unit
     */
    public function getUnitIdAttribute()
    {
        return $this->id_unit;
    }

    /**
     * Alias konsisten untuk struktur organisasi
     */
    public function getStrukturOrganisasiIdAttribute()
    {
        return $this->id_struktur_organisasi;
    }
}
