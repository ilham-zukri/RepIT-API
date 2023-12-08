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
use App\Http\Resources\TicketResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\SparePartResource;
use App\Notifications\SendNotification;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetController extends Controller
{
    public function makeAsset(Request $request)
    {

        if (!$request->purchase_id) {
            $access = auth()->user()->role->asset_management;
            if (!$access) return response()->json(['message' => 'tidak berwenang'], 403);

            $request->validate([
                'owner_id' => 'required|uuid',
                'asset_type' => 'required|string',
                'brand' => 'required|string',
                'serial_number' => 'required|string',
                'utilization' => 'required|string',
            ]);

            $owner = User::find($request->owner_id);
            if (!$owner) return response()->json(['message' => 'Owner tidak ditemukan'], 404);

            $asset = Asset::create([
                'owner_id' => $request->owner_id,
                'asset_type' => $request->asset_type,
                'brand' => $request->brand,
                'model' => $request->model,
                'serial_number' => $request->serial_number,
                'cpu' => $request->cpu ?? '#N/A',
                'ram' => $request->ram ?? '#N/A',
                'utilization' => $request->utilization,
                'qr_code' => Str::uuid(),
                'location_id' => $owner->branch_id,
                'status_id' => 1,
            ]);

            $name = 'SM-' . $asset->created_at->year . '-' . $asset->created_at->month . '-' . $asset->id;

            $asset->update([
                'name' => $name
            ]);

            $qrCode = QrCode::format('png')->merge('/storage/app/img/sabar.jpg', .1)->margin(0)->size(300)->generate($asset->qr_code);

            // Simpan QR Code di direktori publik
            $qrCodePath = 'public/qrcodes/' . $asset->qr_code . '.png';
            Storage::put($qrCodePath, $qrCode);

            // Buat URL untuk QR Code
            $qrCodeUrl = Storage::url($qrCodePath);
            $asset->qrCode()->create([
                'path' => $qrCodeUrl
            ]);
        } else {
            $access = auth()->user()->role->asset_management;
            if (!$access) return response()->json(['message' => 'tidak berwenang'], 403);
            $request->validate([
                'purchase_id' => 'required|integer',
                'items' => 'required|array'
            ]);

            $purchase = Purchase::with('items')->find($request->purchase_id);

            if (!$purchase) {
                return response()->json(['message' => 'Data purchase tidak ditemukan'], 404);
            }

            if ($purchase->status_id <   2) return response()->json(['message' => 'pembelian belum diterima'], 403);

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
                $item['location_id'] = $purchase->request->location_id;
                $item['cpu'] = $item['cpu'] ?? '#N/A';
                $item['ram'] = $item['ram'] ?? '#N/A';

                $asset = $purchase->assets()->create($item);

                $name = 'SM-' . $asset->created_at->year . '-' . $asset->created_at->month . '-' . $asset->id;

                $asset->update([
                    'name' => $name
                ]);

                $qrCode = QrCode::format('png')->merge('/storage/app/img/sabar.jpg', .1)->margin(0)->size(300)->generate($asset->qr_code);

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

            $purchase->request()->update(
                [
                    'status_id' => 4
                ]
            );

            $user = User::find($asset->owner_id);
            if ($user->fcm_token != null) {
                $user->notify(new SendNotification(
                    'Asset siap diterima',
                    'Segera cek dan terima asset',
                    'ticket'
                ));
            }
        }
        return response()->json(['message' => 'Data Aset Telah Dibuat'], 201);
    }

    public function myAssets()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $assets = $user->assets()->paginate(10);
        return AssetResource::collection($assets);
    }

    public function myAssetList()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $assets = $user->assets()->select('id', 'model')->get();

        return response()->json($assets, 200);
    }

    public function acceptAsset(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => 'required|integer'
        ]);

        $user = User::whereId(auth()->user()->id)->select('id')->first();

        $asset = Asset::whereId($request->asset_id)->first();
        if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);
        if ($asset->status_id != 1) return response()->json(['message' => 'Asset Sudah di deploy'], 400);

        if ($user->id != $asset->owner_id) return response()->json(['message' => 'Bukan Penerima'], 403);

        $asset->update([
            'status_id' => 2,
            'deployed_at' => date('Y-m-d')
        ]);

        if ($asset->purchase_id) {
            $asset->purchase->request()->update([
                'status_id' => 4
            ]);
        }

        return response()->json(
            [
                'message' => 'Berhasil',
                'status' => $asset->status->status
            ],
            200
        );
    }

    public function scrapAsset(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'tidak berwenang'], 403);

        $request->validate([
            'asset_id' => 'required|integer'
        ]);

        $asset = Asset::whereId($request->asset_id)->first();
        if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);

        $asset->update([
            'status_id' => 5,
            'scrapped_at' => now(),
            'owner_id' => null
        ]);

        return response()->json(['message' => 'Asset Scrapped', 'data' => ['status' => $asset->status->status]], 200);
    }

    public function transferAsset(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'tidak berwenang'], 403);

        $request->validate([
            'asset_id' => 'required|integer',
            'user_id' => 'required|string',
            'utilization' => 'required|string'
        ]);

        $asset = Asset::find($request->asset_id);
        if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);


        if ($asset->status_id != 2 && $asset->status_id != 4) return response()->json(['message' => 'Asset tidak tersedia'], 400);

        $user = User::find($request->user_id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $asset->update([
            'owner_id' => $user->id,
            'location_id' => $user->branch_id,
            'utilization' => $request->utilization,
            'status_id' => 2,   
            'deployed_at' => now()
        ]);

        return response()->json(['message' => 'Berhasil memindahkan asset'], 200);
    }

    public function reserveAsset(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        if (!$access) return response()->json(['message' => 'tidak berwenang'], 403);

        $request->validate([
            'asset_id' => 'required|integer'
        ]);

        $asset = Asset::find($request->asset_id);
        if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);

        if ($asset->status_id != 2) return response()->json(['message' => 'Asset tidak bisa diambil'], 400);

        $asset->update([
            'owner_id' => null,
            'status_id' => 4,
            'location_id' => 1,
            'deployed_at' => null
        ]);

        return response()->json([
            'message' => 'Berhasil mengambil asset sebagai cadangan',
            'data' => [
                'status' => $asset->status->status
                ]
        ], 200);    
    }

    public function getAllAssets(Request $request)
    {
        $access = auth()->user()->role->asset_management || auth()->user()->role->asset_request;
        if (!$access) return response()->json(['message' => 'tidak berwenang'], 200);

        if (auth()->user()->role->asset_management) {
            $assets = Asset::orderBy('status_id', 'asc')->paginate(10);
        } else {
            $usersWithMatchingDepartment = User::where('department', auth()->user()->department)->pluck('id');
            $assets = Asset::where('location_id', auth()->user()->branch_id)->whereIn('owner_id', $usersWithMatchingDepartment)->paginate(10);
        }

        return AssetResource::collection($assets);
    }

    function getAssetTicketHistory(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer'
        ]);
        $assetId = $request->query('asset_id');
        $asset = Asset::find($assetId);
        if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);
        $tickets = $asset->tickets()->paginate(10);
        if ($tickets->isEmpty()) return response()->json(['message' => 'Belum ada tiket yang terbuat'], 404);
        return TicketResource::collection($tickets);
    }

    public function getAssetAttachedSpareParts(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer'
        ]);

        $assetId = $request->query('asset_id');
        $asset = Asset::find($assetId);
        if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);
        $spareParts = $asset->spareParts()->paginate(10);
        if ($spareParts->isEmpty()) return response()->json(['message' => 'Belum ada Spare Part yang digunakan'], 404);
        return SparePartResource::collection($spareParts);
    }

    public function getAssetByQRCode(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $asset = Asset::where('qr_code', $request->qr_code)->first();

        return new AssetResource($asset);
    }
}
