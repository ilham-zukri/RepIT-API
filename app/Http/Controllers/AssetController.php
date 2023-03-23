<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssetResource;
use App\Http\Resources\UserResource;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function myAssets()
    {
        $assets = Asset::where('owner_id', auth()->user()->id)->get();
        return ['assets' => [AssetResource::collection($assets->loadMissing(['location:id,name']))]];
    }
}
