<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\MasterPegawai;
use App\Models\PegawaiHistory;
use App\Models\SubUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
            ->with(['activeHistory.unit'])
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

        return Inertia::render('Pegawai_v1/Index', [
            'pegawais' => $paginator,

            'stats' => [
                'total' => $paginator->total(),   // semua hasil (lintas halaman)
                'count' => $paginator->count(),   // yang tampil di halaman ini
                'from'  => $paginator->firstItem(),
                'to'    => $paginator->lastItem(),
                // 'photo_url' => asset('storage/pegawai/'.$pegawai->photo),

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

    public function edit(MasterPegawai $pegawai)
    {
        $pegawai->load('activeHistory');

        return Inertia::render('Pegawai_v1/Edit', [
            'pegawai' => [
                'id' => $pegawai->id,
                'nik' => $pegawai->nik,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama,
                'foto_url' => $pegawai->foto_url,
            ],
            'history' => $pegawai->activeHistory,
            'units' => Unit::orderBy('unit')->get(),
            'subUnits' => SubUnit::orderBy('sub_unit')->get(),
        ]);
    }

    public function update(Request $request, MasterPegawai $pegawai)
    {
        $request->validate([
            'nik' => 'required|unique:master_pegawais,nik,' . $pegawai->id,
            'nama' => 'required|string|max:255',
            'status_kepegawaian' => 'required|in:pns,pppk,pppk-pw,thl,nib',
            'id_unit' => 'required|integer',
            'begin_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:begin_date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::transaction(function () use ($request, $pegawai) {

            // =====================
            // UPLOAD FOTO (JIKA ADA)
            // =====================
            if ($request->hasFile('foto')) {

                $nik = $request->nik;
                $filename = $nik . '.jpg';
                $path = 'pegawai/' . $filename;

                // init image manager (v3)
                $manager = new ImageManager(new Driver());

                $image = $manager
                    ->read($request->file('foto')->getRealPath())
                    ->toJpeg(85);

                // hapus file lama jika ada
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }

                // simpan ke storage public
                Storage::disk('public')->put($path, (string) $image);

                // simpan path ke DB
                $pegawai->foto = $path;
            }

            // =====================
            // UPDATE MASTER
            // =====================
            $pegawai->update([
                'nik' => $request->nik,
                'nip' => $request->nip,
                'nama' => $request->nama,
            ]);

            // =====================
            // HISTORI (tetap seperti sebelumnya)
            // =====================
            PegawaiHistory::where('master_pegawai_id', $pegawai->id)
                ->where('is_active', 1)
                ->update(['is_active' => 0]);

            PegawaiHistory::create([
                'master_pegawai_id' => $pegawai->id,
                'status_kepegawaian' => $request->status_kepegawaian,
                'id_unit' => $request->id_unit,
                'id_sub_unit' => $request->id_sub_unit,
                'begin_date' => $request->begin_date,
                'end_date' => $request->end_date,
                'lokasi_kerja' => $request->lokasi_kerja,
                'order' => $request->order ?? 0,
                'is_active' => 1,
            ]);
        });

        return redirect()
            ->route('v1.pegawai.index')
            ->with('success', 'Pegawai berhasil diperbarui');
    }
}
