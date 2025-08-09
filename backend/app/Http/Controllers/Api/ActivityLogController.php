<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Get activity logs with filtering
     */
    public function index(Request $request)
    {
        $request->validate([
            'user_id' => 'sometimes|integer|exists:users,id',
            'action' => 'sometimes|string',
            'entity_type' => 'sometimes|string',
            'entity_id' => 'sometimes|integer',
            'ip_address' => 'sometimes|string',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|in:created_at,user_id,action,entity_type',
            'sort_order' => 'sometimes|string|in:asc,desc',
        ]);

        $query = ActivityLog::with(['user']);

        // Apply filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', 'LIKE', "%{$request->action}%");
        }

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->has('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->input('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ]
        ]);
    }

    /**
     * Get activity log by ID
     */
    public function show($id)
    {
        $log = ActivityLog::with(['user'])->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Activity log not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'log' => $log
        ]);
    }

    /**
     * Create activity log
     */
    public function store(Request $request)
    {
        $request->validate([
            'action' => 'required|string|max:255',
            'entity_type' => 'sometimes|string|max:255',
            'entity_id' => 'sometimes|integer',
            'description' => 'sometimes|string',
            'properties' => 'sometimes|array',
        ]);

        $log = ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $request->action,
            'entity_type' => $request->input('entity_type'),
            'entity_id' => $request->input('entity_id'),
            'description' => $request->input('description'),
            'properties' => $request->input('properties', []),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity log created successfully',
            'log' => $log
        ], 201);
    }

    /**
     * Get user's own activity logs
     */
    public function getUserLogs(Request $request)
    {
        $request->validate([
            'action' => 'sometimes|string',
            'entity_type' => 'sometimes|string',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
        ]);

        $query = ActivityLog::where('user_id', Auth::id());

        // Apply filters
        if ($request->has('action')) {
            $query->where('action', 'LIKE', "%{$request->action}%");
        }

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $perPage = $request->input('per_page', 20);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ]
        ]);
    }

    /**
     * Get activity statistics
     */
    public function getStatistics(Request $request)
    {
        $request->validate([
            'period' => 'sometimes|string|in:today,week,month,year',
            'user_id' => 'sometimes|integer|exists:users,id',
        ]);

        $period = $request->input('period', 'month');
        $userId = $request->input('user_id');

        $query = ActivityLog::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Apply period filter
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
            case 'year':
                $query->where('created_at', '>=', Carbon::now()->startOfYear());
                break;
        }

        $stats = [
            'total_activities' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'top_actions' => $query->select('action')
                ->selectRaw('count(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'activities_by_day' => $query->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get(),
            'top_users' => $query->with('user:id,name,email')
                ->select('user_id')
                ->selectRaw('count(*) as count')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'entity_breakdown' => $query->select('entity_type')
                ->selectRaw('count(*) as count')
                ->whereNotNull('entity_type')
                ->groupBy('entity_type')
                ->orderBy('count', 'desc')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'period' => $period,
            'statistics' => $stats
        ]);
    }

    /**
     * Get recent activities for dashboard
     */
    public function getRecentActivities(Request $request)
    {
        $limit = $request->input('limit', 10);
        $userId = $request->input('user_id');

        $query = ActivityLog::with(['user:id,name,email,avatar']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }

    /**
     * Delete old activity logs
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
            'confirm' => 'required|boolean|accepted',
        ]);

        $days = $request->input('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deletedCount} activity logs older than {$days} days",
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->toISOString()
        ]);
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|string|in:csv,json',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'user_id' => 'sometimes|integer|exists:users,id',
            'action' => 'sometimes|string',
        ]);

        $query = ActivityLog::with(['user:id,name,email']);

        // Apply filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', 'LIKE', "%{$request->action}%");
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $logs = $query->orderBy('created_at', 'desc')->get();
        $format = $request->input('format');

        if ($format === 'csv') {
            return $this->exportToCsv($logs);
        } else {
            return $this->exportToJson($logs);
        }
    }

    /**
     * Get activity trends
     */
    public function getTrends(Request $request)
    {
        $request->validate([
            'period' => 'sometimes|string|in:7d,30d,90d,1y',
            'group_by' => 'sometimes|string|in:hour,day,week,month',
        ]);

        $period = $request->input('period', '30d');
        $groupBy = $request->input('group_by', 'day');

        $query = ActivityLog::query();

        // Apply period filter
        switch ($period) {
            case '7d':
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
                break;
            case '30d':
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
                break;
            case '90d':
                $query->where('created_at', '>=', Carbon::now()->subDays(90));
                break;
            case '1y':
                $query->where('created_at', '>=', Carbon::now()->subYear());
                break;
        }

        // Group by specified interval
        switch ($groupBy) {
            case 'hour':
                $dateFormat = '%Y-%m-%d %H:00:00';
                break;
            case 'day':
                $dateFormat = '%Y-%m-%d';
                break;
            case 'week':
                $dateFormat = '%Y-%u';
                break;
            case 'month':
                $dateFormat = '%Y-%m';
                break;
            default:
                $dateFormat = '%Y-%m-%d';
        }

        $trends = $query->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as period, count(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'success' => true,
            'period' => $period,
            'group_by' => $groupBy,
            'trends' => $trends
        ]);
    }

    // Private helper methods

    private function exportToCsv($logs)
    {
        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'User Name', 'User Email', 'Action', 'Entity Type', 
                'Entity ID', 'Description', 'IP Address', 'User Agent', 'Created At'
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'N/A',
                    $log->user ? $log->user->email : 'N/A',
                    $log->action,
                    $log->entity_type,
                    $log->entity_id,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToJson($logs)
    {
        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.json';
        
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->json([
            'export_date' => Carbon::now()->toISOString(),
            'total_records' => $logs->count(),
            'logs' => $logs
        ], 200, $headers);
    }
}
