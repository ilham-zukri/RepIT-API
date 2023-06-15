<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\Request as AssetRequest;
use App\Models\User;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{

    public function makePurchaseFromRequest(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        $access = $user->role->asset_purchasing;
        if (!$access) return response()->json(['message' => 'unauthorized'], 401);

        $request->validate([
            'request_id' => 'required|integer',
            'purchased_from' => 'required|string',
            'items' => 'required|array'
        ]);

        $assetRequest = AssetRequest::where('id', $request->request_id)->first();
        if($assetRequest->status != 'Approved') return response()->json(['message' => 'has not approved yet'], 401);

        $purchase = $assetRequest->purchases()->create([
            'purchased_by' => $user->id,
            'purchased_from' => $request->purchased_from,
        ]);

        $items = $request->items;
        $totalPrice = 0;

        foreach ($items as $item){
            $purchase_item = $purchase->items()->create($item);
            $totalPrice += $purchase_item->total_price;
        }

        $purchase->update(['total_price' => $totalPrice,]);
        $assetRequest->update([
            'status' => 'Preparation'
        ]);

        return response()->json(['message' => 'created'], 201);
    }
}
