<?php

namespace App\Http\Controllers;

use App\Models\HariLiburNasional;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HariLiburNasionalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $query = HariLiburNasional::query();

        if ($search) {
            $query->where('description', 'like', "%$search%")
                ->orWhere('category', 'like', "%$search%")
                ->orWhere('year', 'like', "%$search%");
        }

        return Inertia::render('HariLiburNasional/Index', [
            'liburs' => $query->orderBy('date', 'desc')->paginate(100)->withQueryString(),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'year' => 'required|digits:4',
            'category' => 'required|string|max:50',
            'description' => 'required|string|max:255',
        ]);

        HariLiburNasional::create($data);
    }

    public function update(Request $request, HariLiburNasional $hariLiburNasional)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'year' => 'required|digits:4',
            'category' => 'required|string|max:50',
            'description' => 'required|string|max:255',
        ]);

        $hariLiburNasional->update($data);
    }

    public function destroy(HariLiburNasional $hariLiburNasional)
    {
        $hariLiburNasional->delete();
    }
}
