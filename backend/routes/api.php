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
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\HealthController;

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

// Health check endpoints
Route::get('health', [HealthController::class, 'check']);
Route::get('health/database', [HealthController::class, 'database']);

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

// Test JWT authentication
Route::post('test-auth', function (\Illuminate\Http\Request $request) {
    try {
        $credentials = $request->only('email', 'password');
        
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }
        
        $user = auth('api')->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user,
                'jwt_config' => [
                    'secret_exists' => !empty(config('jwt.secret')),
                    'ttl' => config('jwt.ttl'),
                    'algo' => config('jwt.algo')
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
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

// Test endpoints for debugging
Route::get('test-apartments', function () {
    $apartments = \App\Models\Apartment::limit(5)->get();
    return response()->json([
        'success' => true,
        'data' => $apartments,
        'count' => $apartments->count(),
        'total_apartments' => \App\Models\Apartment::count()
    ]);
});

Route::get('test-feedbacks', function () {
    $feedbacks = \App\Models\Feedback::limit(5)->get();
    return response()->json([
        'success' => true,
        'data' => $feedbacks,
        'count' => $feedbacks->count(),
        'total_feedbacks' => \App\Models\Feedback::count()
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
        Route::get('me', [AuthController::class, 'userProfile']); // Add alias for mobile app
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

    // Test authenticated endpoints
    Route::get('test-auth-apartments', function() {
        $apartments = \App\Models\Apartment::with(['owner'])->limit(3)->get();
        return response()->json([
            'success' => true,
            'message' => 'Authenticated apartments test',
            'data' => $apartments,
            'count' => $apartments->count(),
            'user' => auth()->user()->name
        ]);
    });

    Route::get('test-auth-feedbacks', function() {
        $feedbacks = \App\Models\Feedback::with(['user', 'apartment'])->limit(3)->get();
        return response()->json([
            'success' => true,
            'message' => 'Authenticated feedbacks test',
            'data' => $feedbacks,
            'count' => $feedbacks->count(),
            'user' => auth()->user()->name
        ]);
    });

    // Apartments
    Route::apiResource('apartments', ApartmentController::class);
    Route::get('apartments/{apartment}/residents', [ApartmentController::class, 'residents']);
    Route::post('apartments/{apartment}/residents', [ApartmentController::class, 'addResident']);
    Route::delete('apartments/{apartment}/residents/{resident}', [ApartmentController::class, 'removeResident']);

    // Users
    Route::get('users/technicians', [UserController::class, 'technicians']);
    Route::get('users/accountants', [UserController::class, 'accountants']);
    Route::apiResource('users', UserController::class);

    // Notifications
    Route::apiResource('notifications', NotificationController::class);
    Route::post('notifications/{notification}/send', [NotificationController::class, 'send']);
    Route::get('notifications/received', [NotificationController::class, 'received']);
    Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);

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
    Route::get('events/upcoming', [EventController::class, 'upcoming']);
    Route::apiResource('events', EventController::class);

    // Votes
    Route::get('votes/active', [VoteController::class, 'active']);
    Route::post('votes/{vote}/activate', [VoteController::class, 'activate']);
    Route::post('votes/{vote}/close', [VoteController::class, 'close']);
    Route::post('votes/{vote}/vote', [VoteController::class, 'submitVote']);
    Route::get('votes/{vote}/results', [VoteController::class, 'results']);
    Route::apiResource('votes', VoteController::class);

    // Search routes
    Route::get('search/global', [SearchController::class, 'globalSearch']);
    Route::post('search/advanced', [SearchController::class, 'advancedSearch']);
    Route::get('search/suggestions', [SearchController::class, 'getSearchSuggestions']);

    // File upload routes
    Route::post('files/upload', [FileUploadController::class, 'uploadSingle']);
    Route::post('files/upload-multiple', [FileUploadController::class, 'uploadMultiple']);
    Route::post('files/upload-avatar', [FileUploadController::class, 'uploadAvatar']);
    Route::post('files/upload-feedback-attachment', [FileUploadController::class, 'uploadFeedbackAttachment']);
    Route::get('files/info', [FileUploadController::class, 'getFileInfo']);
    Route::delete('files/delete', [FileUploadController::class, 'deleteFile']);
    Route::get('files/list', [FileUploadController::class, 'listFiles']);

    // Activity logs routes
    Route::apiResource('activity-logs', ActivityLogController::class)->except(['update', 'destroy']);
    Route::get('activity-logs/user/mine', [ActivityLogController::class, 'getUserLogs']);
    Route::get('activity-logs/statistics', [ActivityLogController::class, 'getStatistics']);
    Route::get('activity-logs/recent', [ActivityLogController::class, 'getRecentActivities']);
    Route::post('activity-logs/cleanup', [ActivityLogController::class, 'cleanup']);
    Route::get('activity-logs/export', [ActivityLogController::class, 'export']);
    Route::get('activity-logs/trends', [ActivityLogController::class, 'getTrends']);

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