<?php

namespace App\Http\Controllers;

use App\Models\SubUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubUnitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $subUnits = SubUnit::with('unit')
            ->when($search, function ($q) use ($search) {
                $q->where('sub_unit', 'like', "%$search%");
            })
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('SubUnit/Index', [
            'subUnits' => $subUnits,
            'units' => Unit::orderBy('unit')->get(),
            'filters' => [
                'search' => $search
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|unique:sub_units,id',
            'sub_unit' => 'required',
            'unit_id' => 'required|exists:units,id',
        ]);

        SubUnit::create($data);

        return redirect()->back()->with('success', 'SubUnit ditambahkan');
    }

    public function update(Request $request, SubUnit $subUnit)
    {
        $data = $request->validate([
            'sub_unit' => 'required',
            'unit_id' => 'required|exists:units,id',
        ]);

        $subUnit->update($data);

        return redirect()->back()->with('success', 'SubUnit diupdate');
    }

    public function destroy(SubUnit $subUnit)
    {
        $subUnit->delete();
        return redirect()->back()->with('success', 'SubUnit dihapus');
    }
}
