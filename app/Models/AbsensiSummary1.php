<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\JadwalResolverService;
use App\Models\DailyNote;
use App\Models\TappingNote;

class AbsensiSummary1 extends Model
{
    protected $table = 'absensi_summaries';

    protected $fillable = [
        'nik',
        'date',
        'time_in',
        'time_out',

        'filename_in',
        'device_id_in',
        'lat_in',
        'long_in',
        'machine_id_in',

        'filename_out',
        'device_id_out',
        'lat_out',
        'long_out',
        'machine_id_out',

        'is_final',
    ];

    protected $casts = [
        'date'     => 'date',
        'is_final' => 'boolean',
    ];

    // protected $appends = [
    //     'device_desc_in',
    //     'device_desc_out',
    //     'active_pegawai_history',
    //     'valid_device_in',
    //     'valid_device_out',
    //     'status_validasi',
    //     'status_validasi_label',
    //     'status_validasi_absensi',
    //     'status_validasi_absensi_in',
    //     'status_validasi_absensi_out',
    //     'jadwal_kerja',
    //     'status_masuk',
    //     'status_pulang',
    //     'status_masuk_label',
    //     'daily_note',
    //     'status_absen',
    //     'time_in_final',
    //     'time_out_final',
    //     'tapmasuk_note',
    //     'tapkeluar_note',
    //     'status_masuk_final',
    //     'status_pulang_final',
    //     'status_hari_final',
    // ];

    protected $appends = [
        'status_hari_final',
        'status_masuk_final',
        'status_pulang_final',
        'time_in_final',
        'time_out_final',
    ];

    protected ?DailyNote $cachedDailyNote = null;
    protected ?TappingNote $cachedTappingNoteIn = null;
    protected ?TappingNote $cachedTappingNoteOut = null;


    /* =====================================================
     * RELATIONS
     * ===================================================== */

    public function deviceIn()
    {
        return $this->belongsTo(Device::class, 'device_id_in', 'device_id');
    }

    public function deviceOut()
    {
        return $this->belongsTo(Device::class, 'device_id_out', 'device_id');
    }

    public function masterPegawai()
    {
        return $this->belongsTo(MasterPegawai::class, 'nik', 'nik');
    }

    /* =====================================================
     * ACCESSORS
     * ===================================================== */

    public function getDeviceDescInAttribute()
    {
        // Jika override admin → device tidak relevan
        if ($this->resolveTappingNote('in')) {
            return 'Administratif';
        }

        if (!$this->device_id_in) {
            return null;
        }

        return \App\Models\Device::where('device_id', $this->device_id_in)
            ->value('desc'); // sesuaikan kolom
    }

    public function getDeviceDescOutAttribute()
    {
        if ($this->resolveTappingNote('out')) {
            return 'Administratif';
        }

        if (!$this->device_id_out) {
            return null;
        }

        return \App\Models\Device::where('device_id', $this->device_id_out)
            ->value('desc');
    }

