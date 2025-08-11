<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::with(['device', 'technician']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('work_performed', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $maintenances = $query->latest()->paginate($perPage);
        
        return response()->json($maintenances);
    }

    public function store(Request $request)
    {
        $maintenance = Maintenance::create($request->all());
        return response()->json([
            'success' => true,
            'data' => $maintenance,
            'message' => 'Maintenance created successfully'
        ], 201);
    }

    public function show($id)
    {
        $maintenance = Maintenance::with(['device', 'technician'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $maintenance,
            'message' => 'Maintenance retrieved successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update($request->all());
        return response()->json([
            'success' => true,
            'data' => $maintenance,
            'message' => 'Maintenance updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Maintenance deleted successfully'
        ]);
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
        return response()->json([
            'success' => true,
            'data' => $maintenances,
            'message' => 'Device maintenances retrieved successfully'
        ]);
    }

    public function myMaintenances(Request $request)
    {
        $user = auth()->user();
        $query = Maintenance::with(['device', 'technician'])
            ->where('assigned_technician', $user->id);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('work_performed', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $maintenances = $query->latest()->paginate($perPage);
        
        return response()->json($maintenances);
    }
}
