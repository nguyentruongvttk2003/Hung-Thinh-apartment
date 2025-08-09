<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function check()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            
            // Get some basic stats
            $stats = [
                'users_count' => DB::table('users')->count(),
                'apartments_count' => DB::table('apartments')->count(),
                'notifications_count' => DB::table('notifications')->count(),
                'events_count' => DB::table('events')->count(),
                'feedbacks_count' => DB::table('feedbacks')->count(),
            ];

            return response()->json([
                'status' => 'OK',
                'message' => 'Backend API is running successfully',
                'database' => 'Connected',
                'timestamp' => now()->toISOString(),
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Backend API has issues',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    public function database()
    {
        try {
            $tables = [
                'users' => DB::table('users')->count(),
                'user_roles' => DB::table('user_roles')->count(),
                'apartments' => DB::table('apartments')->count(),
                'residents' => DB::table('residents')->count(),
                'bills' => DB::table('bills')->count(),
                'bill_details' => DB::table('bill_details')->count(),
                'payments' => DB::table('payments')->count(),
                'notifications' => DB::table('notifications')->count(),
                'maintenance_requests' => DB::table('maintenance_requests')->count(),
                'maintenance_schedules' => DB::table('maintenance_schedules')->count(),
                'events' => DB::table('events')->count(),
                'feedbacks' => DB::table('feedbacks')->count(),
                'service_fees' => DB::table('service_fees')->count(),
                'parking_slots' => DB::table('parking_slots')->count(),
                'vehicles' => DB::table('vehicles')->count(),
            ];

            return response()->json([
                'status' => 'OK',
                'message' => 'Database statistics',
                'tables' => $tables,
                'total_records' => array_sum($tables),
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Database check failed',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }
}
