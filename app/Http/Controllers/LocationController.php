<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getLocations() : JsonResponse {
        $locations = Location::select('id','name')->get();
        return response()->json($locations, 200);
    }
}
