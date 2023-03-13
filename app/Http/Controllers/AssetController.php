<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function myAssets(Request $request)
    {
        $asset = Asset::where('owner_id', $request->owner_id)->get();
        return response()->json($asset);
    }
}
