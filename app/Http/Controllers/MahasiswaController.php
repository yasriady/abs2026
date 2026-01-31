<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MahasiswaExport;
use PDF;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $mahasiswas = Mahasiswa::when($search, function ($q) use ($search) {
            $q->where('nim', 'like', "%$search%")
                ->orWhere('nama', 'like', "%$search%");
        })
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Mahasiswa/Index', [
            'mahasiswas' => $mahasiswas,
            'filters' => [
                'search' => $search
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nim' => 'required|unique:mahasiswas',
            'nama' => 'required',
            'jurusan' => 'required',
            'angkatan' => 'required',
        ]);

        Mahasiswa::create($data);

        return redirect()->back()->with('success', 'Mahasiswa ditambahkan');
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $data = $request->validate([
            'nim' => 'required|unique:mahasiswas,nim,' . $mahasiswa->id,
            'nama' => 'required',
            'jurusan' => 'required',
            'angkatan' => 'required',
        ]);

        $mahasiswa->update($data);

        return redirect()->back()->with('success', 'Mahasiswa diupdate');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();

        return redirect()->back()->with('success', 'Mahasiswa dihapus');
    }

    public function exportExcel()
    {
        return Excel::download(new MahasiswaExport, 'mahasiswa.xlsx');
    }


    public function exportPdf()
    {
        $data = Mahasiswa::all();
        $pdf = PDF::loadView('pdf.mahasiswa', compact('data'));
        return $pdf->download('mahasiswa.pdf');
    }
}
