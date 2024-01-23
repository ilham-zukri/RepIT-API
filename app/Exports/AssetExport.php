<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $locationId;
    public function __construct(int $locationId = null) {
        $this->locationId = $locationId;
    }
    public function collection()
    {
        $assetsQ = Asset::select('id','owner_id', 'asset_type', 'brand', 'name','model', 'serial_number', 'cpu', 'ram', 'utilization', 'location_id', 'created_at', 'deployed_at', 'status_id', 'purchase_id', 'scrapped_at');

        if ($this->locationId) {
            $assetsQ->where('location_id', $this->locationId);
        }

        $assets = $assetsQ->get();

        foreach ($assets as $asset)
        {
            $asset['ticket_count'] = $asset->tickets->count();
        }

        return $assets;
    }

    public function map($asset): array 
    {
        return [
            'Pemilik' => $asset->owner_id ? $asset->owner->full_name : '#N/A',
            'Tipe Aset' => $asset->asset_type,
            'Nama' => $asset->name,
            'Merek' => $asset->brand,
            'Model' => $asset->model,
            'Serial Number' => $asset->serial_number,
            'CPU' => $asset->cpu,
            'RAM' => $asset->ram,
            'Penggunaan' => $asset->utilization,
            'Lokasi' => $asset->location->name,
            'Tanggal Dibuat' => $asset->created_at->format('d-m-Y | H:i y'),
            'Tanggal Diterima' =>($asset->deployed_at) ? $asset->deployed_at->format('d-m-Y | H:i y') : null,
            'status' => $asset->status->status,
            'No. Pembelian' => $asset->purchase_id,
            'Tanggal Dihapus' => $asset->scrapped_at ? $asset->scrapped_at->format('d-m-Y | H:i y') : null,
            'Jumlah Tiket Terbuat' => $asset->ticket_count ?? 0
        ];
    }

    public function headings(): array
    {
        return [
            'Pemilik',
            'Tipe Aset',
            'Nama',
            'Merk',
            'Model',
            'Serial Number',
            'CPU',
            'RAM',
            'Penggunaan',
            'Lokasi',
            'Tanggal Dibuat',
            'Tanggal Diterima',
            'status',
            'No. Pembelian',
            'Tanggal Dihapus',
            'Jumlah Tiket Terbuat'
        ];
    }
}