    public function getActivePegawaiHistoryAttribute()
    {
        $master = $this->masterPegawai;
        if (!$master || !$this->date) {
            return null;
        }

        return PegawaiHistory::where('master_pegawai_id', $master->id)
            ->where('begin_date', '<=', $this->date)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $this->date);
            })
            ->latest('begin_date')
            ->first();
    }

    public function getValidDeviceInAttribute()
    {
        if (!$this->time_in || !$this->active_pegawai_history) {
            return false;
        }

        $device = Device::where('device_id', $this->device_id_in)->first();
        if (!$device) {
            return false;
        }

        return $this->isUnitValidForDevice(
            $this->active_pegawai_history->id_unit,
            $device->unit_id
        );
    }

    public function getValidDeviceOutAttribute()
    {
        if (!$this->time_out || !$this->active_pegawai_history) {
            return false;
        }

        $device = Device::where('device_id', $this->device_id_out)->first();
        if (!$device) {
            return false;
        }

        return $this->isUnitValidForDevice(
            $this->active_pegawai_history->id_unit,
            $device->unit_id
        );
    }

    protected function isUnitValidForDevice($pegawaiUnitId, $deviceUnitId): bool
    {
        if (empty($pegawaiUnitId) || empty($deviceUnitId)) {
            return false;
        }

        if ($deviceUnitId === '-1') {
            return true;
        }

        return in_array(
            (string) $pegawaiUnitId,
            array_map('trim', explode(',', $deviceUnitId))
        );
    }

    /* =====================================================
     * STATUS VALIDASI INTERNAL
     * ===================================================== */

    public function getStatusValidasiAttribute()
    {
        if (!$this->active_pegawai_history) {
            return 'NO_PEGAWAI_HISTORY';
        }

        $hasIn  = !is_null($this->time_in);
        $hasOut = !is_null($this->time_out);

        if (!$hasIn && !$hasOut) return 'NO_ABSENSI';
        if ($hasIn && !$hasOut) return $this->valid_device_in ? 'IN_ONLY' : 'INVALID_DEVICE_IN';
        if (!$hasIn && $hasOut) return $this->valid_device_out ? 'OUT_ONLY' : 'INVALID_DEVICE_OUT';

        if ($this->valid_device_in && $this->valid_device_out) return 'VALID';
        if (!$this->valid_device_in && !$this->valid_device_out) return 'INVALID_DEVICE_BOTH';

        return !$this->valid_device_in
            ? 'INVALID_DEVICE_IN'
            : 'INVALID_DEVICE_OUT';
    }

    public function getStatusValidasiLabelAttribute()
    {
        return match ($this->status_validasi) {
            'VALID'               => 'Absensi valid',
            'IN_ONLY'             => 'Hanya absen masuk',
            'OUT_ONLY'            => 'Hanya absen pulang',
            'INVALID_DEVICE_IN'   => 'Mesin masuk tidak valid',
            'INVALID_DEVICE_OUT'  => 'Mesin pulang tidak valid',
            'INVALID_DEVICE_BOTH' => 'Mesin masuk & pulang tidak valid',
            'NO_PEGAWAI_HISTORY'  => 'Tidak ada riwayat pegawai',
            default               => 'Status tidak diketahui',
        };
    }

    /* =====================================================
     * STATUS ABSENSI FINAL (OVERRIDE DAILY NOTE)
     * ===================================================== */

    public function getStatusValidasiAbsensiInAttribute()
    {
        // 🔥 PRIORITAS 1: TappingNote IN
        $tappingNote = $this->resolveTappingNote('in');
        if ($tappingNote) {
            return $tappingNote->status;
        }

        // fallback lama
        if (!$this->time_in || !$this->active_pegawai_history) {
            return 'ALPA';
        }

        return $this->valid_device_in ? 'HADIR' : 'ALPA';
    }

    public function getStatusValidasiAbsensiOutAttribute()
    {
        // 🔥 PRIORITAS 1: TappingNote OUT
        $tappingNote = $this->resolveTappingNote('out');
        if ($tappingNote) {
            return $tappingNote->status;
        }

        // fallback lama
        if (!$this->time_out || !$this->active_pegawai_history) {
            return 'ALPA';
        }

        return $this->valid_device_out ? 'HADIR' : 'ALPA';
    }

    public function getStatusValidasiAbsensiAttribute()
    {
        // 🔥 override dari DailyNote (tbl_absent)
        $dailyNote = $this->resolveDailyNote();
        if ($dailyNote) {
            return $dailyNote->status;
        }

        // fallback absensi normal
        if (
            $this->status_validasi_absensi_in === 'HADIR'
            || $this->status_validasi_absensi_out === 'HADIR'
        ) {
            return 'HADIR';
        }

        return 'ALPA';
    }

    /* =====================================================
     * DAILY NOTE HELPER (BUKAN RELATION!)
     * ===================================================== */

    public function resolveDailyNote()
    {
        if ($this->cachedDailyNote !== null) {
            return $this->cachedDailyNote;
        }

        if (!$this->date) {
            return null;
        }

        return $this->cachedDailyNote = DailyNote::where('nik', $this->nik)
            ->whereDate('date', $this->date)
            ->first();
    }

    /* =====================================================
     * JADWAL & KETERANGAN
     * ===================================================== */

    public function getJadwalKerjaAttribute()
    {
        if (!$this->date) return null;

        return app(JadwalResolverService::class)
            ->resolve($this->nik, $this->date);
    }

    public function getStatusMasukAttribute()
    {
        // 🔥 PRIORITAS 1: Override oleh TappingNote IN
        if ($this->resolveTappingNote('in')) {
            return 'Adm';
        }

        // Tidak ada absen masuk
        if (!$this->time_in) {
            return null;
        }

        // Absen masuk tapi device tidak valid
        if (!$this->valid_device_in) {
            return 'X';
        }

        // Tidak ada jadwal → tidak bisa dinilai
        if (!$this->jadwal_kerja) {
            return null;
        }

        return $this->time_in <= $this->jadwal_kerja['jam_masuk']
            ? 'TEPAT_WAKTU'
            : 'TELAT';
    }

    public function getStatusPulangAttribute()
    {
        // 🔥 PRIORITAS 1: Override oleh TappingNote OUT
        if ($this->resolveTappingNote('out')) {
            return 'Adm';
        }

        // Tidak ada absen pulang
        if (!$this->time_out) {
            return null;
        }

        // Absen pulang tapi device tidak valid
        if (!$this->valid_device_out) {
            return 'X';
        }

        // Tidak ada jadwal → tidak bisa dinilai
        if (!$this->jadwal_kerja) {
            return null;
        }

        return $this->time_out >= $this->jadwal_kerja['jam_pulang']
            ? 'SESUAI'
            : 'PULANG CEPAT';
    }

    public function getStatusMasukLabelAttribute()
    {
        return match ($this->status_masuk) {
            'TEPAT_WAKTU' => 'Tepat waktu',
            'TELAT'           => 'Terlambat',
            'X'           => 'Mesin tidak valid',
            default       => null,
        };
    }

    public function getDailyNoteAttribute()
    {
        return $this->resolveDailyNote();
    }

    public function getStatusAbsenAttribute()
    {
        /**
         * 1️⃣ PRIORITAS TERTINGGI: DAILY NOTE
         */
        $dailyNote = $this->resolveDailyNote();
        if ($dailyNote) {
            return $dailyNote->status; // ✅ BENAR
        }

        /**
         * 2️⃣ TIDAK ADA RIWAYAT PEGAWAI
         */
        if (!$this->active_pegawai_history) {
            return 'TIDAK_AKTIF';
        }

        /**
         * 3️⃣ ABSENSI AKTUAL
         */
        $hasIn  = !is_null($this->time_in);
        $hasOut = !is_null($this->time_out);

        if (!$hasIn && !$hasOut) {
            return 'ALPA';
        }

        if ($hasIn xor $hasOut) {
            return ($this->valid_device_in || $this->valid_device_out)
                ? 'HADIR_SEBAGIAN'
                : 'HADIR_TIDAK_VALID';
        }

        if ($this->valid_device_in && $this->valid_device_out) {
            return 'HADIR';
        }

        return 'HADIR_TIDAK_VALID';
    }

    protected function resolveTappingNote(string $type): ?TappingNote
    {
        if (!$this->date) {
            return null;
        }

        if ($type === 'in' && $this->cachedTappingNoteIn !== null) {
            return $this->cachedTappingNoteIn;
        }

        if ($type === 'out' && $this->cachedTappingNoteOut !== null) {
            return $this->cachedTappingNoteOut;
        }

        $note = TappingNote::where('nik', $this->nik)
            ->whereDate('date', $this->date)
            ->where('hour', $type) // 'in' / 'out'
            ->first();

        if ($type === 'in') {
            return $this->cachedTappingNoteIn = $note;
        }

        return $this->cachedTappingNoteOut = $note;
    }

    public function getTimeInFinalAttribute()
    {
        $tappingNote = $this->resolveTappingNote('in');

        if ($tappingNote && $tappingNote->tm) {
            return $tappingNote->tm;
        }

        return $this->time_in;
    }

    public function getTimeOutFinalAttribute()
    {
        $tappingNote = $this->resolveTappingNote('out');

        if ($tappingNote && $tappingNote->tm) {
            return $tappingNote->tm;
        }

        return $this->time_out;
    }

    public function v_getTimeInFinalAttribute()
    {
        $tappingNote = $this->resolveTappingNote('in');

        // 🔥 Jika ada override admin
        if ($tappingNote) {

            // 1️⃣ pakai jam admin jika ada
            if (!empty($tappingNote->tm)) {
                return $tappingNote->tm;
            }

            // 2️⃣ fallback ke jam mesin jika ada
            if (!empty($this->time_in)) {
                return $this->time_in;
            }

            return null;
        }

        // normal (tanpa override)
        return $this->time_in;
    }

    public function v_getTimeOutFinalAttribute()
    {
        $tappingNote = $this->resolveTappingNote('out');

        if ($tappingNote) {
            if (!empty($tappingNote->tm)) {
                return $tappingNote->tm;
            }

            if (!empty($this->time_out)) {
                return $this->time_out;
            }

            return null;
        }

        return $this->time_out;
    }


    public function getTapmasukNoteAttribute()
    {
        return $this->resolveTappingNote('in')?->notes;
    }

    public function getTapkeluarNoteAttribute()
    {
        return $this->resolveTappingNote('out')?->notes;
    }

    // https://chatgpt.com/share/69889bed-65cc-8011-8822-953334fdf144

    public function getStatusMasukFinalAttribute()
    {
        // 🥇 PRIORITAS 1: DailyNote (harian)
        $dailyNote = $this->resolveDailyNote();
        if ($dailyNote) {
            return $dailyNote->status; // CT, CBS, IB, CM, CKAP, dll
        }

        // 🥈 PRIORITAS 2: TappingNote IN (admin per jam)
        $tappingNote = $this->resolveTappingNote('in');
        if ($tappingNote) {
            return $tappingNote->status;
        }

        // 🥉 PRIORITAS 3: Pegawai tidak aktif
        if (!$this->active_pegawai_history) {
            return 'ALPA';
        }

        // 🥉 PRIORITAS 4: Absensi mesin
        if (!$this->time_in) {
            return 'ALPA';
        }

        return $this->valid_device_in
            ? 'HADIR'
            : 'ALPA';
    }

    public function getStatusPulangFinalAttribute()
    {
        // 🥇 PRIORITAS 1: DailyNote (harian)
        $dailyNote = $this->resolveDailyNote();
        if ($dailyNote) {
            return $dailyNote->status;
        }

        // 🥈 PRIORITAS 2: TappingNote OUT (admin per jam)
        $tappingNote = $this->resolveTappingNote('out');
        if ($tappingNote) {
            return $tappingNote->status;
        }

        // 🥉 PRIORITAS 3: Pegawai tidak aktif
        if (!$this->active_pegawai_history) {
            return 'ALPA';
        }

        // 🥉 PRIORITAS 4: Absensi mesin
        if (!$this->time_out) {
            return 'ALPA';
        }

        return $this->valid_device_out
            ? 'HADIR'
            : 'ALPA';
    }

    public function getStatusHariFinalAttribute()
    {
        // 🥇 PRIORITAS 1: DailyNote (hari penuh)
        $dailyNote = $this->resolveDailyNote();
        if ($dailyNote) {
            return $dailyNote->status;
        }

        // 🥈 PRIORITAS 2: Pegawai tidak aktif
        if (!$this->active_pegawai_history) {
            return 'ALPA';
        }

        // 🥉 PRIORITAS 3: Gabungan masuk / pulang
        if (
            $this->status_masuk_final !== 'ALPA'
            || $this->status_pulang_final !== 'ALPA'
        ) {
            return 'HADIR';
        }

        return 'ALPA';
    }
}
