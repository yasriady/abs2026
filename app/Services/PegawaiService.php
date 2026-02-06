<?php

namespace App\Services;

use App\Models\PegawaiHistory;

class PegawaiService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function createHistory($pegawai, $data)
    {
        PegawaiHistory::where('master_pegawai_id', $pegawai->id)
            ->update(['is_active' => false]);

        return PegawaiHistory::create(array_merge(
            $data,
            [
                'master_pegawai_id' => $pegawai->id,
                'is_active' => true,
            ]
        ));
    }
}
