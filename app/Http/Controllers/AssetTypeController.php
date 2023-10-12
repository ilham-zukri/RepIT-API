<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    public function getAssetTypes() : JsonResponse {
        $assetTypes = AssetType::all();
        return response()->json($assetTypes, 200, );
    }
}
