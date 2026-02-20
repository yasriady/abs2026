<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\AbsensiSummary;
use App\Models\MasterPegawai;
use App\Models\PegawaiHistory;

class RekapBulananService
{
    protected array $statusMap = [
        'IZIN'  => ['symbol' => 'I', 'stat' => 'izin'],
        'CUTI'  => ['symbol' => 'C', 'stat' => 'cuti'],
        'SAKIT' => ['symbol' => 'S', 'stat' => 'sakit'],
    ];

    // Jumlah cuti/status khusus per-key dari status_hari_final.
    protected array $statusHariFinalCutiKeys = [
        'DL',
        'CT',
        'CBS',
        'CS',
        'CM',
        'CKAP',
        'CB',
        'CLTN',
        'TB',
    ];

    public function generate($unitId, $subUnitId, $start, $end, $statusPegawai = null, $nik = null)
    {
        $start = Carbon::parse($start)->startOfDay();
        $end   = Carbon::parse($end)->endOfDay();

        $histories = $this->getHistories($unitId, $subUnitId, $start, $end, $statusPegawai, $nik);

        if ($histories->isEmpty()) {
            return collect();
        }

        $pegawaiIds = $histories->pluck('master_pegawai_id')->unique()->values();
        $pegawais   = $this->getPegawai($pegawaiIds);
        $absenMap   = $this->mapAbsensi($pegawais, $start, $end);
        $historyMap = $this->buildTimeline($histories, $start, $end);
        $dates      = $this->generateDates($start, $end);
        $hariLiburSet = $this->getHariLiburSet($start, $end);

        // return $this->buildMatrix($pegawaiIds, $pegawais, $absenMap, $historyMap, $dates);
        return $this->buildMatrix($pegawaiIds, $pegawais, $absenMap, $historyMap, $dates, $hariLiburSet);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Histories
    |--------------------------------------------------------------------------
    */
    protected function getHistories($unitId, $subUnitId, $start, $end, $statusPegawai = null, $nik = null)
    {
        return PegawaiHistory::query()
            ->when(
                $nik,
                fn($q) => $q->whereHas('masterPegawai', fn($pq) => $pq->where('nik', $nik)),
                fn($q) => $q->where('id_unit', $unitId)
            )
            ->when($nik, fn($q) => $q, fn($q) => $q->when($subUnitId, fn($sq) => $sq->where('id_sub_unit', $subUnitId)))
            ->when(
                !empty($statusPegawai),
                fn($q) => $q->whereRaw(
                    'LOWER(TRIM(status_kepegawaian)) = ?',
                    [strtolower(trim($statusPegawai))]
                )
            )
            ->whereDate('begin_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $start);
            })
            ->orderBy('master_pegawai_id')
            ->orderBy('begin_date')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Pegawai
    |--------------------------------------------------------------------------
    */
    protected function getPegawai($pegawaiIds)
    {
        return MasterPegawai::whereIn('id', $pegawaiIds)
            ->get()
            ->keyBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Absensi Map
    |--------------------------------------------------------------------------
    */
    protected function mapAbsensi($pegawais, $start, $end)
    {
        $niks = $pegawais->pluck('nik')->filter()->values();

        $rows = AbsensiSummary::whereBetween('date', [$start, $end])
            ->whereIn('nik', $niks)
            ->get();

        $map = [];

        foreach ($rows as $r) {
            $map[$r->nik][$r->date] = $r;
        }

        return $map;
    }

    /*
    |--------------------------------------------------------------------------
    | Timeline Pegawai
    |--------------------------------------------------------------------------
    */
    protected function buildTimeline($histories, $start, $end)
    {
        $map = [];

        foreach ($histories as $h) {

            $dStart = Carbon::parse($h->begin_date);
            $dEnd   = $h->end_date ? Carbon::parse($h->end_date) : $end;

            $periodStart = $dStart->greaterThan($start) ? $dStart : $start;
            $periodEnd   = $dEnd->lessThan($end) ? $dEnd : $end;

            for ($d = $periodStart->copy(); $d <= $periodEnd; $d->addDay()) {
                $map[$h->master_pegawai_id][$d->toDateString()] = true;
            }
        }

        return $map;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Dates
    |--------------------------------------------------------------------------
    */
    protected function generateDates($start, $end)
    {
        $dates = [];

        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        return $dates;
    }

    /*
    |--------------------------------------------------------------------------
    | Build Matrix
    |--------------------------------------------------------------------------
    */
    protected function buildMatrix($pegawaiIds, $pegawais, $absenMap, $historyMap, $dates, $hariLiburSet)
    {
        $result = [];
        $totalHariPeriode = count($dates);
        $hariKerjaPeriode = $this->countHariKerjaPeriode($dates, $hariLiburSet);

        foreach ($pegawaiIds as $pid) {

            $pegawai = $pegawais[$pid] ?? null;
            if (!$pegawai) continue;

            $row = [
                'pegawai_id' => $pid,
                'nama' => $pegawai->nama,
                'nik' => $pegawai->nik,
                'dates' => [],
                'stats' => $this->emptyStats()
            ];

            $row['stats']['total_hari'] = $totalHariPeriode;
            $row['stats']['hari_kerja'] = $hariKerjaPeriode;

            foreach ($dates as $tgl) {

                $isActive = ($historyMap[$pid][$tgl] ?? false);
                $day = \Carbon\Carbon::parse($tgl)->dayOfWeek;
                $isWeekend = ($day == 0 || $day == 6);
                $isHoliday = isset($hariLiburSet[$tgl]);

                if (!($historyMap[$pid][$tgl] ?? false)) {
                    $row['dates'][$tgl] = [
                        'symbol' => '-',
                        'raw' => null,
                        'color_in' => 'bg-gray',
                        'color_out' => 'bg-gray'
                    ];

                    continue;
                }

                $absen = $absenMap[$pegawai->nik][$tgl] ?? null;

                if (!$absen) {
                    $row['dates'][$tgl] = [
                        'symbol' => 'A',
                        'raw' => null,
                        'color_in' => 'bg-gray',
                        'color_out' => 'bg-gray'
                    ];

                    if (!$isWeekend && !$isHoliday) {
                        $row['stats']['total_alpa']++;
                    }
                    continue;
                }

                $this->applyStatus($row, $tgl, $absen, $isWeekend, $isHoliday);
            }

            // if ($pegawai->nik == '1405121305970003')
            //     dd($row);

            $result[] = $row;
        }

        // dd(collect($result)->sortBy('nama')->values());

        return collect($result)->sortBy('nama')->values();
    }

    protected function countHariKerjaPeriode(array $dates, $hariLiburSet): int
    {
        $count = 0;

        foreach ($dates as $tgl) {
            $day = \Carbon\Carbon::parse($tgl)->dayOfWeek;
            $isWeekend = ($day == 0 || $day == 6);
            $isHoliday = isset($hariLiburSet[$tgl]);

            if (!$isWeekend && !$isHoliday) {
                $count++;
            }
        }

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty Stats Template
    |--------------------------------------------------------------------------
    */
    protected function emptyStats()
    {
        return [
            'hadir' => 0,
            'telat' => 0,
            'total_alpa' => 0,
            'izin' => 0,
            'cuti' => 0,
            'sakit' => 0,
            'DL' => 0,
            'CT' => 0,
            'CBS' => 0,
            'CS' => 0,
            'CM' => 0,
            'CKAP' => 0,
            'CB' => 0,
            'CLTN' => 0,
            'TB' => 0,
            'total_menit' => 0,
            
            // NEW
            'menit_telat' => 0,
            'total_hari' => 0,
            'hari_kerja' => 0,
        ];
    }

    protected function getHariLiburSet($start, $end)
    {
        return \App\Models\HariLiburNasional::whereBetween('date', [$start, $end])
            ->pluck('date')
            ->flip(); // supaya jadi set lookup cepat
    }

    protected function rawAbsensi($absen)
    {
        if (!$absen) return null;

        $statusHari = strtoupper(trim((string) ($absen->status_hari_final ?? '')));
        $attrInNorm = strtolower(trim((string) ($absen->attribute_in ?? '')));
        $attrOutNorm = strtolower(trim((string) ($absen->attribute_out ?? '')));
        $hasAdminAttr = in_array($attrInNorm, ['/adm', 'adm'], true)
            || in_array($attrOutNorm, ['/adm', 'adm'], true);
        $showStatusAsTime = $hasAdminAttr && !in_array($statusHari, ['HADIR', 'ALPA'], true);

        /*
    | FORMAT TIME IN
    */
        $inFmt = null;

        if ($showStatusAsTime) {
            $inFmt = $statusHari ?: null;
        } elseif ($absen->time_in_final) {

            $jam = substr($absen->time_in_final, 0, 5);

            if ($absen->attribute_in) {
                $jam .= '' . $absen->attribute_in;
            }

            $inFmt = $jam;
        }

        /*
    | FORMAT TIME OUT
    */
        $outFmt = null;

        if ($showStatusAsTime) {
            $outFmt = $statusHari ?: null;
        } elseif ($absen->time_out_final) {

            $jam = substr($absen->time_out_final, 0, 5);

            if ($absen->attribute_out) {
                $jam .= '' . $absen->attribute_out;
            }

            $outFmt = $jam;
        }

        return [
            'time_in_final'  => $absen->time_in_final,
            'time_out_final' => $absen->time_out_final,

            // formatted
            'time_in_fmt'  => $inFmt,
            'time_out_fmt' => $outFmt,

            'attribute_in' => $absen->attribute_in,
            'attribute_out' => $absen->attribute_out,

            'status_masuk_final'  => $absen->status_masuk_final,
            'status_pulang_final' => $absen->status_pulang_final,
            'status_hari_final'   => $absen->status_hari_final,
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Apply Status Logic
    |--------------------------------------------------------------------------
    */
    protected function applyStatus(&$row, $tgl, $absen, bool $isWeekend = false, bool $isHoliday = false)
    {
        $status = strtoupper(trim((string) ($absen->status_hari_final ?? 'A')));
        $symbol = $status;
        $statusMasuk = strtoupper((string) ($absen->status_masuk_final ?? ''));
        $statusPulang = strtoupper((string) ($absen->status_pulang_final ?? ''));
        $hasTapIn = !empty($absen->time_in_final);
        $hasTapOut = !empty($absen->time_out_final);
        $isTotalAlpaDay = !$hasTapIn && !$hasTapOut;

        if ($isTotalAlpaDay && !$isWeekend && !$isHoliday) {
            $row['stats']['total_alpa']++;
        }

        // MT (Menit Telat+PC): akumulasi late+early, kecuali hari yang sudah masuk TotalAlpa.
        if (!$isTotalAlpaDay && !$isWeekend && !$isHoliday) {
            $menitTelatMasuk = max(0, (int) ($absen->late_minutes ?? 0));
            $menitPulangCepat = max(0, (int) ($absen->early_minutes ?? 0));
            $totalMenitPelanggaran = $menitTelatMasuk + $menitPulangCepat;

            $row['stats']['total_menit'] += $totalMenitPelanggaran;
            $row['stats']['menit_telat'] += $totalMenitPelanggaran;
        }

        /*
    |--------------------------------------------------------------------------
    | HADIR
    |--------------------------------------------------------------------------
    */
        if ($status === 'HADIR') {

            $symbol = $absen->time_in_final
                ? substr($absen->time_in_final, 0, 5)
                : 'H';

            $isTelat = str_contains($statusMasuk, '/T')
                || $statusMasuk === 'TELAT'
                || $statusMasuk === 'TERLAMBAT';

            if ($isTelat) {
                $symbol .= 'T';
                $row['stats']['telat']++;
            }

            $row['stats']['hadir']++;
        }

        /*
    |--------------------------------------------------------------------------
    | ALPA
    |--------------------------------------------------------------------------
    */
        elseif (in_array($status, ['A', 'ALPA', 'ALPHA'], true)) {
            $symbol = 'A';
        }

        /*
    |--------------------------------------------------------------------------
    | JUMLAH CUTI/STATUS KHUSUS BERDASARKAN status_hari_final
    |--------------------------------------------------------------------------
    */
        elseif (in_array($status, $this->statusHariFinalCutiKeys, true)) {
            $symbol = $status;
            $row['stats'][$status]++;
        }

        /*
    |--------------------------------------------------------------------------
    | STATUS MAPPING (IZIN, CUTI, SAKIT)
    |--------------------------------------------------------------------------
    */ elseif (isset($this->statusMap[$status])) {

            $map = $this->statusMap[$status];
            $symbol = $map['symbol'];

            $row['stats'][$map['stat']]++;
        }

        /*
    |--------------------------------------------------------------------------
    | DEFAULT UNKNOWN STATUS
    |--------------------------------------------------------------------------
    */ else {
            $symbol = $status;
        }

        /*
    |--------------------------------------------------------------------------
    | RAW DATA
    |--------------------------------------------------------------------------
    */
        $raw = $this->rawAbsensi($absen);
        $attrInNorm = strtolower(trim((string) ($absen->attribute_in ?? '')));
        $attrOutNorm = strtolower(trim((string) ($absen->attribute_out ?? '')));
        $hasAdminAttr = in_array($attrInNorm, ['/adm', 'adm'], true)
            || in_array($attrOutNorm, ['/adm', 'adm'], true);
        $forceWhiteCell = $hasAdminAttr && !in_array($status, ['HADIR', 'ALPA'], true);

        /*
    |--------------------------------------------------------------------------
    | COLOR LOGIC
    |--------------------------------------------------------------------------
    | aturan:
    | - tidak ada in & out → merah semua
    | - salah satu kosong → yang kosong kuning
    | - lengkap → putih semua
    |--------------------------------------------------------------------------
    */

        $in  = $raw['time_in_final'] ?? null;
        $out = $raw['time_out_final'] ?? null;

        if ($forceWhiteCell) {
            $colorIn  = 'bg-white';
            $colorOut = 'bg-white';
        } elseif (!$in && !$out) {

            $colorIn  = 'bg-red';
            $colorOut = 'bg-red';
        } elseif (!$in || !$out) {

            $colorIn  = $in  ? 'bg-white' : 'bg-yellow';
            $colorOut = $out ? 'bg-white' : 'bg-yellow';
        } else {

            $colorIn  = 'bg-white';
            $colorOut = 'bg-white';
        }

        /*
    |--------------------------------------------------------------------------
    | FINAL CELL DATA
    |--------------------------------------------------------------------------
    */
        $row['dates'][$tgl] = [
            'symbol' => $symbol,
            'raw' => $raw,
            'color_in' => $colorIn,
            'color_out' => $colorOut
        ];
    }
}
