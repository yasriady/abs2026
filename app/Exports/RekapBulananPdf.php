<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\RekapBulananService;
use Carbon\Carbon;

class RekapBulananPdf
{
    public static function download(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'integer'],
            'sub_unit_id' => ['nullable', 'integer'],
            'bulan' => ['required'],
            'status_pegawai' => ['nullable']
        ]);

        $service = new RekapBulananService();

        // generate rows langsung
        $rows = $service->generate(
            $validated['unit_id'] ?? null,
            $validated['sub_unit_id'] ?? null,
            $validated['bulan'],
            $validated['status_pegawai'] ?? null
        );

        // ambil tanggal dari row pertama
        $dates = !empty($rows)
            ? array_keys($rows[0]['dates'])
            : [];

        // label periode
        $periodeLabel = Carbon::parse($validated['bulan'])->translatedFormat('F Y');

        // label unit (opsional — sesuaikan kalau punya helper)
        $unitName = 'Unit';
        $statusLabel = '';

        $pdf = Pdf::loadView('rekap.pdf', compact(
            'rows',
            'dates',
            'unitName',
            'statusLabel',
            'periodeLabel'
        ))->setPaper('A4', 'landscape');

        return $pdf->download('rekap-absensi.pdf');
    }
}
