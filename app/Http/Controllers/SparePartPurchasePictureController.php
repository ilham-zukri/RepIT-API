<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SparePartPurchase;
use App\Models\SparePartPurchasePicture;

class SparePartPurchasePictureController extends Controller
{
    public function uploadPicture(Request $request){
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'Forbidden'], 403);

        $request->validate([
            'purchase_id' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:6144'
        ]);

        $purchase = SparePartPurchase::find($request->purchase_id);
        if (!$purchase) return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);

        $image = $request->file('image');
        $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('purchases-images'), $imageName);

        $purchase->picture()->create([
            'url' => 'purchases-images/' . $imageName
        ]);
    
        return response()->json([
            'message' => 'Gambar berhasil ditambahkan',
            'url' => 'purchases-images/' . $imageName
        ], 201);
    }
}
