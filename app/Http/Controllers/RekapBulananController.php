<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Rekap;
use App\Models\RekapBulanan;
use App\Models\StatusPegawai;
use App\Models\SubUnit;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;
use App\Services\RekapBulananService;

class RekapBulananController extends Controller
{
    public function index(Request $request)
    {
        // $user = auth()->user();
        $user = $request->user();
        $unitFilter = $request->filled('unit') ? (int) $request->unit : null;
        $actor = $user->username ?? $user->name ?? $user->email ?? ('user-' . $user->id);

        // Log::info($user->role);

        /*
        |--------------------------------------------------------------------------
        | 1. Tentukan bulan target
        |--------------------------------------------------------------------------
        */

        $bulan = $request->bulan
            ? Carbon::parse($request->bulan)
            : now();

        $tanggal = $bulan->copy()->startOfMonth()->format('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | 2. Tentukan scope unit berdasarkan role
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('admin')) {
            $units = Unit::select('id', 'unit')->get();
        } else {
            // admin unit
            $units = Unit::where('id', $user->unit_id)
                ->select('id', 'unit')
                ->get();
        }
        $unitIds = $units->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | 3. Cek record yang sudah ada
        |--------------------------------------------------------------------------
        */

        $existing = RekapBulanan::whereDate('date', $tanggal)
            ->whereIn('unit_id', $unitIds)
            ->pluck('unit_id')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | 4. Cari unit yang belum punya record
        |--------------------------------------------------------------------------
        */

        $missingUnits = $units->filter(fn($u) => !in_array($u->id, $existing));

        /*
        |--------------------------------------------------------------------------
        | 5. Generate jika ada yang belum ada
        |--------------------------------------------------------------------------
        */

        if ($missingUnits->count()) {

            $insertData = [];

            foreach ($missingUnits as $unit) {

                $insertData[] = [
                    'id' => $unit->id . '_' . $tanggal,
                    'date' => $tanggal,
                    'unit_id' => $unit->id,
                    'status' => 'open',
                    'remark' => null,
                    'user' => $actor,
                    'ip_address' => $request->ip(),
                    'queue' => 0,
                    'sub_unit_id' => null,
                    'status_kepegawaian' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('tbl_rekap_list')->insert($insertData);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Load data final
        |--------------------------------------------------------------------------
        */

        $query = RekapBulanan::with('unit')
            ->whereDate('date', $tanggal)
            ->whereIn('unit_id', $unitIds)
            ->orderByDesc('date')
            ->orderBy('unit_id');

        if ($unitFilter) {
            $query->where('unit_id', $unitFilter);
        }

        return inertia('Rekap/RekapBulanan', [
            'rekaps' => $query->paginate(25)->withQueryString(),
            'units' => $units,
            'subUnitsGrouped' => SubUnit::all()->groupBy('unit_id'),
            'statusPegawais' => StatusPegawai::query()
                ->select('id', 'code', 'label')
                ->orderBy('id')
                ->get(),
            'filters' => [
                'bulan' => $bulan->format('Y-m'),
                'unit' => $unitFilter ? (string) $unitFilter : ''
            ]
        ]);
    }

    public function print(Request $request)
    {
        $service = new RekapBulananService();

        $validated = $request->validate([
            'unit_id' => ['nullable', 'integer'],
            'sub_unit_id' => ['nullable', 'integer'],
            'bulan' => ['required', 'date_format:Y-m'],
            'status_pegawai' => ['nullable', 'string'],
            'nik' => ['nullable', 'string', 'max:30'],
        ]);

        $nik = isset($validated['nik']) && trim($validated['nik']) !== ''
            ? trim($validated['nik'])
            : null;

        if (!$nik && empty($validated['unit_id'])) {
            return back()->withErrors([
                'unit_id' => 'Unit wajib diisi jika parameter nik tidak diberikan.',
            ]);
        }

        $unitId = !empty($validated['unit_id']) ? (int) $validated['unit_id'] : null;
        $subUnitId = !empty($validated['sub_unit_id']) ? (int) $validated['sub_unit_id'] : null;
        $statusPegawai = isset($validated['status_pegawai']) && trim($validated['status_pegawai']) !== ''
            ? trim($validated['status_pegawai'])
            : null;

        $bulan = Carbon::createFromFormat('Y-m', $validated['bulan']);
        $dt1 = $bulan->copy()->startOfMonth()->toDateString();
        $dt2 = $bulan->copy()->endOfMonth()->toDateString();
        $unitName = $unitId
            ? (Unit::whereKey($unitId)->value('unit') ?? ('Unit ' . $unitId))
            : '-';
        if ($nik) {
            $pegawaiLabel = \App\Models\MasterPegawai::query()
                ->where('nik', $nik)
                ->value('nama');
            $unitName = 'NIK ' . $nik . ($pegawaiLabel ? (' - ' . $pegawaiLabel) : '');
            $subUnitId = null;
        }
        $statusMeta = null;
        if ($statusPegawai) {
            $statusMeta = StatusPegawai::query()
                ->whereRaw('LOWER(TRIM(code)) = ?', [strtolower(trim($statusPegawai))])
                ->first();
        }
        $statusLabel = $statusMeta?->label ?: ($statusPegawai ? strtoupper($statusPegawai) : '-');
        $periodeLabel = Carbon::parse($dt1)->format('d M') . ' s/d ' . Carbon::parse($dt2)->format('d M Y');

        $rows = $service->generate(
            $unitId,
            $subUnitId,
            $dt1,
            $dt2,
            $statusPegawai,
            $nik
        );

        /*
    | Generate list tanggal header
    */
        $dates = [];

        $start = Carbon::parse($dt1);
        $end   = Carbon::parse($dt2);

        for ($d = $start->copy(); $d <= $end; $d->addDay()) {

            if ($d->isWeekend()) {
                continue;
            }

            $dates[] = $d->toDateString();
        }

        $libur = \App\Models\HariLiburNasional::whereBetween('date', [$dt1, $dt2])
            ->pluck('date')
            ->flip();

        // dd($rows);

        return view('rekap.bulanan', [
            'rows'  => $rows,
            'dates' => $dates,
            'libur' => $libur,
            'start' => $dt1,
            'end' => $dt2,
            'unitName' => $unitName,
            'statusLabel' => $statusLabel,
            'periodeLabel' => $periodeLabel,
        ]);
    }
}
