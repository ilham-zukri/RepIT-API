<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asset;
use App\Models\Purchase;
use App\Models\AssetType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AssetResource;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetController extends Controller
{
    public function makeAsset(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'tidak berwenang'], 200);

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
            $request->validate([
                'purchase_id' => 'required|integer',
                'items' => 'required|array'
            ]);

            $purchase = Purchase::with('items')->find($request->purchase_id);

            if (!$purchase) {
                return response()->json(['message' => 'Data purchase tidak ditemukan'], 404);
            }

            if ($purchase->status_id != 2) return response()->json(['message' => 'pembelian belum diterima'], 403);

            if ($purchase->status_id > 2) return response()->json(['message' => 'Asset untuk pembelian ini sudah diterima atau dibatalkan'], 403);

            $items = $request->items;

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
                $item['owner_id'] = $purchase->request->for_user;
                $item['qr_code'] = Str::uuid();
                $item['deployed_at'] = date('Y-m-d');

                $asset = $purchase->assets()->create($item);
                $qrCode = QrCode::format('png')->merge('/storage/app/img/sabar.jpg', .3)->margin(0)->size(300)->generate($asset->qr_code);

                // Simpan QR Code di direktori publik
                $qrCodePath = 'public/qrcodes/' . $asset->qr_code . '.png';
                Storage::put($qrCodePath, $qrCode);

                // Buat URL untuk QR Code
                $qrCodeUrl = Storage::url($qrCodePath);
                $asset->qrCode()->create([
                    'path' => $qrCodeUrl
                ]);
            }

            $purchase->update([
                'status_id' => 3
            ]);
        }



        return response()->json(['message' => 'Data Aset Telah Dibuat'], 201);
    }

    public function myAssets()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $assets = $user->assets;
        return ['assets' => AssetResource::collection($assets)];
    }

    function acceptAsset(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => 'required|integer'
        ]);

        $user = User::whereId(auth()->user()->id)->select('id')->first();

        $asset = Asset::whereId($request->asset_id)->first();
        if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);
        if ($asset->status_id != 1) return response()->json(['message' => 'Asset Sudah di deploy'], 400);

        if ($user->id != $asset->owner_id) return response()->json(['message' => 'forbidden'], 403);

        $asset->update([
            'status_id' => 2,
            'deployed_at' => date('Y-m-d')
        ]);
        return response()->json(
            [
                'message' => 'Berhasil',
                'status' => $asset->status->status
            ],
            200
        );
    }

    function getAllAssets(Request $request){
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'tidak berwenang'], 200);

        $assets = Asset::paginate(10);

        return AssetResource::collection($assets);
    }
}
