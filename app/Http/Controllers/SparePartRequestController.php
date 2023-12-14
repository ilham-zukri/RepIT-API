<?php

namespace App\Http\Controllers;

use App\Http\Resources\SparePartRequestResource;
use App\Models\SparePartRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SparePartRequestController extends Controller
{
    public function makeSparepartRequest(Request $request): JsonResponse
    {
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

    public function getSparepartRequests(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $sparePartRequestsQ = SparePartRequest::orderBy('status_id', 'asc')->orderBy('created_at', 'asc');
        if ($request->query('search_param')) {
            $sparePartRequestsQ = SparePartRequest::search($request->query('search_param'));
        }

        if (!auth()->user()->role->asset_approval) {
            $sparePartRequestsQ = SparePartRequest::where('status_id', '>', 1)
                ->orderBy('status_id', 'asc')
                ->orderBy('created_at', 'asc');
            
            if ($request->query('search_param')) {
                $sparePartRequestsQ = SparePartRequest::search($request->query('search_param'));
                $requestS = $sparePartRequestsQ->first();
                if($requestS->status_id == 1){
                    return response()->json(['message' => 'data tidak ditemukan '], 404);
                }
            }
            $sparePartRequests = $sparePartRequestsQ->paginate(10);
            return SparePartRequestResource::collection($sparePartRequests);
        }


        $sparePartRequests = $sparePartRequestsQ->paginate(10);
        return SparePartRequestResource::collection($sparePartRequests);

    }

    public function approveSparepartRequest(Request $request): JsonResponse
    {
        $access = auth()->user()->role->asset_approval;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'request_id' => 'required|integer',
            'approved' => 'required|boolean'
        ]);

        $sparePartRequest = SparePartRequest::find($request->request_id);
        if (!$sparePartRequest) return response()->json(['message' => 'Tidak ditemukan'], 404);

        $statusId = ($request->approved) ? 2 : 5;

        $sparePartRequest->update([
            'status_id' => $statusId,
            'approved_at' => now()
        ]);

        return response()->json([
            'message' => 'Berhasil setujui request',
            'data' => [
                'status' => $sparePartRequest->status->status
            ]
        ], 200);
    }
}
