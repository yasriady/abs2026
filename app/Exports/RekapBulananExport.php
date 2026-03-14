<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromView;
use App\Services\RekapBulananService;
use App\Models\Unit;
use App\Models\StatusPegawai;
use App\Models\MasterPegawai;
use App\Models\HariLiburNasional;

class RekapBulananExport implements FromView
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC ENTRY POINT
    |--------------------------------------------------------------------------
    */
    public static function download(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'integer'],
            'sub_unit_id' => ['nullable', 'integer'],
            'bulan' => ['required', 'date_format:Y-m'],
            'status_pegawai' => ['nullable', 'string'],
            'nik' => ['nullable', 'string', 'max:30'],
        ]);

        $nik = isset($validated['nik']) && trim($validated['nik']) !== ''
            ? trim($validated['nik'])
            : null;

        if (!$nik && empty($validated['unit_id'])) {
            abort(422, 'Unit wajib diisi jika parameter nik tidak diberikan.');
        }

        $unitId = !empty($validated['unit_id']) ? (int)$validated['unit_id'] : null;
        $subUnitId = !empty($validated['sub_unit_id']) ? (int)$validated['sub_unit_id'] : null;

        $statusPegawai = isset($validated['status_pegawai']) && trim($validated['status_pegawai']) !== ''
            ? trim($validated['status_pegawai'])
            : null;

        $bulan = Carbon::createFromFormat('Y-m', $validated['bulan']);
        $dt1 = $bulan->copy()->startOfMonth()->toDateString();
        $dt2 = $bulan->copy()->endOfMonth()->toDateString();

        $unitName = $unitId
            ? (Unit::whereKey($unitId)->value('unit') ?? ('Unit ' . $unitId))
            : '-';

        if ($nik) {
            $pegawaiLabel = MasterPegawai::where('nik', $nik)->value('nama');
            $unitName = 'NIK ' . $nik . ($pegawaiLabel ? (' - ' . $pegawaiLabel) : '');
            $subUnitId = null;
        }

        $statusMeta = null;
        if ($statusPegawai) {
            $statusMeta = StatusPegawai::whereRaw(
                'LOWER(TRIM(code))=?',
                [strtolower(trim($statusPegawai))]
            )->first();
        }

        $statusLabel = $statusMeta?->label
            ?: ($statusPegawai ? strtoupper($statusPegawai) : '-');

        $periodeLabel = Carbon::parse($dt1)->format('d M')
            . ' s/d ' .
            Carbon::parse($dt2)->format('d M Y');

        /*
        | ambil data rekap
        */
        $service = new RekapBulananService();

        $rows = $service->generate(
            $unitId,
            $subUnitId,
            $dt1,
            $dt2,
            $statusPegawai,
            $nik
        );

        /*
        | generate tanggal header
        */
        $dates = [];

        $start = Carbon::parse($dt1);
        $end   = Carbon::parse($dt2);

        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            if ($d->isWeekend()) continue;
            $dates[] = $d->toDateString();
        }

        $libur = HariLiburNasional::whereBetween('date', [$dt1, $dt2])
            ->pluck('date')
            ->flip();

        $data = [
            'rows' => $rows,
            'dates' => $dates,
            'libur' => $libur,
            'unitName' => $unitName,
            'statusLabel' => $statusLabel,
            'periodeLabel' => $periodeLabel
        ];

        return Excel::download(
            new self($data),
            'rekap-' . $validated['bulan'] . '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    public function view(): View
    {
        return view('rekap.excel', $this->data);
    }
}
