<?php

use App\Http\Controllers\Api\PatientJourneyController;
use App\Http\Controllers\Api\PublicCheckinController;
// Public Controllers
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\WaitingScreenController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
// Dashboard Controllers
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PublicAppointmentController;
use App\Http\Controllers\PublicAvailabilityController;
use App\Http\Controllers\PublicClinicController;
use App\Http\Controllers\PublicDoctorController;
use App\Http\Controllers\PublicServiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VisitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::prefix('public')->group(function () {
    Route::get('/clinic', [PublicClinicController::class, 'show']);
    Route::get('/services', [PublicServiceController::class, 'index']);
    Route::get('/doctors', [PublicDoctorController::class, 'index']);
    Route::get('/available-slots', [PublicAvailabilityController::class, 'index']);
    Route::post('/appointments', [PublicAppointmentController::class, 'store'])->middleware('throttle:5,1');

    // Contactless Check-in & Waiting Screen
    Route::get('/checkin/{token}', [PublicCheckinController::class, 'show']);
    Route::post('/checkin/{token}/confirm', [PublicCheckinController::class, 'confirm']);
    Route::get('/waiting-screen', [WaitingScreenController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Protected Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard General
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Appointments (Dashboard)
    Route::apiResource('appointments', AppointmentController::class);
    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

    // Patients
    Route::apiResource('patients', PatientController::class);

    // Employees
    Route::apiResource('employees', EmployeeController::class)->parameters([
        'employees' => 'employee',
    ]);

    // Visits (Consultations)
    Route::apiResource('visits', VisitController::class);

    // Prescriptions
    Route::apiResource('prescriptions', PrescriptionController::class);

    // Billing & Invoices
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print']);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('payments', PaymentController::class);

    // Reports
    Route::get('/reports/financial', [ReportController::class, 'financial']);
    Route::get('/reports/appointments', [ReportController::class, 'appointments']);

    // Queue Management
    Route::get('/admin/queue/today', [QueueController::class, 'today']);
    Route::post('/admin/queue/{id}/call', [QueueController::class, 'call']);
    Route::post('/admin/queue/{id}/start', [QueueController::class, 'start']);
    Route::post('/admin/queue/{id}/billing', [QueueController::class, 'billing']);
    Route::post('/admin/queue/{id}/complete', [QueueController::class, 'complete']);
    Route::post('/admin/queue/{id}/skip', [QueueController::class, 'skip']);
    Route::patch('/admin/queue/{id}/priority', [QueueController::class, 'updatePriority']);

    // Patient Journey
    Route::get('/admin/appointments/{appointment}/journey', [PatientJourneyController::class, 'show']);
});
