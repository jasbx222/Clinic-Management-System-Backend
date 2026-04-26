<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Visit;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Patient::observe(AuditObserver::class);
        Appointment::observe(AuditObserver::class);
        Visit::observe(AuditObserver::class);
        Prescription::observe(AuditObserver::class);
        Invoice::observe(AuditObserver::class);
        Payment::observe(AuditObserver::class);
    }
}
