<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use App\Models\SubUnit;
use App\Models\Unit;
use App\Models\AbsensiSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class HarianControllerX extends Controller
{
    public function index(Request $request)
    {
        // =======================
        // DATE (AMAN UNTUK ISO / Y-m-d)
        // =======================
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : now();

        $user = $request->user();

        // =======================
        // UNIT BERDASARKAN ROLE
        // =======================
        if ($user->hasRole('admin')) {
            $units = Unit::orderBy('id')->get();
        } elseif ($user->hasRole('admin_unit')) {
            $units = Unit::where('id', $user->unit_id)->get();
            $request->merge(['unit_id' => $user->unit_id]);
        } else {
            $units = collect();
        }

        // =======================
        // SUB UNIT
        // =======================
        $subUnits = collect();
        if ($request->unit_id) {
            $subUnits = SubUnit::where('unit_id', $request->unit_id)
                ->orderBy('sub_unit')
                ->get();
        }

        // =======================
        // STEP 1: AMBIL PEGAWAI AKTIF (RINGAN & CEPAT)
        // =======================
        $activePegawaiIds = DB::table('pegawai_histories')
            ->select('master_pegawai_id')
            ->where('begin_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });

        if ($request->unit_id) {
            $activePegawaiIds->where('id_unit', $request->unit_id);
        }

        if ($request->sub_unit_id) {
            $activePegawaiIds->where('id_sub_unit', $request->sub_unit_id);
        }

        $activePegawaiIds = $activePegawaiIds->distinct();

        // =======================
        // STEP 2: QUERY MASTER PEGAWAI (PAGINATION MURNI)
        // =======================
        $query = MasterPegawai::query()
            ->whereIn('id', $activePegawaiIds);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $paginator = $query
            ->orderBy('nama', 'asc')
            ->paginate(20)
            ->withQueryString();

        // =======================
        // STEP 3: AMBIL SUMMARY ABSENSI (BATCH)
        // =======================
        $niks = $paginator->getCollection()
            ->pluck('nik')
            ->filter()
            ->unique()
            ->values();

        $summaries = AbsensiSummary::query()
            ->select([
                'id',
                'nik',
                'date',
                'time_in',
                'time_out',
                'device_id_in',
                'device_id_out',
                'is_final',
            ])
            ->with([
                'deviceIn:device_id,desc',
                'deviceOut:device_id,desc',
            ])
            ->whereBetween('date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->whereIn('nik', $niks)
            ->get()
            ->keyBy('nik');


        // =======================
        // STEP 4: INJECT SUMMARY (INDEX-SAFE)
        // =======================
        $paginator->getCollection()->transform(function ($pegawai) use ($summaries) {

            $summary = $summaries->get(trim($pegawai->nik));

            if (!$summary) {
                $pegawai->summary = null;
                return $pegawai;
            }

            // =========================
            // STATUS RINGKAS (CEPAT)
            // =========================
            $statusMasuk  = $summary->time_in  ? 'HADIR' : '-';
            $statusPulang = $summary->time_out ? 'HADIR' : '-';

            $statusHari = ($summary->time_in || $summary->time_out)
                ? 'HADIR'
                : 'ALPA';

            // =========================
            // DATA UNTUK UI LIST
            // =========================
            $pegawai->summary = [
                'id' => $summary->id,

                // JAM (RAW, CEPAT)
                'time_in'  => $summary->time_in,
                'time_out' => $summary->time_out,

                // DEVICE
                'device_desc_in'  => $summary->deviceIn?->desc,
                'device_desc_out' => $summary->deviceOut?->desc,

                // STATUS (RINGKAS UNTUK LIST)
                'status_masuk'  => $statusMasuk,
                'status_pulang' => $statusPulang,
                'status_hari'   => $statusHari,

                // FLAG
                'is_final' => (bool) $summary->is_final,
            ];

            return $pegawai;
        });

        // =======================
        // RENDER INERTIA
        // =======================
        return Inertia::render('Absensi/Harian/Index', [
            'pegawais' => $paginator,

            'stats' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'from'  => $paginator->firstItem(),
                'to'    => $paginator->lastItem(),
            ],

            'filters' => [
                'unit_id'     => $request->unit_id,
                'sub_unit_id' => $request->sub_unit_id,
                'date'        => $date->toDateString(),
                'search'      => $request->search,
            ],

            'units'    => $units,
            'subUnits' => $subUnits,
        ]);
    }
}
