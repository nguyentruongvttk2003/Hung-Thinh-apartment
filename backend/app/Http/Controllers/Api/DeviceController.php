<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::query();
        
        // Filter by category if provided
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }
        
        // Filter by status if provided
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('device_code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        $devices = $query->latest()->paginate($request->per_page ?? 20);
        return response()->json($devices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_code' => 'required|string|max:255|unique:devices',
            'category' => 'required|in:elevator,generator,water_pump,air_conditioner,lighting,security,other',
            'location' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'installation_date' => 'required|date',
            'warranty_expiry' => 'nullable|date',
            'status' => 'required|in:active,inactive,maintenance,broken',
            'specifications' => 'nullable|string',
            'notes' => 'nullable|string',
            'responsible_technician' => 'nullable|exists:users,id'
        ]);
        
        $device = Device::create($validated);
        
        return response()->json($device, 201);
    }

    public function show($id)
    {
        $device = Device::findOrFail($id);
        return response()->json($device);
    }

    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_code' => ['required', 'string', 'max:255', Rule::unique('devices')->ignore($device->id)],
            'category' => 'required|in:elevator,generator,water_pump,air_conditioner,lighting,security,other',
            'location' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'installation_date' => 'required|date',
            'warranty_expiry' => 'nullable|date',
            'status' => 'required|in:active,inactive,maintenance,broken',
            'specifications' => 'nullable|string',
            'notes' => 'nullable|string',
            'responsible_technician' => 'nullable|exists:users,id'
        ]);
        
        $device->update($validated);
        
        return response()->json($device);
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();
        
        return response()->json(['message' => 'Device deleted successfully']);
    }

    public function byCategory($category)
    {
        $devices = Device::where('category', $category)->get();
        return response()->json($devices);
    }
}
