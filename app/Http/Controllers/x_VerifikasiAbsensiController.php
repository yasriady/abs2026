<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use App\Models\SubUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerifikasiAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->filled('date')
            ? $request->date
            : now()->toDateString();

        // ===== UNIT BY ROLE (copy dari PegawaiController) =====
        $user = $request->user();

        if ($user->hasRole('admin')) {
            $units = Unit::orderBy('id')->get();
        } elseif ($user->hasRole('admin_unit')) {
            $units = Unit::where('id', $user->unit_id)->get();
            $request->merge(['unit_id' => $user->unit_id]);
        } else {
            $units = collect();
        }

        $subUnits = collect();
        if ($request->unit_id) {
            $subUnits = SubUnit::where('unit_id', $request->unit_id)->get();
        }

        // ===== PEGAWAI AKTIF DI TANGGAL =====
        $pegawais = MasterPegawai::with([
            'activeHistory',
            'absensis' => function ($q) use ($date) {
                $q->where('tanggal', $date);
            }
        ])
            ->whereHas('activeHistory', function ($q) use ($request, $date) {
                $q->where('begin_date', '<=', $date)
                    ->where(
                        fn($x) =>
                        $x->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date)
                    );

                if ($request->unit_id) {
                    $q->where('id_unit', $request->unit_id);
                }

                if ($request->sub_unit_id) {
                    $q->where('id_sub_unit', $request->sub_unit_id);
                }
            })
            ->orderBy('nama')
            ->get();

        return Inertia::render('Absensi/Verifikasi/Index', [
            'pegawais' => $pegawais,
            'units'    => $units,
            'subUnits' => $subUnits,
            'filters'  => $request->only([
                'unit_id',
                'sub_unit_id',
                'date',
            ]),
        ]);
    }
}
