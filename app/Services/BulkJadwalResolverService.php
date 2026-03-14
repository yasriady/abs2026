<?php

namespace App\Services;

use App\Models\JadwalPegawai;
use App\Models\JadwalSubUnit;
use App\Models\JadwalUnit;
use App\Models\JadwalDinas;
use Carbon\Carbon;

class BulkJadwalResolverService
{
    protected $pegawaiMap = [];
    protected $subMap = [];
    protected $unitMap = [];
    protected $dinasMap = [];

    public function preload($start, $end)
    {
        // ===== JADWAL PEGAWAI =====
        // Untuk jadwal pegawai, filter berdasarkan range date
        $jadwalPegawai = JadwalPegawai::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        \Log::info('Jadwal Pegawai ditemukan: ' . $jadwalPegawai->count());

        foreach ($jadwalPegawai as $j) {
            // Parse tanggal untuk mendapatkan hari (1-7, Senin=1, Minggu=7)
            $hari = Carbon::parse($j->date)->dayOfWeekIso;

            // Gunakan NIK sebagai key
            if (!isset($this->pegawaiMap[$j->nik])) {
                $this->pegawaiMap[$j->nik] = [];
            }

            // Simpan jadwal per hari
            $this->pegawaiMap[$j->nik][$hari] = $j;

            \Log::info("Map Pegawai: NIK {$j->nik}, Hari {$hari}, Masuk {$j->jam_masuk}");
        }

        // ===== SUB UNIT =====
        $jadwalSubUnit = JadwalSubUnit::where('start_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start);
            })
            ->get();

        \Log::info('Jadwal Sub Unit ditemukan: ' . $jadwalSubUnit->count());

        foreach ($jadwalSubUnit as $j) {
            if (!isset($this->subMap[$j->sub_unit_id])) {
                $this->subMap[$j->sub_unit_id] = [];
            }
            $this->subMap[$j->sub_unit_id][$j->hari] = $j;
        }

        // ===== UNIT =====
        $jadwalUnit = JadwalUnit::where('start_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start);
            })
            ->get();

        \Log::info('Jadwal Unit ditemukan: ' . $jadwalUnit->count());

        foreach ($jadwalUnit as $j) {
            if (!isset($this->unitMap[$j->unit_id])) {
                $this->unitMap[$j->unit_id] = [];
            }
            $this->unitMap[$j->unit_id][$j->hari] = $j;
        }

        // ===== DINAS GLOBAL =====
        $jadwalDinas = JadwalDinas::where('start_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start);
            })
            ->get();

        \Log::info('Jadwal Dinas ditemukan: ' . $jadwalDinas->count());

        foreach ($jadwalDinas as $j) {
            $this->dinasMap[$j->hari] = $j;
        }
    }

    public function resolve($pegawai, $hari)
    {
        \Log::info("Resolve: NIK {$pegawai->nik}, Hari {$hari}");

        // PRIORITAS 1: Jadwal Pegawai (berdasarkan NIK)
        if (isset($this->pegawaiMap[$pegawai->nik]) && isset($this->pegawaiMap[$pegawai->nik][$hari])) {
            \Log::info("Ditemukan jadwal pegawai untuk NIK {$pegawai->nik} hari {$hari}");
            return $this->format($this->pegawaiMap[$pegawai->nik][$hari], 'pegawai');
        }

        // PRIORITAS 2: Jadwal Sub Unit
        if (isset($this->subMap[$pegawai->id_sub_unit]) && isset($this->subMap[$pegawai->id_sub_unit][$hari])) {
            \Log::info("Ditemukan jadwal sub unit untuk ID {$pegawai->id_sub_unit} hari {$hari}");
            return $this->format($this->subMap[$pegawai->id_sub_unit][$hari], 'sub_unit');
        }

        // PRIORITAS 3: Jadwal Unit
        if (isset($this->unitMap[$pegawai->id_unit]) && isset($this->unitMap[$pegawai->id_unit][$hari])) {
            \Log::info("Ditemukan jadwal unit untuk ID {$pegawai->id_unit} hari {$hari}");
            return $this->format($this->unitMap[$pegawai->id_unit][$hari], 'unit');
        }

        // PRIORITAS 4: Jadwal Dinas Global
        if (isset($this->dinasMap[$hari])) {
            \Log::info("Ditemukan jadwal dinas untuk hari {$hari}");
            return $this->format($this->dinasMap[$hari], 'dinas');
        }

        \Log::info("Tidak ditemukan jadwal untuk NIK {$pegawai->nik} hari {$hari}");
        return null;
    }

    protected function format($j, $src)
    {
        return [
            'jam_masuk' => $j->jam_masuk,
            'jam_pulang' => $j->jam_pulang,
            'sumber' => $src
        ];
    }
}
