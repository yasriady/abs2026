<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use App\Models\PegawaiHistory;
use App\Models\SubUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PegawaiController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:pegawai.view')->only('index');
        // $this->middleware('permission:pegawai.create')->only('store');
        // $this->middleware('permission:pegawai.update')->only('update');
        // $this->middleware('permission:pegawai.delete')->only('destroy');
    }

    // =========================
    // INDEX
    // =========================
    public function index(Request $request)
    {
        $date = $request->filled('date')
            ? $request->date
            : now()->toDateString();

        $user = $request->user();

        // =======================
        // UNIT BERDASARKAN ROLE
        // =======================
        if ($user->hasRole('admin')) {
            $units = Unit::orderBy('id')->get();
        } elseif ($user->hasRole('admin_unit')) {
            $units = Unit::where('id', $user->unit_id)->get();
            // paksa filter unit ke unit sendiri
            $request->merge([
                'unit_id' => $user->unit_id
            ]);
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
        // QUERY PEGAWAI
        // =======================
        $query = MasterPegawai::query()
            ->with(['activeHistory' => function ($q) use ($request, $date) {

                if (!$request->boolean('all_date')) {
                    $q->where('begin_date', '<=', $date)
                        ->where(function ($x) use ($date) {
                            $x->whereNull('end_date')
                                ->orWhere('end_date', '>=', $date);
                        });
                }

                if ($request->unit_id) {
                    $q->where('id_unit', $request->unit_id);
                }

                if ($request->sub_unit_id) {
                    $q->where('id_sub_unit', $request->sub_unit_id);
                }
            }])
            ->whereHas('activeHistory', function ($q) use ($request, $date) {

                if (!$request->boolean('all_date')) {
                    $q->where('begin_date', '<=', $date)
                        ->where(function ($x) use ($date) {
                            $x->whereNull('end_date')
                                ->orWhere('end_date', '>=', $date);
                        });
                }

                if ($request->unit_id) {
                    $q->where('id_unit', $request->unit_id);
                }

                if ($request->sub_unit_id) {
                    $q->where('id_sub_unit', $request->sub_unit_id);
                }
            });

        // =======================
        // SEARCH
        // =======================
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('nama', 'asc')
            ->paginate(20)
            ->withQueryString();

        // dd($paginator->items());

        return Inertia::render('Pegawai/Index', [
            'pegawais' => $paginator,

            'stats' => [
                'total' => $paginator->total(),   // semua hasil (lintas halaman)
                'count' => $paginator->count(),   // yang tampil di halaman ini
                'from'  => $paginator->firstItem(),
                'to'    => $paginator->lastItem(),
            ],

            'filters' => $request->only([
                'unit_id',
                'sub_unit_id',
                'date',
                'all_date',
                'search',
            ]),
            'units' => $units,
            'subUnits' => $subUnits,
        ]);
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|unique:master_pegawais,nik',
            'nama' => 'required',
            'status_kepegawaian' => 'required|in:pns,pppk,pppk-pw,thl,nib',
            'id_unit' => 'required',
            'begin_date' => 'required|date',
        ]);

        DB::transaction(function () use ($request) {

            $pegawai = MasterPegawai::firstOrCreate(
                ['nik' => $request->nik],
                [
                    'nip' => $request->nip,
                    'nama' => $request->nama,
                ]
            );

            // matikan histori lama
            PegawaiHistory::where('master_pegawai_id', $pegawai->id)
                ->update(['is_active' => false]);

            $idSubUnit = $request->id_sub_unit ?: null;
            $idStruktur = $request->id_struktur_organisasi ?: null;

            PegawaiHistory::create([
                'master_pegawai_id' => $pegawai->id,
                'status_kepegawaian' => $request->status_kepegawaian,
                'id_unit' => $request->id_unit,
                'id_sub_unit' => $idSubUnit,
                'id_struktur_organisasi' => $idStruktur,
                'begin_date' => $request->begin_date,
                'end_date' => $request->end_date,
                'is_active' => true,
                'lokasi_kerja' => $request->lokasi_kerja,
                'order' => $request->order ?? 0,
            ]);
        });

        return redirect()->back()->with('success', 'Pegawai berhasil ditambahkan');
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, MasterPegawai $pegawai)
    {
        $request->validate([
            'nik' => 'required|unique:master_pegawais,nik,' . $pegawai->id,
            'nama' => 'required',
            'status_kepegawaian' => 'required|in:pns,pppk,pppk-pw,thl,nib',
            'id_unit' => 'required',
            'begin_date' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $pegawai) {

            // update master
            $pegawai->update([
                'nik' => $request->nik,
                'nip' => $request->nip,
                'nama' => $request->nama,
            ]);

            // matikan histori lama
            PegawaiHistory::where('master_pegawai_id', $pegawai->id)
                ->update(['is_active' => false]);

            $idSubUnit = $request->id_sub_unit ?: null;
            $idStruktur = $request->id_struktur_organisasi ?: null;

            // buat histori baru
            PegawaiHistory::create([
                'master_pegawai_id' => $pegawai->id,
                'status_kepegawaian' => $request->status_kepegawaian,
                'id_unit' => $request->id_unit,
                'id_sub_unit' => $idSubUnit,
                'id_struktur_organisasi' => $idStruktur,
                'begin_date' => $request->begin_date,
                'end_date' => $request->end_date,
                'is_active' => true,
                'lokasi_kerja' => $request->lokasi_kerja,
                'order' => $request->order ?? 0,
            ]);
        });

        return redirect()->back()->with('success', 'Pegawai berhasil diperbarui');
    }

    // =========================
    // DELETE
    // =========================
    public function destroy(MasterPegawai $pegawai)
    {
        DB::transaction(function () use ($pegawai) {

            PegawaiHistory::where('master_pegawai_id', $pegawai->id)
                ->update(['is_active' => false]);

            $pegawai->delete();
        });

        return redirect()->back()->with('success', 'Pegawai berhasil dihapus');
    }

    // =========================
    // MIGRASI FAST (ANTI DUPLIKAT)
    // =========================
    public function migrasiPegawaiFast()
    {
        DB::beginTransaction();

        try {

            // =========================
            // MASTER (1 BARIS PER NIK)
            // =========================
            DB::statement("
                INSERT INTO master_pegawais (nik, nip, nama, created_at, updated_at)
                SELECT
                    t.nik,
                    MAX(t.nip)  AS nip,
                    MAX(t.nama) AS nama,
                    NOW(),
                    NOW()
                FROM tbl_pegawai_all t
                WHERE t.nik IS NOT NULL
                  AND t.nik <> ''
                GROUP BY t.nik
                ON DUPLICATE KEY UPDATE
                    nip = VALUES(nip),
                    nama = VALUES(nama),
                    updated_at = NOW()
            ");

            // =========================
            // HISTORI (SAFE MODE)
            // =========================
            DB::statement("
                INSERT INTO pegawai_histories (
                    master_pegawai_id,
                    status_kepegawaian,
                    id_unit,
                    id_sub_unit,
                    id_struktur_organisasi,
                    begin_date,
                    end_date,
                    is_active,
                    lokasi_kerja,
                    `order`,
                    created_at,
                    updated_at
                )
                SELECT
                    m.id AS master_pegawai_id,

                    CASE
                        WHEN LOWER(TRIM(t.status_kepegawaian)) IN ('asn','pns','pppk','pppk-pw','thl','nib')
                            THEN LOWER(TRIM(t.status_kepegawaian))
                        ELSE NULL
                    END AS status_kepegawaian,

                    NULLIF(t.id_unit, -1),
                    NULLIF(t.id_sub_unit, -1),
                    NULLIF(t.id_struktur_organisasi, -1),

                    CASE
                        WHEN CAST(t.begin_date AS CHAR) IN ('0000-00-00', '1900-01-00')
                          OR t.begin_date IS NULL
                        THEN '1970-01-01'
                        ELSE STR_TO_DATE(CAST(t.begin_date AS CHAR), '%Y-%m-%d')
                    END AS begin_date,

                    CASE
                        WHEN CAST(t.end_date AS CHAR) IN ('0000-00-00', '1900-01-00')
                          OR t.end_date IS NULL
                        THEN NULL
                        ELSE STR_TO_DATE(CAST(t.end_date AS CHAR), '%Y-%m-%d')
                    END AS end_date,

                    CASE
                        WHEN
                            CURDATE() >=
                            CASE
                                WHEN CAST(t.begin_date AS CHAR) IN ('0000-00-00', '1900-01-00')
                                  OR t.begin_date IS NULL
                                THEN '1970-01-01'
                                ELSE STR_TO_DATE(CAST(t.begin_date AS CHAR), '%Y-%m-%d')
                            END
                        AND (
                            CASE
                                WHEN CAST(t.end_date AS CHAR) IN ('0000-00-00', '1900-01-00')
                                  OR t.end_date IS NULL
                                THEN CURDATE()
                                ELSE STR_TO_DATE(CAST(t.end_date AS CHAR), '%Y-%m-%d')
                            END
                            >= CURDATE()
                        )
                        THEN 1
                        ELSE 0
                    END AS is_active,

                    t.lokasi_kerja,
                    COALESCE(t.`order`, 0),
                    NOW(),
                    NOW()
                FROM tbl_pegawai_all t
                JOIN master_pegawais m
                  ON m.nik = t.nik
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM pegawai_histories h
                    WHERE h.master_pegawai_id = m.id
                      AND h.begin_date =
                        CASE
                            WHEN CAST(t.begin_date AS CHAR) IN ('0000-00-00', '1900-01-00')
                              OR t.begin_date IS NULL
                            THEN '1970-01-01'
                            ELSE STR_TO_DATE(CAST(t.begin_date AS CHAR), '%Y-%m-%d')
                        END
                )
            ");

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Migrasi cepat berhasil (master & histori, anti-duplikasi)',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function histori(Request $request, MasterPegawai $pegawai)
    {
        $from = $request->filled('from')
            ? $request->from
            : null;

        $to = $request->filled('to')
            ? $request->to
            : null;

        $query = PegawaiHistory::where('master_pegawai_id', $pegawai->id)
            ->orderBy('begin_date', 'desc');

        if ($from) {
            $query->whereDate('begin_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('begin_date', '<=', $to);
        }

        $histories = $query->paginate(20)->withQueryString();

        return Inertia::render('Pegawai/Histori', [
            'pegawai' => [
                'id'   => $pegawai->id,
                'nik'  => $pegawai->nik,
                'nip'  => $pegawai->nip,
                'nama' => $pegawai->nama,
            ],

            'histories' => $histories,

            'filters' => [
                'from' => $from,
                'to'   => $to,
            ],
        ]);
    }

    public function show(MasterPegawai $pegawai)
    {
        $pegawai->load([
            'activeHistory',
        ]);

        return Inertia::render('Pegawai/Show', [
            'pegawai' => [
                'id' => $pegawai->id,
                'nik' => $pegawai->nik,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama,
                'active_history' => $pegawai->activeHistory,
            ],
        ]);
    }


    public function editMaster(MasterPegawai $pegawai)
    {
        return Inertia::render('Pegawai/EditMaster', [
            'pegawai' => [
                'id'   => $pegawai->id,
                'nik'  => $pegawai->nik,
                'nip'  => $pegawai->nip,
                'nama' => $pegawai->nama,
            ],
        ]);
    }

    public function updateMaster(Request $request, MasterPegawai $pegawai)
    {
        $request->validate([
            'nik'  => 'required|unique:master_pegawais,nik,' . $pegawai->id,
            'nama' => 'required|string|max:255',
            'nip'  => 'nullable|string|max:50',
        ]);

        $pegawai->update([
            'nik'  => $request->nik,
            'nip'  => $request->nip,
            'nama' => $request->nama,
        ]);

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data master pegawai berhasil diperbarui');
    }

    public function editHistori(MasterPegawai $pegawai, PegawaiHistory $history)
    {
        abort_if($history->master_pegawai_id !== $pegawai->id, 404);

        return Inertia::render('Pegawai/EditHistori', [
            'pegawai' => [
                'id'   => $pegawai->id,
                'nik'  => $pegawai->nik,
                'nip'  => $pegawai->nip,
                'nama' => $pegawai->nama,
            ],
            'history' => $history,
        ]);
    }

    public function updateHistori(Request $request, MasterPegawai $pegawai, PegawaiHistory $history)
    {
        abort_if($history->master_pegawai_id !== $pegawai->id, 404);

        $request->validate([
            'status_kepegawaian' => 'required|in:asn,pns,pppk,pppk-pw,thl,nib',
            'id_unit' => 'required',
            'begin_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:begin_date',
        ]);

        DB::transaction(function () use ($request, $pegawai) {

            // nonaktifkan histori aktif
            PegawaiHistory::where('master_pegawai_id', $pegawai->id)
                ->where('is_active', 1)
                ->update(['is_active' => 0]);

            // buat histori baru (hasil edit)
            PegawaiHistory::create([
                'master_pegawai_id' => $pegawai->id,
                'status_kepegawaian' => $request->status_kepegawaian,
                'id_unit' => $request->id_unit,
                'id_sub_unit' => $request->id_sub_unit,
                'id_struktur_organisasi' => $request->id_struktur_organisasi,
                'begin_date' => $request->begin_date,
                'end_date' => $request->end_date,
                'is_active' => true,
                'lokasi_kerja' => $request->lokasi_kerja,
                'order' => $request->order ?? 0,
            ]);
        });

        return redirect()
            ->route('pegawai.histori', $pegawai->id)
            ->with('success', 'Histori pegawai berhasil diperbarui');
    }

    public function editHistoriRaw(MasterPegawai $pegawai, PegawaiHistory $history)
    {
        // Pastikan histori milik pegawai ini
        abort_if($history->master_pegawai_id !== $pegawai->id, 404);

        // Batasi akses (contoh: hanya admin)
        abort_unless(auth()->user()->hasRole('admin'), 403);

        return Inertia::render('Pegawai/EditHistoriRaw', [
            'pegawai' => [
                'id'   => $pegawai->id,
                'nik'  => $pegawai->nik,
                'nip'  => $pegawai->nip,
                'nama' => $pegawai->nama,
            ],
            'history' => $history,
        ]);
    }

    public function updateHistoriRaw(Request $request, MasterPegawai $pegawai, PegawaiHistory $history)
    {
        abort_if($history->master_pegawai_id !== $pegawai->id, 404);
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $request->validate([
            'status_kepegawaian' => 'required|in:asn,pns,pppk,pppk-pw,thl,nib',
            'id_unit' => 'nullable|integer',
            'id_sub_unit' => 'nullable|integer',
            'id_struktur_organisasi' => 'nullable|integer',
            'begin_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:begin_date',
            'is_active' => 'required|boolean',
            'lokasi_kerja' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $history) {
            $history->update([
                'status_kepegawaian' => $request->status_kepegawaian,
                'id_unit' => $request->id_unit,
                'id_sub_unit' => $request->id_sub_unit,
                'id_struktur_organisasi' => $request->id_struktur_organisasi,
                'begin_date' => $request->begin_date,
                'end_date' => $request->end_date,
                'is_active' => $request->is_active,
                'lokasi_kerja' => $request->lokasi_kerja,
                'order' => $request->order ?? 0,
            ]);
        });

        return redirect()
            ->route('pegawai.histori', $pegawai->id)
            ->with('success', 'Raw histori berhasil diperbarui');
    }
}
