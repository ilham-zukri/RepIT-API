<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function getPriorities() : JsonResponse {
        return response()->json(Priority::all(), 200);
    }
}
