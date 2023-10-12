<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestListResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Request as AssetRequest;

class RequestController extends Controller
{
    public function makeRequest(Request $request)
    {

        $user = User::where('id', auth()->user()->id)->first();

        $access = $user->role->asset_request;

        if (!$access) return response()->json(['message' => 'tidak berwenang'], 403);

        if ($request->for_user) {
            $forUser = User::whereId($request->for_user)->select('id')->first();
            if (!$forUser) return response()->json(['message' => 'user tidak ditemukan'], 404);
        }

        $user->requests()->create([
            'title' => $request->title,
            'description' => $request->description,
            'priority_id' => $request->priority ?? 4,
            'for_user' => $request->for_user ?? auth()->user()->id,
            'location_id' => $request->location_id ?? 1
        ]);

        return response()->json(['message' => 'berhasil'], 201);
    }

    public function approveRequest(Request $request)
    {
        $access = auth()->user()->role->asset_approval;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $assetRequest = AssetRequest::where('id', $request->request_id)->first();

        $statusId = ($request->approved) ? 2 : 5;

        $assetRequest->update([
            'status_id' => $statusId,
            'approved_at' => date('Y-m-d')
        ]);

        return response()->json(
            [
                'message' => 'Berhasil',
                'status' => $assetRequest->status->status
            ],
            200
        );
    }

    public function getRequests(Request $request)
    {
        $access = auth()->user()->role->asset_approval;

        if (auth()->user()->role->asset_purchasing && !$access) {
            $assetRequests = AssetRequest::where('status_id', '>', 1)->orderBy('status_id', 'asc')->orderBy('priority_id', "asc")->orderBy('created_at', 'desc')->paginate(10);
            if (!$assetRequests->first()) return response()->json(['message' => 'Data tidak ditemukan'], 404);

            return RequestListResource::collection($assetRequests);
        }

        if (!$access) return response()->json(['message' => 'Forbidden'], 403);

        $sPriority = ($request->query('priority_sort')) ? $request->query('priority_sort') : 'asc';
        $sStatus = ($request->query('status_sort')) ? $request->query('status_sort') : 'asc';
        $sCreatedAt = $request->query('created_at_sort');
        $fLocation = $request->query('filter_location');

        $assetRequestsQ = AssetRequest::orderBy('status_id', $sStatus)->orderBy('priority_id', $sPriority);

        if ($sCreatedAt) {
            $assetRequestsQ = AssetRequest::orderBy('created_at', $sCreatedAt)->orderBy('priority_id', $sPriority);
        }

        if ($fLocation) {
            $assetRequestsQ = AssetRequest::whereLocationId($fLocation)->orderBy('created_at', 'desc')->orderBy('priority_id', 'asc')->orderBy('status_id', 'asc');
        }


        $assetRequests = $assetRequestsQ->paginate(10);
        $existedData = ($assetRequests->first()) ? true : false;

        if (!$existedData) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        return RequestListResource::collection($assetRequests);
    }
}
