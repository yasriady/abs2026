<?php

namespace App\Http\Controllers;

use App\Jobs\RegenerateNikJob;
use App\Jobs\RegenerateUnitJob;
use App\Models\Unit;
use App\Models\SubUnit;
use App\Models\MasterPegawai;
use App\Models\AbsensiSummary;
use App\Models\EtlJobNik;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\info;

class AbsensiHarianController extends Controller
{
    public function index(Request $request)
    {
        // =======================
        // DATE
        // =======================
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : now();

        $user = $request->user();

        // =======================
        // UNIT BASED ON ROLE
        // =======================
        if ($user->hasRole('admin')) {
            $units = Unit::orderBy('unit')->get();
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

        // $statuses = Status::enabled()
        //     ->orderBy('ordering')
        //     ->get([
        //         'name',
        //         'desc',
        //         'color',
        //         'day',
        //         'in',
        //         'out'
        //     ]);


        // =======================
        // STEP 1: ACTIVE PEGAWAI IDS
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
        // STEP 2: MASTER PEGAWAI PAGINATION
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
        // STEP 3: LOAD SUMMARY BATCH
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
                'time_in_final',
                'time_out_final',
                'status_masuk_final',
                'status_pulang_final',
                'status_hari_final',
                'device_id_in',
                'device_id_out',
                'final_note',
                'is_final',
                'device_desc_in',
                'device_desc_out',
                'attribute_in',
                'attribute_out',
                'notes_hari',
                'notes_in',
                'notes_out',
            ])
            // ->with([
            //     'deviceIn:device_id,desc',
            //     'deviceOut:device_id,desc',
            // ])
            ->whereDate('date', $date)
            ->whereIn('nik', $niks)
            ->get()
            ->keyBy('nik');

        // =======================
        // STEP 4: INJECT SUMMARY
        // =======================
        $paginator->getCollection()->transform(function ($pegawai) use ($summaries) {

            $summary = $summaries->get(trim($pegawai->nik));

            if (!$summary) {
                $pegawai->summary = null;
                return $pegawai;
            }

            $pegawai->summary = [
                'id' => $summary->id,

                'time_in_final'  => $summary->time_in_final,
                'time_out_final' => $summary->time_out_final,

                'status_masuk_final'  => $summary->status_masuk_final,
                'status_pulang_final' => $summary->status_pulang_final,
                'status_hari_final'   => $summary->status_hari_final,

                'device_desc_in'  => $summary->deviceIn?->desc,
                'device_desc_out' => $summary->deviceOut?->desc,

                'final_note' => $summary->final_note,
                'is_final'   => (bool) $summary->is_final,

                'attribute_in'       => $summary->attribute_in,
                'machine_in'    => $summary->device_desc_in,
                'attribute_out'       => $summary->attribute_out,
                'machine_out'    => $summary->device_desc_out,
                'notes_hari'    => $summary->notes_hari,
                'notes_in'    => $summary->notes_in,
                'notes_out'    => $summary->notes_out,

            ];

            return $pegawai;
        });

        // =======================
        // RENDER
        // =======================
        return Inertia::render('Absensi/AbsensiHarian', [
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
            // 'statuses' => $statuses,
            'statusDay' => Status::enabled()->forDay()->get(),
            'statusIn'  => Status::enabled()->forIn()->get(),
            // 'statusOut' => Status::enabled()->forOut()->get(),

        ]);
    }

    public function updateStatus(Request $r)
    {
        $r->validate([
            'id'     => 'required|integer',
            'status' => 'required|string|max:10',
            'notes'  => 'nullable|string'
        ]);

        DB::transaction(function () use ($r) {

            $summary = AbsensiSummary::findOrFail($r->id);

            // update summary
            $summary->update([
                'status_hari_final' => $r->status,
                'notes_hari'        => $r->notes
            ]);

            $summary->syncAbsent($r->status, $r->notes);
        });

        return back()->with('success', 'Status berhasil diperbarui');
    }

    public function updateJam(Request $r)
    {
        // info($r->all());

        $r->merge([
            'jam'   => (int) $r->jam,
            'menit' => (int) $r->menit
        ]);

        /* ================= VALIDATION ================= */
        $validated = $r->validate([
            'id'     => ['required', 'integer', 'exists:absensi_summaries,id'],
            'type'   => ['required', 'in:in,out'],
            'status' => ['required', 'string', 'max:50'],
            'jam'    => ['required', 'integer', 'min:0', 'max:23'],
            'menit'  => ['required', 'integer', 'min:0', 'max:59'],
            'notes'  => ['nullable', 'string', 'max:500']
        ]);

        // info(__FUNCTION__);

        /* ================= GET MODEL ================= */

        $summary = AbsensiSummary::findOrFail($validated['id']);


        /* ================= NORMALIZE TIME ================= */

        $jam   = str_pad($validated['jam'], 2, '0', STR_PAD_LEFT);
        $menit = str_pad($validated['menit'], 2, '0', STR_PAD_LEFT);
        $time  = "$jam:$menit:00";


        /* ================= ASSIGN FIELD ================= */

        if ($validated['type'] === 'in') {
            $summary->time_in_final      = $time;
            $summary->status_masuk_final = $validated['status'];
            $summary->notes_in           = $validated['notes'];
            $summary->attribute_in       = 'Adm';
        } else {
            $summary->time_out_final      = $time;
            $summary->status_pulang_final = $validated['status'];
            $summary->notes_out           = $validated['notes'];
            $summary->attribute_out       = 'Adm';
        }


        /* ================= SAVE ================= */

        $summary->save();


        /* ================= RESPONSE ================= */

        return back()->with('success', 'Jam absensi berhasil diperbarui');
    }

    public function regenerateUnit(Request $request)
    {
        // info(__FUNCTION__);
        Log::info("REGENERATE HIT", request()->all());

        $user = $request->user();

        abort_if(
            !$user->hasRole('admin') && !$user->hasRole('admin_unit'),
            403
        );

        $date = $request->date ?? now()->toDateString();

        if ($user->hasRole('admin')) {
            $unitId = $request->unit_id;
        } else {
            $unitId = $user->unit_id;
        }

        if (cache()->has("etl-running-$unitId")) {
            return back()->with('error', 'ETL sedang berjalan');
        }

        cache()->put("etl-running-$unitId", true, 300);

        RegenerateUnitJob::dispatch(
            $date,
            $unitId
        );

        return back()->with('success', 'Proses regenerate dijalankan.');
    }

    public function regenerateNik(Request $r)
    {
        $job = EtlJobNik::create([
            'nik' => $r->nik,
            'date' => $r->date,
            'status' => 'queued'
        ]);

        RegenerateNikJob::dispatch(
            $r->nik,
            $r->date,
            $job->id
        );

        return response()->json([
            'job_id' => $job->id
        ]);
    }

    public function statusNik($id)
    {
        return EtlJobNik::findOrFail($id);
    }
}
