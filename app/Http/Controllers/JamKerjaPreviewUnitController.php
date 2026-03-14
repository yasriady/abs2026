<?php

namespace App\Http\Controllers;

use App\Models\PegawaiHistory;
use App\Models\Unit;
use App\Services\JadwalResolverService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\BulkJadwalResolverService;

class JamKerjaPreviewUnitController extends Controller
{
    public function index(Request $request, BulkJadwalResolverService $resolver)
    {
        \DB::enableQueryLog();

        $month = $request->get('month');
        $unitId = $request->get('unit_id');
        $user = auth()->user();

        // Ambil daftar unit untuk dropdown
        $units = Unit::select('id', 'unit as nama_unit')
            ->when(
                $user->hasRole('admin_unit') || $user->hasRole('admin_subunit'),
                fn($q) => $q->where('id', $user->unit_id)
            )
            ->orderBy('nama_unit')
            ->get();

        if (!$month || !$unitId) {
            return Inertia::render('JamKerja/PreviewUnit/Index', [
                'loaded' => false,
                'rows' => [],
                'dates' => [],
                'month' => null,
                'unitId' => $unitId,
                'units' => $units
            ]);
        }

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Ambil histories pegawai yang aktif pada periode tersebut
        $histories = PegawaiHistory::query()
            ->with(['masterPegawai' => function ($q) {
                $q->select('id', 'nama', 'nik');
            }])
            ->where('begin_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start);
            })
            ->where('id_unit', $unitId) // Filter berdasarkan unit yang dipilih
            ->when(
                $user->hasRole('admin_subunit'),
                fn($q) => $q->where('id_sub_unit', $user->sub_unit_id)
            )
            ->get()
            ->groupBy('master_pegawai_id');

        $dates = [];
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $dates[] = $cursor->copy();
            $cursor->addDay();
        }

        $resolver->preload($start, $end);

        $rows = [];

        foreach ($histories as $pegawaiId => $items) {
            // Ambil history pertama untuk mendapatkan data pegawai
            $firstHistory = $items->first();
            $pegawai = $firstHistory->masterPegawai;

            // Skip jika pegawai tidak ditemukan
            if (!$pegawai) continue;

            $jadwal = [];

            foreach ($dates as $d) {
                $jadwal[$d->day] = $resolver->resolve(
                    $pegawai,
                    $d->dayOfWeekIso
                );
            }

            $rows[] = [
                'id' => $pegawaiId,
                'nama' => $pegawai->nama,
                'nik' => $pegawai->nik,
                'unit_id' => $firstHistory->unit_id,
                'sub_unit_id' => $firstHistory->sub_unit_id,
                'jadwal' => $jadwal
            ];
        }

        // Urutkan rows berdasarkan nama
        usort($rows, function ($a, $b) {
            return strcmp($a['nama'], $b['nama']);
        });

        $queries = \DB::getQueryLog();
        \Log::info('Total queries: ' . count($queries));
        foreach ($queries as $index => $query) {
            \Log::info("Query {$index}: " . json_encode($query));
        }

        // PAGINATION MANUAL
        $page = (int) $request->get('page', 1);
        $perPage = 20;

        $total = count($rows);
        $offset = ($page - 1) * $perPage;

        $paginatedRows = array_slice($rows, $offset, $perPage);

        return Inertia::render('JamKerja/PreviewUnit/Index', [
            'loaded' => true,
            'month' => $month,
            'unitId' => $unitId,
            'units' => $units,
            'dates' => collect($dates)->map->day,
            'rows' => $paginatedRows,

            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ]);
    }
}
