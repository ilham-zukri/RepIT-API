<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function makeRequest(Request $request){

        $user = User::where('id', auth()->user()->id)->first();

        $assetRequest = $user->requests()->create([
            'status' => 'created',
            'description' => $request->description,
            'priority' => $request->priority ?? 'low'
        ]);

        return response()->json($assetRequest, 201);
    }
}
