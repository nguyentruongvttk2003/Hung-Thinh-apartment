<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApartmentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Test route để debug
Route::get('test-users', function () {
    $users = \App\Models\User::select('id', 'name', 'email', 'password', 'role', 'status')->get();
    return response()->json([
        'users' => $users,
        'count' => $users->count()
    ]);
});

Route::get('test-simple', function () {
    return response()->json([
        'message' => 'API working!',
        'timestamp' => now(),
        'config' => [
            'jwt_secret_exists' => !empty(config('jwt.secret')),
            'database' => config('database.connections.mysql.database'),
            'auth_default_guard' => config('auth.defaults.guard'),
            'auth_api_driver' => config('auth.guards.api.driver'),
        ]
    ]);
});

Route::post('test-login', function (\Illuminate\Http\Request $request) {
    $email = $request->email;
    $password = $request->password;
    
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found', 'email' => $email]);
    }
    
    $passwordCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);
    
    return response()->json([
        'user_found' => true,
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'password_hash' => $user->password,
            'status' => $user->status,
            'role' => $user->role
        ],
        'password_provided' => $password,
        'password_check' => $passwordCheck,
        'auth_attempt' => auth('api')->attempt(['email' => $email, 'password' => $password])
    ]);
});

Route::get('test-login-get', function (\Illuminate\Http\Request $request) {
    // Test với admin user cố định
    $email = 'admin@hungthinh.com';
    $password = 'admin123';
    
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found', 'email' => $email]);
    }
    
    $passwordCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);
    
    try {
        $authAttempt = auth('api')->attempt(['email' => $email, 'password' => $password]);
    } catch (\Exception $e) {
        $authAttempt = false;
        $authError = $e->getMessage();
    }
    
    return response()->json([
        'user_found' => true,
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'password_hash' => substr($user->password, 0, 20) . '...', // Hide full hash
            'status' => $user->status,
            'role' => $user->role
        ],
        'password_provided' => $password,
        'password_check' => $passwordCheck,
        'auth_attempt' => $authAttempt,
        'auth_error' => $authError ?? null,
        'jwt_config' => [
            'secret_length' => strlen(config('jwt.secret')),
            'ttl' => config('jwt.ttl'),
            'algo' => config('jwt.algo')
        ]
    ]);
});

// Temporary test endpoints (outside auth middleware)
Route::get('test-recent-activities', function() {
    return response()->json([
        [
            'type' => 'feedback',
            'title' => 'Phản ánh về điện thoại',
            'description' => 'Cư dân tầng 5 phản ánh về việc mất điện...',
            'created_at' => now()->subHours(2),
        ],
        [
            'type' => 'payment',
            'title' => 'Thanh toán tiền điện',
            'description' => 'Căn hộ A101 - Số tiền: 500,000 VND',
            'created_at' => now()->subHours(5),
        ],
        [
            'type' => 'feedback',
            'title' => 'Phản ánh về thang máy',
            'description' => 'Thang máy số 2 bị kẹt tại tầng 8...',
            'created_at' => now()->subDay(),
        ],
    ]);
});

// Protected routes
Route::group(['middleware' => 'auth:api'], function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('profile', [AuthController::class, 'userProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
    });

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/recent-activities', [DashboardController::class, 'recentActivities']);
    
    // Test dashboard endpoint
    Route::get('dashboard/test', function() {
        return response()->json([
            'message' => 'Dashboard test endpoint working',
            'user' => auth()->user()->name,
            'timestamp' => now()
        ]);
    });

    // Apartments
    Route::apiResource('apartments', ApartmentController::class);
    Route::get('apartments/{apartment}/residents', [ApartmentController::class, 'residents']);
    Route::post('apartments/{apartment}/residents', [ApartmentController::class, 'addResident']);
    Route::delete('apartments/{apartment}/residents/{resident}', [ApartmentController::class, 'removeResident']);

    // Users
    Route::apiResource('users', UserController::class);
    Route::get('users/technicians', [UserController::class, 'technicians']);
    Route::get('users/accountants', [UserController::class, 'accountants']);

    // Notifications
    Route::apiResource('notifications', NotificationController::class);
    Route::post('notifications/{notification}/send', [NotificationController::class, 'send']);
    Route::get('notifications/received', [NotificationController::class, 'received']);
    Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead']);

    // Feedbacks
    Route::apiResource('feedbacks', FeedbackController::class);
    Route::post('feedbacks/{feedback}/assign', [FeedbackController::class, 'assign']);
    Route::post('feedbacks/{feedback}/resolve', [FeedbackController::class, 'resolve']);
    Route::post('feedbacks/{feedback}/rate', [FeedbackController::class, 'rate']);

    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('invoices/apartment/{apartment}', [InvoiceController::class, 'byApartment']);
    Route::post('invoices/bulk-create', [InvoiceController::class, 'bulkCreate']);

    // Payments
    Route::apiResource('payments', PaymentController::class);
    Route::post('payments/{payment}/process', [PaymentController::class, 'process']);
    Route::get('payments/invoice/{invoice}', [PaymentController::class, 'byInvoice']);

    // Devices
    Route::apiResource('devices', DeviceController::class);
    Route::get('devices/category/{category}', [DeviceController::class, 'byCategory']);

    // Maintenances
    Route::apiResource('maintenances', MaintenanceController::class);
    Route::post('maintenances/{maintenance}/start', [MaintenanceController::class, 'start']);
    Route::post('maintenances/{maintenance}/complete', [MaintenanceController::class, 'complete']);
    Route::get('maintenances/device/{device}', [MaintenanceController::class, 'byDevice']);

    // Events
    Route::apiResource('events', EventController::class);
    Route::get('events/upcoming', [EventController::class, 'upcoming']);

    // Votes
    Route::apiResource('votes', VoteController::class);
    Route::post('votes/{vote}/activate', [VoteController::class, 'activate']);
    Route::post('votes/{vote}/close', [VoteController::class, 'close']);
    Route::post('votes/{vote}/vote', [VoteController::class, 'submitVote']);
    Route::get('votes/{vote}/results', [VoteController::class, 'results']);
    Route::get('votes/active', [VoteController::class, 'active']);

    // Admin only routes
    Route::group(['middleware' => 'admin'], function () {
        Route::get('statistics', [DashboardController::class, 'statistics']);
        Route::get('reports/financial', [DashboardController::class, 'financialReport']);
        Route::get('reports/maintenance', [DashboardController::class, 'maintenanceReport']);
    });

    // Resident only routes
    Route::group(['middleware' => 'resident'], function () {
        Route::get('my-apartments', [ApartmentController::class, 'myApartments']);
        Route::get('my-invoices', [InvoiceController::class, 'myInvoices']);
        Route::get('my-payments', [PaymentController::class, 'myPayments']);
    });

    // Technician only routes
    Route::group(['middleware' => 'technician'], function () {
        Route::get('my-assignments', [FeedbackController::class, 'myAssignments']);
        Route::get('my-maintenances', [MaintenanceController::class, 'myMaintenances']);
    });

    // Accountant only routes
    Route::group(['middleware' => 'accountant'], function () {
        Route::get('financial-summary', [DashboardController::class, 'financialSummary']);
        Route::get('outstanding-invoices', [InvoiceController::class, 'outstanding']);
    });
}); 