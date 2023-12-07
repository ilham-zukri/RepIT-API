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

    public function updatePriority(Request $request) : JsonResponse {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);
        
        $request->validate([
           'priority_id' => 'required|integer',
           'max_response_time' => 'required|integer',
           'max_resolve_time' => 'required|integer',
        ]);

        $priority = Priority::find($request->priority_id);
        if (!$priority) return response()->json(['message' => 'Prioritas tidak ditemukan'], 404);

        $priority->update([
            'max_response_time' => $request->max_response_time,
            'max_resolve_time' => $request->max_resolve_time
        ]);

        return response()->json(['message' => 'Berhasil Memperbarui Prioritas'], 200);
    }
}
