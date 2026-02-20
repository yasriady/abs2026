<?php

namespace App\Http\Controllers;

use App\Models\JadwalPegawai;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class JamKerjaPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $date = $request->get('date');

        $jadwals = JadwalPegawai::query()
            ->leftJoin('master_pegawais', 'master_pegawais.nik', '=', 'jadwal_pegawais.nik')
            ->select(
                'jadwal_pegawais.*',
                'master_pegawais.nama as pegawai_nama',
                'master_pegawais.nip as pegawai_nip'
            )
            ->when($date, function ($query) use ($date) {
                $query->whereDate('jadwal_pegawais.date', $date);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->orWhere('jadwal_pegawais.nik', 'like', "%{$search}%")
                        ->orWhere('master_pegawais.nama', 'like', "%{$search}%")
                        ->orWhere('master_pegawais.nip', 'like', "%{$search}%")
                        ->orWhere('jadwal_pegawais.jam_masuk', 'like', "%{$search}%")
                        ->orWhere('jadwal_pegawais.jam_pulang', 'like', "%{$search}%")
                        ->orWhere('jadwal_pegawais.date', 'like', "%{$search}%");
                });
            })
            ->orderBy('jadwal_pegawais.date')
            ->orderBy('master_pegawais.nama')
            ->orderBy('jadwal_pegawais.nik')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('JamKerja/Pegawai/Index', [
            'jadwals' => $jadwals,
            'filters' => [
                'search' => $search,
                'date' => $date,
            ],
            'pegawaiHints' => MasterPegawai::query()
                ->orderBy('nama')
                ->limit(500)
                ->get(['nik', 'nama', 'nip']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if ($this->hasDuplicate($data)) {
            return back()
                ->withErrors([
                    'nik' => 'Jadwal pegawai untuk tanggal tersebut sudah ada.',
                ])
                ->withInput();
        }

        JadwalPegawai::create($data);

        return back()->with('success', 'Jadwal pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalPegawai $jadwalPegawai)
    {
        $data = $this->validatePayload($request);

        if ($this->hasDuplicate($data, $jadwalPegawai->id)) {
            return back()
                ->withErrors([
                    'nik' => 'Jadwal pegawai untuk tanggal tersebut sudah ada.',
                ])
                ->withInput();
        }

        $jadwalPegawai->update($data);

        return back()->with('success', 'Jadwal pegawai berhasil diperbarui.');
    }

    public function destroy(JadwalPegawai $jadwalPegawai)
    {
        $jadwalPegawai->delete();

        return back()->with('success', 'Jadwal pegawai berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'nik' => ['required', 'string', 'max:30', 'exists:master_pegawais,nik'],
            'date' => ['required', 'date'],
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
        $data['nik'] = trim((string) $data['nik']);
        $data['jam_masuk'] = "{$data['jam_masuk']}:00";
        $data['jam_pulang'] = "{$data['jam_pulang']}:00";

        return $data;
    }

    private function hasDuplicate(array $data, ?int $ignoreId = null): bool
    {
        return JadwalPegawai::query()
            ->where('nik', $data['nik'])
            ->whereDate('date', $data['date'])
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }
}
