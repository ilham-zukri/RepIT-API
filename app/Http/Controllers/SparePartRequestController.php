<?php

namespace App\Http\Controllers;

use App\Http\Resources\SparePartRequestResource;
use App\Models\SparePartRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SparePartRequestController extends Controller
{
    public function makeSparepartRequest(Request $request) : JsonResponse {
        $user = User::whereId($request->user()->id)->first();
        $access = $user->role->asset_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $user->sparePartRequests()->create($request->all());

        return response()->json([
            'message' => 'Berhasil membuat request'
        ], 201);
    }

    public function getSparepartRequests(){
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $sparePartRequests = SparePartRequest::orderBy('status_id', 'asc')->orderBy('created_at', 'asc')->paginate(10);

        return SparePartRequestResource::collection($sparePartRequests);
    }
}
