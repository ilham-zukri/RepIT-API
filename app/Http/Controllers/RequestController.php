<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Request as AssetRequest;

class RequestController extends Controller
{
    public function makeRequest(Request $request){

        $user = User::where('id', auth()->user()->id)->first();

        $access = $user->role->asset_request;

        if(!$access) return response()->json(['message' => 'tidak berwenang'], 403);

        if($request->for_user){
            $forUser = User::whereId($request->for_user)->select('id')->first();
            if(!$forUser) return response()->json(['message' => 'user tidak ditemukan'], 404);
        }

        $user->requests()->create([
            'status' => 'Requested',
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'Low',
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

        $status = ($request->approved) ? 'Approved' : 'Declined';
        
        $assetRequest->update([
            'status' => $status,
            'approved_at' => date('Y-m-d')
        ]);

        return response()->json(['message' => 'berhasil'], 200);
    }

    public function getRequests() {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'Forbidden'], 403);

        $assetRequests = AssetRequest::orderByDesc('priority_id')->paginate(10);

        return response()->json([$assetRequests], 200);
    }
}
