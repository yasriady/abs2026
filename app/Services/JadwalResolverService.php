<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\JadwalPegawai;
use App\Models\JadwalSubUnit;
use App\Models\JadwalUnit;
use App\Models\JadwalDinas;
use App\Models\PegawaiHistory;
use App\Models\MasterPegawai;

class JadwalResolverService
{
    /**
     * Resolve jadwal kerja pegawai pada tanggal tertentu
     *
     * @param  string  $nik
     * @param  string|Carbon  $date
     * @return array|null
     */
    public function resolve(string $nik, $date): ?array
    {
        $date = Carbon::parse($date);
        // $hari = $date->dayOfWeekIso(); // 1=Senin ... 7=Minggu
        // $hari = $date->dayOfWeekIso; // 1=Senin ... 7=Minggu
        $hari = (int) $date->format('N'); // ISO-8601: 1 (Mon) - 7 (Sun)

        // Ddy: resolve dulu nik 
        $pegawaiId = MasterPegawai::where('nik', $nik)->value('id');

        if (!$pegawaiId) {
            return null; // NIK tidak terdaftar
        }

        // ===============================
        // 1️⃣ JADWAL KHUSUS PEGAWAI
        // ===============================
        $jadwalPegawai = JadwalPegawai::where('nik', $nik)
            ->where('date', $date->toDateString())
            ->first();

        if ($jadwalPegawai) {
            return $this->formatResult(
                'pegawai',
                $jadwalPegawai->jam_masuk,
                $jadwalPegawai->jam_pulang
            );
        }

        // ===============================
        // 2️⃣ AMBIL HISTORI UNIT PEGAWAI
        // ===============================
        $history = PegawaiHistory::where('master_pegawai_id', $pegawaiId)
            ->where('begin_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderByDesc('begin_date')
            ->first();

        if (!$history) {
            return null; // pegawai belum punya unit pada tanggal tsb
        }

        // ===============================
        // 3️⃣ JADWAL SUB UNIT
        // ===============================
        if ($history->sub_unit_id) {
            $jadwalSubUnit = JadwalSubUnit::where('sub_unit_id', $history->sub_unit_id)
                ->where('hari', $hari)
                ->aktif($date)
                ->orderByDesc('start_date')
                ->first();

            if ($jadwalSubUnit) {
                return $this->formatResult(
                    'sub_unit',
                    $jadwalSubUnit->jam_masuk,
                    $jadwalSubUnit->jam_pulang
                );
            }
        }

        // ===============================
        // 4️⃣ JADWAL UNIT
        // ===============================
        if ($history->unit_id) {
            $jadwalUnit = JadwalUnit::where('unit_id', $history->unit_id)
                ->where('hari', $hari)
                ->aktif($date)
                ->orderByDesc('start_date')
                ->first();

            if ($jadwalUnit) {
                return $this->formatResult(
                    'unit',
                    $jadwalUnit->jam_masuk,
                    $jadwalUnit->jam_pulang
                );
            }
        }

        // ===============================
        // 5️⃣ JADWAL DINAS (FALLBACK)
        // ===============================
        $jadwalDinas = JadwalDinas::where('hari', $hari)
            ->aktif($date)
            ->orderByDesc('start_date')
            ->first();

        if ($jadwalDinas) {
            return $this->formatResult(
                'dinas',
                $jadwalDinas->jam_masuk,
                $jadwalDinas->jam_pulang
            );
        }

        return null;
    }

    /**
     * Format hasil resolver
     */
    protected function formatResult(string $sumber, string $masuk, string $pulang): array
    {
        return [
            'sumber'      => $sumber,   // pegawai | sub_unit | unit | dinas
            'jam_masuk'  => $masuk,
            'jam_pulang' => $pulang,
        ];
    }
}
