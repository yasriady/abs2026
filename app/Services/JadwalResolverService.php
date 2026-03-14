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
        $hari = (int) $date->format('N'); // ISO-8601: 1 (Mon) - 7 (Sun)

        // Resolve nik ke pegawaiId
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
                $jadwalPegawai->jam_pulang,
                $jadwalPegawai->batas_in,
                $jadwalPegawai->batas_out,
                $jadwalPegawai->toleransi_telat_menit,
                $jadwalPegawai->toleransi_pulang_cepat_menit,
                $this->hitungPenaltiTidakTap($jadwalPegawai->batas_in, $jadwalPegawai->jam_masuk),
                $this->hitungPenaltiTidakTap($jadwalPegawai->jam_pulang, $jadwalPegawai->batas_out)
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
                    $jadwalSubUnit->jam_pulang,
                    $jadwalSubUnit->batas_in,
                    $jadwalSubUnit->batas_out,
                    $jadwalSubUnit->toleransi_telat_menit,
                    $jadwalSubUnit->toleransi_pulang_cepat_menit,
                    $this->hitungPenaltiTidakTap($jadwalSubUnit->batas_in, $jadwalSubUnit->jam_masuk),
                    $this->hitungPenaltiTidakTap($jadwalSubUnit->jam_pulang, $jadwalSubUnit->batas_out)
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
                    $jadwalUnit->jam_pulang,
                    $jadwalUnit->batas_in,
                    $jadwalUnit->batas_out,
                    $jadwalUnit->toleransi_telat_menit,
                    $jadwalUnit->toleransi_pulang_cepat_menit,
                    $this->hitungPenaltiTidakTap($jadwalUnit->batas_in, $jadwalUnit->jam_masuk),
                    $this->hitungPenaltiTidakTap($jadwalUnit->jam_pulang, $jadwalUnit->batas_out)
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
                $jadwalDinas->jam_pulang,
                $jadwalDinas->batas_in,
                $jadwalDinas->batas_out,
                $jadwalDinas->toleransi_telat_menit,
                $jadwalDinas->toleransi_pulang_cepat_menit,
                $this->hitungPenaltiTidakTap($jadwalDinas->batas_in, $jadwalDinas->jam_masuk),
                $this->hitungPenaltiTidakTap($jadwalDinas->jam_pulang, $jadwalDinas->batas_out)
            );
        }

        return null;
    }

    /**
     * Hitung penalti tidak tap in / tap out dalam menit
     *
     * @param string|null $waktuAwal
     * @param string|null $waktuAkhir
     * @param bool $reverseMode Jika true, hitung waktuAwal - waktuAkhir (untuk penalti out)
     * @return int|null
     */
    protected function hitungPenaltiTidakTap(?string $waktuAwal, ?string $waktuAkhir, bool $reverseMode = false): ?int
    {
        // Jika salah satu waktu tidak tersedia, return null
        if (!$waktuAwal || !$waktuAkhir) {
            return null;
        }

        try {
            // Parse waktu ke Carbon (tanpa tanggal)
            $awal = Carbon::parse($waktuAwal);
            $akhir = Carbon::parse($waktuAkhir);

            // Untuk penalti tidak tap in: batas_in - jam_masuk
            // Untuk penalti tidak tap out: jam_pulang - batas_out
            if ($reverseMode) {
                // Kasus penalti out: jam_pulang - batas_out
                return $awal->diffInMinutes($akhir, false);
            } else {
                // Kasus penalti in: batas_in - jam_masuk
                return $akhir->diffInMinutes($awal, false);
            }
        } catch (\Exception $e) {
            // Jika terjadi error parsing, return null
            return null;
        }
    }

    /**
     * Format hasil resolver
     */
    protected function formatResult(
        string $sumber,
        ?string $jam_masuk,
        ?string $jam_pulang,
        ?string $batas_in,
        ?string $batas_out,
        ?int $toleransi_telat_menit,
        ?int $toleransi_pulang_cepat_menit,
        ?int $penalti_tidak_tap_in,
        ?int $penalti_tidak_tap_out
    ): array {
        return [
            'sumber'                       => $sumber,
            'jam_masuk'                     => $jam_masuk,
            'jam_pulang'                    => $jam_pulang,
            'batas_in'                       => $batas_in,
            'batas_out'                      => $batas_out,
            'toleransi_telat_menit'          => $toleransi_telat_menit,
            'toleransi_pulang_cepat_menit'   => $toleransi_pulang_cepat_menit,
            'penalti_tidak_tap_in'           => $penalti_tidak_tap_in,
            'penalti_tidak_tap_out'          => $penalti_tidak_tap_out,
        ];
    }

    /**
     * Method tambahan untuk mendapatkan penjelasan tentang nilai penalti
     * (opsional, untuk debugging)
     */
    public function debugResolve(string $nik, $date): ?array
    {
        $result = $this->resolve($nik, $date);

        if ($result) {
            $result['debug'] = [
                'penalti_in_formula' => $result['batas_in'] && $result['jam_masuk']
                    ? "{$result['batas_in']} - {$result['jam_masuk']} = {$result['penalti_tidak_tap_in']} menit"
                    : 'tidak dapat dihitung',
                'penalti_out_formula' => $result['jam_pulang'] && $result['batas_out']
                    ? "{$result['jam_pulang']} - {$result['batas_out']} = {$result['penalti_tidak_tap_out']} menit"
                    : 'tidak dapat dihitung',
            ];
        }

        return $result;
    }
}
