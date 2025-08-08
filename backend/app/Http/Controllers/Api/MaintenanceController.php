<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $maintenances = Maintenance::latest()->paginate(15);
        return response()->json($maintenances);
    }

    public function store(Request $request)
    {
        $maintenance = Maintenance::create($request->all());
        return response()->json($maintenance, 201);
    }

    public function show($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        return response()->json($maintenance);
    }

    public function update(Request $request, $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update($request->all());
        return response()->json($maintenance);
    }

    public function destroy($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();
        return response()->json(['message' => 'Maintenance deleted successfully']);
    }

    public function start($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        return response()->json($maintenance);
    }

    public function complete($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        return response()->json($maintenance);
    }

    public function byDevice($deviceId)
    {
        $maintenances = Maintenance::where('device_id', $deviceId)->latest()->get();
        return response()->json($maintenances);
    }
}
