<?php

namespace App\Http\Controllers;

use App\Models\JadwalDinas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class JamKerjaDinasController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $jadwals = JadwalDinas::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->orWhere('hari', (int) $search);
                    }

                    $q->orWhere('jam_masuk', 'like', "%{$search}%")
                        ->orWhere('jam_pulang', 'like', "%{$search}%")
                        ->orWhere('start_date', 'like', "%{$search}%")
                        ->orWhere('end_date', 'like', "%{$search}%");
                });
            })
            ->orderBy('start_date')
            ->orderBy('hari')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('JamKerja/Dinas/Index', [
            'jadwals' => $jadwals,
            'filters' => [
                'search' => $search,
            ],
            'hariOptions' => $this->hariOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if ($this->hasPeriodeConflict($data)) {
            return back()
                ->withErrors([
                    'hari' => 'Jadwal bentrok: hari dan periode tanggal sudah dipakai.',
                ])
                ->withInput();
        }

        JadwalDinas::create($data);

        return back()->with('success', 'Jadwal dinas berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalDinas $jadwalDinas)
    {
        $data = $this->validatePayload($request);

        if ($this->hasPeriodeConflict($data, $jadwalDinas->id)) {
            return back()
                ->withErrors([
                    'hari' => 'Jadwal bentrok: hari dan periode tanggal sudah dipakai.',
                ])
                ->withInput();
        }

        $jadwalDinas->update($data);

        return back()->with('success', 'Jadwal dinas berhasil diperbarui.');
    }

    public function destroy(JadwalDinas $jadwalDinas)
    {
        $jadwalDinas->delete();

        return back()->with('success', 'Jadwal dinas berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
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

        return JadwalDinas::query()
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
