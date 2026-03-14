<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AbsensiRaw;

class DataMesinAbsenController extends Controller
{
    public function index(Request $request)
    {
        $query = AbsensiRaw::query();

        // =========================
        // FILTER
        // =========================

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('date', $request->tanggal);
        }

        // Filter NIK
        if ($request->filled('nik')) {
            $query->where('nik', 'like', '%' . $request->nik . '%');
        }

        // Filter Nama
        if ($request->filled('nama')) {
            $query->where('name', 'like', '%' . $request->nama . '%');
        }

        // Filter Device
        if ($request->filled('device')) {
            $query->where('device_id', $request->device);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // =========================
        // QUERY FINAL
        // =========================

        $data = $query
            ->orderByDesc('ts')
            ->paginate(50)
            ->withQueryString();

        // Ambil list device unik untuk dropdown filter
        $devices = AbsensiRaw::select('device_id')
            ->distinct()
            ->pluck('device_id');

        return Inertia::render('DataMesinAbsen/Index', [
            'data' => $data,
            'filters' => $request->only([
                'tanggal',
                'nik',
                'nama',
                'device',
                'status'
            ]),
            'devices' => $devices,
        ]);
    }
}
