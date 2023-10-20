<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\User;
use App\Models\Asset;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Request as AssetRequest;
use App\Http\Resources\PurchaseResource;
use App\Http\Resources\PurchaseListResource;

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
        if (!$assetRequest) return response()->json(['message' => 'Request tidak ditemukan'], 404);
        if ($assetRequest->status->status != 'Approved') return response()->json(['message' => 'Request Belum disetujui'], 401);

        $purchase = $assetRequest->purchases()->create([
            'purchased_by' => $user->id,
            'purchased_from' => $request->purchased_from,
        ]);

        $items = $request->items;
        $totalPrice = 0;

        foreach ($items as $item) {
            $purchase_item = $purchase->items()->create($item);
            $totalPrice += $purchase_item->total_price;
        }

        $purchase->update(['total_price' => $totalPrice,]);
        $assetRequest->update([
            'status_id' => 3
        ]);

        return response()->json(['message' => 'Purchase request terbuat'], 201);
    }

    public function getPurchases()
    {
        $access = (auth()->user()->role->asset_approval || auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);
        $purchases = Purchase::orderBy('status_id', 'asc')->orderBy('created_at', 'asc')->paginate(10);

        return PurchaseListResource::collection($purchases);
    }

    public function cancelPurchase(Request $request): JsonResponse
    {
        $access = (auth()->user()->role->asset_approval || auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $request->validate([
            'id' => 'required|integer'
        ]);

        $purchase = Purchase::whereId($request->id)->first();
        if (!$purchase) return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);

        $request = $purchase->request;

        $purchase->update([
            'status_id' => 4
        ]);

        $request->update([
            'status_id' => 2
        ]);

        return response()->json(['message' => 'berhasil'], 200);
    }

    public function testPdf()
    {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('purchase_document'));

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A5', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $pdf = $dompdf->stream();
    }

    public function recivePurchaseItems(Request $request): JsonResponse
    {
        $access = (auth()->user()->role->asset_approval || auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $request->validate([
            'owner_id' => 'required|string',
            'serial_number' => 'required|string',
            'location_id' => 'required|integer'
        ]);

        $purchase = Purchase::with('items')->find($request->purchase_id);

        if (!$purchase) return response()->json(['message' => 'Data purchase tidak ditemukan'], 404);
        

        $isExisted = $purchase->items->contains('model', $request->model);

        if (!$isExisted) return response()->json(['message' => 'Item tidak ditemukan pada data purchase'], 404);
        

        $assetModelCount = $purchase->assets->where('model', $request->model)->count();
        $purchaseModelCount = $purchase->items->where('model', $request->model)->first()->amount;

        if ($assetModelCount >= $purchaseModelCount) return response()->json(['message' => 'Jumlah Aset dengan Model tersebut sudah melebihi jumlah pembelian'], 400);

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
}
