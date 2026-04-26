<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
// Public Controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
// Dashboard Controllers
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
});
