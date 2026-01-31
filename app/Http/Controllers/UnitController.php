<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $units = Unit::when($search, function ($q) use ($search) {
            $q->where('unit', 'like', "%$search%");
        })
            ->orderBy('id', 'asc')
            ->paginate(250)
            ->withQueryString();

        return Inertia::render('Unit/Index', [
            'units' => $units,
            'filters' => [
                'search' => $search
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit' => 'required|unique:units,unit'
        ]);

        Unit::create($data);

        return redirect()->back()->with('success', 'Unit berhasil ditambahkan');
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'unit' => 'required|unique:units,unit,' . $unit->id
        ]);

        $unit->update($data);

        return redirect()->back()->with('success', 'Unit berhasil diupdate');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->back()->with('success', 'Unit berhasil dihapus');
    }
}
