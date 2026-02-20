<?php

namespace App\Http\Controllers;

use App\Models\JadwalSubUnit;
use App\Models\SubUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class JamKerjaSubUnitController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $unitId = $request->get('unit_id');
        $subUnitId = $request->get('sub_unit_id');
        $startDate = $request->get('start_date');

        $jadwals = JadwalSubUnit::query()
            ->leftJoin('sub_units', 'sub_units.id', '=', 'jadwal_sub_units.sub_unit_id')
            ->leftJoin('units', 'units.id', '=', 'sub_units.unit_id')
            ->select(
                'jadwal_sub_units.*',
                'sub_units.sub_unit as sub_unit_name',
                'sub_units.unit_id as parent_unit_id',
                'units.unit as unit_name'
            )
            ->when($unitId, function ($query) use ($unitId) {
                $query->where('sub_units.unit_id', $unitId);
            })
            ->when($subUnitId, function ($query) use ($subUnitId) {
                $query->where('jadwal_sub_units.sub_unit_id', $subUnitId);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('jadwal_sub_units.start_date', $startDate);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->orWhere('jadwal_sub_units.hari', (int) $search);
                    }

                    $q->orWhere('units.unit', 'like', "%{$search}%")
                        ->orWhere('sub_units.sub_unit', 'like', "%{$search}%")
                        ->orWhere('jadwal_sub_units.jam_masuk', 'like', "%{$search}%")
                        ->orWhere('jadwal_sub_units.jam_pulang', 'like', "%{$search}%")
                        ->orWhere('jadwal_sub_units.start_date', 'like', "%{$search}%")
                        ->orWhere('jadwal_sub_units.end_date', 'like', "%{$search}%");
                });
            })
            ->orderBy('jadwal_sub_units.start_date')
            ->orderBy('jadwal_sub_units.hari')
            ->orderBy('units.unit')
            ->orderBy('sub_units.sub_unit')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('JamKerja/SubUnit/Index', [
            'jadwals' => $jadwals,
            'filters' => [
                'search' => $search,
                'unit_id' => $unitId,
                'sub_unit_id' => $subUnitId,
                'start_date' => $startDate,
            ],
            'hariOptions' => $this->hariOptions(),
            'units' => Unit::query()
                ->orderBy('unit')
                ->get(['id', 'unit']),
            'subUnits' => SubUnit::query()
                ->orderBy('sub_unit')
                ->get(['id', 'unit_id', 'sub_unit']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if ($this->hasPeriodeConflict($data)) {
            return back()
                ->withErrors([
                    'hari' => 'Jadwal bentrok: sub unit, hari, dan periode tanggal sudah dipakai.',
                ])
                ->withInput();
        }

        JadwalSubUnit::create($data);

        return back()->with('success', 'Jadwal sub unit berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalSubUnit $jadwalSubUnit)
    {
        $data = $this->validatePayload($request);

        if ($this->hasPeriodeConflict($data, $jadwalSubUnit->id)) {
            return back()
                ->withErrors([
                    'hari' => 'Jadwal bentrok: sub unit, hari, dan periode tanggal sudah dipakai.',
                ])
                ->withInput();
        }

        $jadwalSubUnit->update($data);

        return back()->with('success', 'Jadwal sub unit berhasil diperbarui.');
    }

    public function destroy(JadwalSubUnit $jadwalSubUnit)
    {
        $jadwalSubUnit->delete();

        return back()->with('success', 'Jadwal sub unit berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'sub_unit_id' => ['required', 'integer', 'exists:sub_units,id'],
            'hari' => ['required', 'integer', 'between:1,7'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
        ]);

        $validator->after(function ($v) use ($request) {
            if ($request->filled('jam_masuk') && $request->filled('jam_pulang')) {
                if ($request->jam_pulang <= $request->jam_masuk) {
                    $v->errors()->add('jam_pulang', 'Jam pulang harus lebih besar dari jam masuk.');
                }
            }
        });

        $data = $validator->validate();
        $data['jam_masuk'] = "{$data['jam_masuk']}:00";
        $data['jam_pulang'] = "{$data['jam_pulang']}:00";

        return $data;
    }

    private function hasPeriodeConflict(array $data, ?int $ignoreId = null): bool
    {
        $newStart = $data['start_date'] ?? '1000-01-01';
        $newEnd = $data['end_date'] ?? '9999-12-31';

        return JadwalSubUnit::query()
            ->where('sub_unit_id', $data['sub_unit_id'])
            ->where('hari', $data['hari'])
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->whereRaw("COALESCE(start_date, '1000-01-01') <= ?", [$newEnd])
            ->whereRaw("COALESCE(end_date, '9999-12-31') >= ?", [$newStart])
            ->exists();
    }

    private function hariOptions(): array
    {
        return [
            ['value' => 1, 'label' => 'Senin'],
            ['value' => 2, 'label' => 'Selasa'],
            ['value' => 3, 'label' => 'Rabu'],
            ['value' => 4, 'label' => 'Kamis'],
            ['value' => 5, 'label' => 'Jumat'],
            ['value' => 6, 'label' => 'Sabtu'],
            ['value' => 7, 'label' => 'Minggu'],
        ];
    }
}
