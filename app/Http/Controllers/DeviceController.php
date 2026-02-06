<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $devices = Device::query()
            ->when($request->search, function ($q, $search) {
                $q->where('device_id', 'like', "%$search%")
                    ->orWhere('desc', 'like', "%$search%")
                    ->orWhere('unit_id', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10) // WAJIB paginate
            ->withQueryString();

        return Inertia::render('Device/Index', [
            'devices' => $devices,
            'filters' => $request->only('search'),
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|unique:devices',
            'unit_id' => 'required',
            'desc' => 'required',
        ]);

        Device::create($request->all());
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'device_id' => 'required|unique:devices,device_id,' . $device->id,
            'unit_id' => 'required',
            'desc' => 'required',
        ]);

        $device->update($request->all());
    }

    public function destroy(Device $device)
    {
        $device->delete();
    }
}
