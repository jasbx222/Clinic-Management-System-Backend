<?php

namespace App\Http\Controllers;

use App\Models\User;

class PublicDoctorController extends Controller
{
    public function index()
    {
        $doctors = User::where('role', 'doctor')
            ->where('is_active', true)
            ->with('doctorProfile')
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'specialty' => $doc->doctorProfile->specialization ?? 'عام',
                    'bio' => $doc->doctorProfile->bio ?? '',
                    'consultation_fee' => $doc->doctorProfile->consultation_fee ?? 0,
                    'appointment_duration' => $doc->doctorProfile->appointment_duration ?? 30,
                    'image_url' => null,
                    'is_active' => true,
                ];
            });

        return response()->json(['data' => $doctors]);
    }
}
