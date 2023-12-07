<?php

namespace App\Http\Controllers;

use App\Http\Resources\PerformanceResource;
use App\Models\Performance;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function getPerformances(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $performances = Performance::orderBy('created_at', 'desc')->paginate(10);

        return PerformanceResource::collection($performances);
    }
}
