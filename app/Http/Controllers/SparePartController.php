<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\SparePartPurchase;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SparePartController extends Controller
{
    public function makeSparePart(Request $request): JsonResponse
    {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'Tidak Berwenang'], 403);

        if (!$request->purchase_id) {
            $request->validate([
                'type_id' => 'required|integer',
                'brand' => 'required|string',
                'model' => 'required|model',
                'serial_number' => 'required|string',
            ]);

            $sparePartData = $request->all();
            $qrUuid = Str::uuid();
            $qrCode = QrCode::format('png')->merge('/storage/app/img/sabar.jpg', .3)->margin(0)->size(300)->generate($qrUuid);
            $qrCodePath = 'public/qrcodes/' . $qrUuid . '.png';
            Storage::put($qrCodePath, $qrCode);

            $qrCodeUrl = Storage::url($qrCodePath);
            $sparePartData['qr_path'] = $qrCodeUrl;

            SparePart::create($sparePartData);
        } else {
            $request->validate([
                'purchase_id' => 'required|integer',
                'items' => 'required|array',
                'items.*.type_id' => 'required|integer',
            ]);

            $purchase = SparePartPurchase::with('items')->find($request->purchase_id);

            if (!$purchase) {
                return response()->json(['message' => 'Data purchase tidak ditemukan'], 404);
            }

            if ($purchase->status_id <   2) return response()->json(['message' => 'pembelian belum diterima'], 403);

            if ($purchase->status_id > 2) return response()->json(['message' => 'Asset untuk pembelian ini sudah diterima atau dibatalkan'], 403);

            $items = $request->items;

            if (!$items[0]) return response()->json(['message' => 'Item tidak boleh kosong'], 400);

            foreach ($items as $item) {
                $modelToCount = $item['model'];

                $isExisted = $purchase->items->contains('model', $modelToCount);

                if (!$isExisted) {
                    return response()->json(['message' => "Item {$modelToCount} tidak ditemukan pada data purchase"], 404);
                }

                $collection = collect($items);

                $purchaseItemCount = $purchase->items->where('model', $item['model'])->first()->amount;

                $count = $collection->filter(function ($item) use ($modelToCount) {
                    return $item['model'] === $modelToCount;
                })->count();

                if ($count > $purchaseItemCount) {
                    return response()->json(['message' => "jumlah item untuk model {$modelToCount} melebihi pembelian"], 400);
                } elseif ($count < $purchaseItemCount) {
                    return response()->json(['message' => "jumlah item untuk model {$modelToCount} kurang dari pembelian"], 400);
                }
            }

            foreach ($items as $item) {
                $qrUuid = Str::uuid();
                $qrCode = QrCode::format('png')->merge('/storage/app/img/sabar.jpg', .3)->margin(0)->size(300)->generate($qrUuid);
                $qrCodePath = 'public/qrcodes/' . $qrUuid . '.png';
                Storage::put($qrCodePath, $qrCode);
                $qrCodeUrl = Storage::url($qrCodePath);

                $item['qr_path'] = $qrCodeUrl;

                $purchase->spareParts()->create($item);
            }

            $purchase->update([
                'status_id' => 3
            ]);
        }

        return response()->json(["message" => "Berhasil Menambahkan Spare Part"], 201);
    }

    public function getAllSpareParts() {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'Tidak Berwenang'], 403);

        $spareParts = SparePart::paginate(10);

        return $spareParts;
    }
}
