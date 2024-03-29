<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dompdf\Dompdf;
use App\Models\User;
use App\Models\Asset;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Exports\PurchaseExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\AssetResource;
use App\Models\Request as AssetRequest;
use Illuminate\Support\Facades\Storage;
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
            'description' => 'required|string',
            'items' => 'required|array'
        ]);

        $assetRequest = AssetRequest::where('id', $request->request_id)->first();
        if (!$assetRequest) return response()->json(['message' => 'Request tidak ditemukan'], 404);
        if ($assetRequest->status_id < 2) return response()->json(['message' => 'Request Belum disetujui'], 401);
        if ($assetRequest->status_id > 2) return response()->json(['message' => 'Request sudah dibuatkan pembelian atau sudah dibatalkan'], 401);

        $purchase = $assetRequest->purchases()->create([
            'purchased_by' => $user->id,
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
        $assetRequest->update([
            'status_id' => 3
        ]);

        return $this->generatePurchaseDocument($purchase->id);

        // return response()->json(['message' => 'Purchase request terbuat'], 201);
    }

    protected function generatePurchaseDocument($purchaseId)
    {

        $purchase = Purchase::query()
            ->select('id', 'purchased_by', 'total_price', 'created_at', 'purchased_from', 'description')
            ->with(['items' => function ($query) {
                $query->select('id', 'brand', 'model', 'amount', 'price_ea', 'total_price', 'purchase_id');
            }, 'buyer:id,full_name'],)
            ->where('id', $purchaseId)
            ->first();

        if (!$purchase) return response()->json(['message' => 'data purchase tidak ditemukan'], 404);

        $date = Carbon::parse($purchase->created_at)->format('d F y');

        $data = $purchase->toArray();
        $data['created_at'] = $date;

        $pdfPath = public_path('purchase-documents');

        Pdf::loadView('purchase_document', [
            'purchase' => $data
        ])->setPaper('a5', 'landscape')->save($pdfPath . '/' . $purchase->id . '.pdf');

        $pdfFullPath = $pdfPath . '/' . $purchase->id . '.pdf';

        $purchase->update([
            'doc_path' => $pdfFullPath
        ]);

        return response()->json(['message' => 'Berhasil Terbuat'], 201);
    }

    public function getPurchases(Request $request)
    {
        $access = (auth()->user()->role->asset_approval || auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);
        $purchasesQ = Purchase::orderBy('status_id', 'asc')
            ->orderBy('created_at', 'asc');

        if($request->query('search_param')){
            $purchasesQ = Purchase::search($request->query('search_param'));
        }
        
        $purchases = $purchasesQ->paginate(10);

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

    public function receivePurchase(Request $request): JsonResponse
    {
        $access = (auth()->user()->role->asset_approval || auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $request->validate([
            'id' => 'required|integer'
        ]);

        $purchase = Purchase::find($request->id);

        if (!$purchase) return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);

        $purchase->update([
            'status_id' => 2
        ]);

        return response()->json(['message' => 'berhasil'], 200);
    }

    public function getReceivedPurchases()
    {
        $access = (auth()->user()->role->asset_approval || auth()->user()->role->asset_purchasing);
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $purchases = Purchase::where('status_id', 2)->paginate(10);

        if (!$purchases->first()) return response()->json(['message' => 'Belum ada pembelian yang diterima'], 404);

        return PurchaseListResource::collection($purchases);
    }

    public function getPurchasedAssets(Request $request)
    {
        $access = auth()->user()->role->asset_purchasing;
        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $assets = Asset::where('purchase_id', $request->id)->get();
        if (!$assets->first()) return response()->json(['message' => 'Belum ada asset yang diterima'], 404);

        return AssetResource::collection($assets);
    }

    public function exportPurchasesReport(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m'
        ]);

        $access = auth()->user()->role->asset_management;

        if (!$access) return response()->json(['message' => 'Tidak berwenang'], 403);

        $purchases = Purchase::select('request_id', 'purchased_by', 'purchased_from', 'total_price', 'status_id', 'description', 'created_at')
            ->whereYear('created_at', Carbon::parse($request->month)->year)
            ->whereMonth('created_at', Carbon::parse($request->month)->month)
            ->get();
        
        if (!$purchases->first()) return response()->json(['message' => 'Belum ada data'], 404);

        $date = Carbon::now()->format('d-m-Y');
        $path = 'reports/'.$date.'_purchases_' . $request->month . '.xlsx';

        Excel::store(new PurchaseExport($purchases), $path, 'real_public');

       return response()->json(['message' => 'Berhasil', 'path' => $path], 201);
    }
}
