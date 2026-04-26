<?php

namespace App\Http\Controllers;

use App\Models\Service;

class PublicServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->get();

        return response()->json(['data' => $services]);
    }
}
