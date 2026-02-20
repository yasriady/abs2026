<?php

namespace App\Http\Controllers;

use App\Models\JadwalUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class JamKerjaUnitController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $jadwals = JadwalUnit::query()
            ->leftJoin('units', 'units.id', '=', 'jadwal_units.unit_id')
            ->select('jadwal_units.*', 'units.unit as unit_name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->orWhere('jadwal_units.hari', (int) $search);
                    }

                    $q->orWhere('units.unit', 'like', "%{$search}%")
                        ->orWhere('jadwal_units.jam_masuk', 'like', "%{$search}%")
                        ->orWhere('jadwal_units.jam_pulang', 'like', "%{$search}%")
                        ->orWhere('jadwal_units.start_date', 'like', "%{$search}%")
                        ->orWhere('jadwal_units.end_date', 'like', "%{$search}%");
                });
            })
            ->orderBy('jadwal_units.start_date')
            ->orderBy('jadwal_units.hari')
            ->orderBy('units.unit')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('JamKerja/Unit/Index', [
            'jadwals' => $jadwals,
            'filters' => [
                'search' => $search,
            ],
            'hariOptions' => $this->hariOptions(),
            'units' => Unit::query()
                ->orderBy('unit')
                ->get(['id', 'unit']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if ($this->hasPeriodeConflict($data)) {
            return back()
                ->withErrors([
                    'hari' => 'Jadwal bentrok: unit, hari, dan periode tanggal sudah dipakai.',
                ])
                ->withInput();
        }

        JadwalUnit::create($data);

        return back()->with('success', 'Jadwal unit berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalUnit $jadwalUnit)
    {
        $data = $this->validatePayload($request);

        if ($this->hasPeriodeConflict($data, $jadwalUnit->id)) {
            return back()
                ->withErrors([
                    'hari' => 'Jadwal bentrok: unit, hari, dan periode tanggal sudah dipakai.',
                ])
                ->withInput();
        }

        $jadwalUnit->update($data);

        return back()->with('success', 'Jadwal unit berhasil diperbarui.');
    }

    public function destroy(JadwalUnit $jadwalUnit)
    {
        $jadwalUnit->delete();

        return back()->with('success', 'Jadwal unit berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => ['required', 'integer', 'exists:units,id'],
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

        return JadwalUnit::query()
            ->where('unit_id', $data['unit_id'])
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
