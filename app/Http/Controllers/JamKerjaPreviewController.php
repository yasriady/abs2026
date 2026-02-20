<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use App\Models\PegawaiHistory;
use App\Services\JadwalResolverService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JamKerjaPreviewController extends Controller
{
    public function index(Request $request, JadwalResolverService $resolver)
    {
        $nik = trim((string) $request->get('nik', ''));
        $date = $request->get('date') ?: now()->toDateString();

        $result = null;
        $pegawai = null;
        $history = null;
        $checked = false;

        if ($nik !== '') {
            $checked = true;

            $pegawai = MasterPegawai::query()
                ->where('nik', $nik)
                ->first(['id', 'nik', 'nip', 'nama']);

            if ($pegawai) {
                $result = $resolver->resolve($nik, $date);

                $history = PegawaiHistory::query()
                    ->with([
                        'unit:id,unit',
                        'subUnit:id,sub_unit',
                    ])
                    ->where('master_pegawai_id', $pegawai->id)
                    ->where('begin_date', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date);
                    })
                    ->orderByDesc('begin_date')
                    ->first();
            }
        }

        return Inertia::render('JamKerja/Preview/Index', [
            'filters' => [
                'nik' => $nik,
                'date' => $date,
            ],
            'checked' => $checked,
            'day' => Carbon::parse($date)->isoFormat('dddd'),
            'pegawai' => $pegawai,
            'history' => $history ? [
                'unit' => $history->unit?->unit,
                'sub_unit' => $history->subUnit?->sub_unit,
                'begin_date' => $history->begin_date,
                'end_date' => $history->end_date,
            ] : null,
            'resolved' => $result,
            'pegawaiHints' => MasterPegawai::query()
                ->orderBy('nama')
                ->limit(500)
                ->get(['nik', 'nama', 'nip']),
        ]);
    }
}
