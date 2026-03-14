<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiRaw;
use Carbon\Carbon;
use Inertia\Inertia;

class AbsensiRawController extends Controller
{
    public function index(Request $request)
    {
        // =========================
        // DEFAULT EMPTY PAGINATOR
        // =========================
        $paginator = null;

        // =========================
        // FILTER ACTIVE ?
        // =========================
        $isFiltered = $request->has('filter');

        if ($isFiltered) {

            $request->validate([
                'tanggal' => ['nullable', 'date'],
                'nik'     => ['nullable', 'string'],
                'search'  => ['nullable', 'string']
            ]);

            $masterDb = config('database.connections.mysql.database');

            $query = AbsensiRaw::query()
                ->leftJoin("$masterDb.master_pegawais as mp", 'mp.nik', '=', 'DB_ATT_tbl_attendance2.nik')
                ->select([
                    'DB_ATT_tbl_attendance2.*',
                    'mp.nama as nama'
                ]);

            if ($request->tanggal) {
                $query->whereDate('DB_ATT_tbl_attendance2.date', '<=', Carbon::parse($request->tanggal));
            }

            if ($request->nik) {
                $query->where('DB_ATT_tbl_attendance2.nik', $request->nik);
            }

            if ($request->search) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('DB_ATT_tbl_attendance2.nik', 'like', "%$search%")
                        ->orWhere('mp.nama', 'like', "%$search%")
                        ->orWhere('DB_ATT_tbl_attendance2.device', 'like', "%$search%");
                });
            }

            $paginator = $query
                ->orderBy('DB_ATT_tbl_attendance2.date', 'desc')
                ->paginate(50)
                ->withQueryString();
        }

        // =========================
        // RENDER
        // =========================
        return Inertia::render('AbsensiRaw/Index', [

            'rows' => $paginator,

            'stats' => $paginator ? [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'from'  => $paginator->firstItem(),
                'to'    => $paginator->lastItem(),
            ] : null,

            'filters' => [
                'tanggal' => $request->tanggal,
                'nik'     => $request->nik,
                'search'  => $request->search,
            ],

            'isFiltered' => $isFiltered
        ]);
    }
}
