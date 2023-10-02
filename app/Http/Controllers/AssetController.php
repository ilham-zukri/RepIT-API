<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function makeAsset(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['messagae' => 'tidak berwenang'], 200);

        $request->validate([
            'owner_id' => 'required|string',
            'serial_number' => 'required|string',
            'location_id' => 'required|integer'
        ]);

        if (!$request->purchase_id) {

            Asset::create([
                'owner_id' => $request->owner_id,
                'asset_type' => $request->asset_type,
                'brand' => $request->brand,
                'model' => $request->model,
                'serial_number' => $request->serial_number,
                'cpu' => $request->cpu ?? '#N/A',
                'ram' => $request->ram ?? '#N/A',
                'utilization' => $request->utilization,
                'location_id' => $request->location_id,
                'status' => 'Ready',
            ]);
            
        } else {

            $purchase = Purchase::with('items')->find($request->purchase_id);

            if (!$purchase) {
                return response()->json(['message' => 'Data purchase tidak ditemukan'], 404);
            }

            $isExisted = $purchase->items->contains('model', $request->model);

            if (!$isExisted) {
                return response()->json(['message' => 'Item tidak ditemukan pada data purchase'], 404);
            }

            $assetModelCount = $purchase->assets->where('model', $request->model)->count();
            $purchaseModelCount = $purchase->items->where('model', $request->model)->first()->amount;

           if($assetModelCount >= $purchaseModelCount) return response()->json(['message'=>'Jumlah Aset dengan Model tersebut sudah melebihi jumlah pembelian'], 400);

            Asset::create([
                'purchase_id' => $request->purchase_id,
                'owner_id' => $request->owner_id,
                'asset_type' => $request->asset_type,
                'brand' => $request->brand,
                'model' => $request->model,
                'serial_number' => $request->serial_number,
                'cpu' => $request->cpu ?? '#N/A',
                'ram' => $request->ram ?? '#N/A',
                'utilization' => $request->utilization,
                'location_id' => $request->location_id,
                'status' => 'Ready',
            ]);
        }

        return response()->json(['message' => 'Data Aset Telah Dibuat'], 201);
    }

    public function myAssets(){
        $user = User::where('id', auth()->user()->id)->first();
        $assets = $user->assets;
        return ['assets' => AssetResource::collection($assets)];
    }

    function acceptAsset(Request $request) : JsonResponse{
        $request->validate([
            'asset_id' => 'required|integer'
        ]);

        $user = User::whereId(auth()->user()->id)->select('id')->first();
        
        $asset = Asset::whereId($request->asset_id)->first();
        if(!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);
        if($asset->status != 'Ready' || 'On Repair') return response()->json(['message'=>'Asset Sudah di deploy'], 400);

        if($user->id != $asset->owner_id) return response()->json(['message'=> 'forbidden'], 403);

        $asset->update([
            'status' => 'Deployed',
            'deployed_at' => date('Y-m-d')  
        ]);
        return response()->json(['message' => 'Berhasil'], 200);
    }
}
