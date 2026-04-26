<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if doctor exists
        $doctor = User::firstOrCreate([
            'email' => 'doctor@example.com',
        ], [
            'name' => 'د. محمد الطبيب',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '0500000001',
        ]);

        DoctorProfile::firstOrCreate([
            'user_id' => $doctor->id,
        ], [
            'specialization' => 'General',
            'consultation_fee' => 100,
            'appointment_duration' => 30,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $patientUser = User::firstOrCreate([
                'email' => "patient{$i}@example.com",
            ], [
                'name' => "مريض {$i}",
                'password' => Hash::make('password'),
                'role' => 'patient',
                'phone' => "050111110{$i}",
            ]);

            $patient = Patient::firstOrCreate([
                'user_id' => $patientUser->id,
            ], [
                'file_number' => "PT-2026-00{$i}",
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'blood_group' => 'O+',
            ]);

            Appointment::firstOrCreate([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => Carbon::today()->format('Y-m-d'),
                'appointment_time' => (9 + $i).':00:00',
            ], [
                'status' => 'pending',
                'reason' => 'فحص روتيني',
            ]);

            Invoice::firstOrCreate([
                'patient_id' => $patient->id,
            ], [
                'subtotal' => 150,
                'tax' => 0,
                'total' => 150,
                'paid_amount' => 150,
                'status' => 'paid',
            ]);
        }
    }
}
