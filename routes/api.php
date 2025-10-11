<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\BillingPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientIntakeController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Health check route
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'success' => true,
            'message' => 'API is running successfully',
            'timestamp' => now(),
            'database' => 'Connected'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'API is running but database connection failed',
            'timestamp' => now(),
            'database' => 'Disconnected',
            'error' => $e->getMessage()
        ], 500);
    }
});
// Authentication Routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Patient Intake Routes
    Route::get('/patient-intake/next-enrollment-id', [PatientIntakeController::class, 'getNextEnrollmentId']);
    Route::apiResource('patient-intake', PatientIntakeController::class);

    // Billing Payments Routes
    Route::apiResource('billing-payments', BillingPaymentController::class);
    

    // Audit Trail Routes
    Route::get('/audit-trail', [AuditTrailController::class, 'index']);
    Route::get('/audit-trail/{id}', [AuditTrailController::class, 'show']);
    Route::put('/audit-trail/{id}', [AuditTrailController::class, 'update']);
    Route::get('/audit-trail/export', [AuditTrailController::class, 'export']);

    // Dashboard Routes
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);
});

// Handle preflight requests
Route::options('/{any}', function () {
    return response()->json();
})->where('any', '.*');