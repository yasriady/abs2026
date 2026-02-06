<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use App\Models\SubUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HarianController extends Controller
{
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
        // QUERY PEGAWAI (SAMA DENGAN PEGAWAI)
        // =======================
        $query = MasterPegawai::query()
            ->with(['activeHistory' => function ($q) use ($request, $date) {

                // ❗ absensi harian TETAP pakai filter tanggal
                $q->where('begin_date', '<=', $date)
                    ->where(function ($x) use ($date) {
                        $x->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date);
                    });

                if ($request->unit_id) {
                    $q->where('id_unit', $request->unit_id);
                }

                if ($request->sub_unit_id) {
                    $q->where('id_sub_unit', $request->sub_unit_id);
                }

                // ⬇️ PENTING: agar nama unit & subunit bisa ditampilkan
                $q->with(['unit', 'subUnit']);
            }])
            ->whereHas('activeHistory', function ($q) use ($request, $date) {

                $q->where('begin_date', '<=', $date)
                    ->where(function ($x) use ($date) {
                        $x->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date);
                    });

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

        $paginator = $query
            ->orderBy('nama', 'asc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Absensi/Harian/Index', [
            'pegawais' => $paginator,

            'stats' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'from'  => $paginator->firstItem(),
                'to'    => $paginator->lastItem(),
            ],

            'filters' => $request->only([
                'unit_id',
                'sub_unit_id',
                'date',
                'search',
            ]),

            'units'    => $units,
            'subUnits' => $subUnits,
        ]);
    }


    public function xindexok1(Request $request)
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
        // QUERY PEGAWAI AKTIF
        // =======================
        $query = MasterPegawai::query()
            ->with(['activeHistory' => function ($q) use ($request, $date) {

                $q->where('begin_date', '<=', $date)
                    ->where(function ($x) use ($date) {
                        $x->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date);
                    });

                if ($request->unit_id) {
                    $q->where('id_unit', $request->unit_id);
                }

                if ($request->sub_unit_id) {
                    $q->where('id_sub_unit', $request->sub_unit_id);
                }
            }])
            ->whereHas('activeHistory', function ($q) use ($request, $date) {

                $q->where('begin_date', '<=', $date)
                    ->where(function ($x) use ($date) {
                        $x->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date);
                    });

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

        $pegawais = $query
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Absensi/Harian/Index', [
            'pegawais' => $pegawais,
            'stats' => [
                'total' => $pegawais->total(),
                'count' => $pegawais->count(),
                'from'  => $pegawais->firstItem(),
                'to'    => $pegawais->lastItem(),
            ],
            'units'    => $units,
            'subUnits' => $subUnits,
            'filters'  => $request->only([
                'unit_id',
                'sub_unit_id',
                'date',
                'search',
            ]),
        ]);
    }
}
