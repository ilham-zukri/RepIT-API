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

        if(!$access) return response()->json(['message' => 'tidak berwenang'], 401);

        $assetRequest = $user->requests()->create([
            'status' => 'Requested',
            'description' => $request->description,
            'priority' => $request->priority ?? 'Low'
        ]);

        return response()->json($assetRequest, 201);
    }

    public function approveRequest(Request $request) 
    {
        $access = auth()->user()->role->asset_approval;
        if (!$access) return response()->json(['message' => 'unauthorized'], 401);

        $assetRequest = AssetRequest::where('id', $request->request_id)->first();

        $status = ($request->approved) ? 'Approved' : 'Declined';
        
        $assetRequest->update([
            'status' => $status
        ]);

        return response()->json(['message' => 'berhasil'], 200);
    }
}
