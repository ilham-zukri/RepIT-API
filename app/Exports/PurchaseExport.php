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

    private $month;
    public function __construct($month) {
        $this->month = $month;
    }
    public function collection()
    {
        $purchases = Purchase::select('request_id', 'purchased_by', 'purchased_from', 'total_price', 'status_id', 'description', 'created_at')
            ->whereYear('created_at', Carbon::parse($this->month)->year)
            ->whereMonth('created_at', Carbon::parse($this->month)->month)
            ->get();
        
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
