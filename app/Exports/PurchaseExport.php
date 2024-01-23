<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PurchaseExport implements FromCollection, WithMapping, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    private $purchases;
    public function __construct($purchases) {
        $this->purchases = $purchases;
    }
    public function collection()
    {
        $purchases = $this->purchases;
        
        return $purchases;
    }

    public function map($purchase): array
    {
        return [
            'Pemohon' => $purchase->request->requester->full_name,
            'Pembuat' => $purchase->buyer->full_name,
            'Vendor' => $purchase->purchased_from,
            'Total Harga' => $purchase->total_price,
            'Status' => $purchase->status->status,
            'Keterangan' => $purchase->description,
            'Tanggal Pilihan' => $purchase->created_at->format('d-m-Y | H:i y'),
        ];
    }

    public function headings(): array
    {
        return [
            'Pemohon',
            'Pembuat',
            'Vendor',
            'Total Harga',
            'Status',
            'Keterangan',
            'Tanggal Pembelian'
        ];
    }
}
