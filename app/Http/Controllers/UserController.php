<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use App\Models\SubUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['unit', 'subUnit','roles']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return Inertia::render('User/Index', [
            'users' => $query->orderBy('name')
                ->paginate(10)
                ->withQueryString(),

            'filters' => [
                'search' => $request->search,
                'units' => Unit::orderBy('unit')->get(['id', 'unit']),
                'sub_units' => SubUnit::orderBy('sub_unit')->get(['id', 'sub_unit', 'unit_id']),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'unit_id' => 'nullable|exists:units,id',
            'sub_unit_id' => 'nullable|exists:sub_units,id',
            'role' => 'required|string'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'unit_id' => $data['unit_id'],
            'sub_unit_id' => $data['sub_unit_id'],
        ]);

        $user->assignRole($data['role']);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'password' => 'nullable|min:6',
            'unit_id' => 'nullable|exists:units,id',
            'sub_unit_id' => 'nullable|exists:sub_units,id',
            'role' => 'required|string'
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'unit_id' => $data['unit_id'],
            'sub_unit_id' => $data['sub_unit_id'],
        ]);

        if (!empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password'])
            ]);
        }

        $user->syncRoles([$data['role']]);
    }

    public function destroy(User $user)
    {
        $user->delete();
    }
}
