<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;

class PublicClinicController extends Controller
{
    public function show()
    {
        $setting = ClinicSetting::first();

        return response()->json(['data' => $setting]);
    }
}
