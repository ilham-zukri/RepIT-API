<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SparePartRequest;
use App\Models\SparePartPurchase;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SparePartPurchaseResource;

class SparePartPurchaseController extends Controller
{
    public function makePurchase(Request $request): JsonResponse
    {
        $user = User::where('id', auth()->user()->id)->first();
        $access = $user->role->asset_purchasing;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'request_id' => 'required|integer',
            'purchased_from' => 'required|string',
            'description' => 'required|string',
            'items' => 'required|array'
        ]);

        $sparePartRequest = SparePartRequest::find($request->request_id);
        if (!$sparePartRequest) return response()->json(['message' => 'Request tidak ditemukan'], 404);
        if ($sparePartRequest->status_id < 2) return response()->json(['message' => 'Request Belum disetujui'], 401);
        if ($sparePartRequest->status_id > 2) return response()->json(['message' => 'Request sudah dibuatkan pembelian atau sudah dibatalkan'], 401);

        $purchase = $sparePartRequest->purchase()->create([
            'purchased_by_id' => $user->id,
            'description' => $request->description,
            'purchased_from' => $request->purchased_from,
        ]);

        $items = $request->items;
        $totalPrice = 0;

        foreach ($items as $item) {
            $purchase_item = $purchase->items()->create($item);
            $totalPrice += $purchase_item->total_price;
        }

        $purchase->update(['total_price' => $totalPrice,]);
        $sparePartRequest->update([
            'status_id' => 3
        ]);

        return $this->generatePurchaseDocument($purchase->id);
    }

    protected function generatePurchaseDocument($purchaseId): JsonResponse
    {
        $sparepartPurchase = SparePartPurchase::query()
            ->select('id', 'purchased_by_id', 'total_price', 'created_at', 'purchased_from', 'description')
            ->with(['items' => function ($query) {
                $query->select('id', 'brand', 'model', 'amount', 'price_ea', 'total_price', 'purchase_id');
            }, 'buyer:id,full_name'],)
            ->where('id', $purchaseId)
            ->first();

        if (!$sparepartPurchase) return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);

        $date = Carbon::parse($sparepartPurchase->created_at)->format('d F y');

        $data = $sparepartPurchase->toArray();
        $data['created_at'] = $date;

        $pdfPath = public_path('purchase-documents');

        Pdf::loadView('purchase_document', [
            'purchase' => $data
        ])->setPaper('a5', 'landscape')->save($pdfPath . '/' . 'spare-part-' . $sparepartPurchase->id . '.pdf');

        $pdfFullPath = $pdfPath . '/' . 'spare-part-' . $sparepartPurchase->id . '.pdf';

        $sparepartPurchase->update(['doc_path' => $pdfFullPath]);

        return response()->json(['message' => 'Berhasil Terbuat'], 201);
    }

    public function getPurchases()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $access = $user->role->asset_purchasing;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $purchases = SparePartPurchase::orderBy('status_id', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return SparePartPurchaseResource::collection($purchases);
    }

    public function cancelPurchase(Request $request): JsonResponse
    {
        $access = (auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $request->validate([
            'id' => 'required|integer'
        ]);

        $purchase = SparePartPurchase::whereId($request->id)->first();
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

    public function receivePurchase(Request $request): JsonResponse
    {
        $access = (auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $request->validate([
            'id' => 'required|integer'
        ]);

        $purchase = SparePartPurchase::find($request->id);

        if (!$purchase) return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);

        $purchase->update([
            'status_id' => 2
        ]);

        return response()->json(['message' => 'berhasil'], 200);
    }

    public function getReceivedPurchases()
    {
        $access = (auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $purchases = SparePartPurchase::where('status_id', 2)->paginate(10);

        if (!$purchases->first()) return response()->json(['message' => 'Belum ada pembelian yang diterima'], 404);

        return SparePartPurchaseResource::collection($purchases);
    }
}
