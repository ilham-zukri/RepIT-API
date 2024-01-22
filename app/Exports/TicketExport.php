<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TicketExport implements FromCollection, WithHeadings, WithMapping
{
    private $date;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct(String $date)
    {
        $this->date = $date;
    }
    public function collection()
    {
        $tickets = Ticket::select('created_by_id', 'handler_id', 'title', 'description', 'priority_id', 'asset_id', 'status_id', 'created_at','responded_at', 'ticket_category_id', 'resolved_at')
            ->whereYear('created_at', Carbon::parse($this->date)->year)
            ->whereMonth('created_at', Carbon::parse($this->date)->month)
            ->get();

            foreach ($tickets as $ticket) {
                $createdAt = $ticket->created_at;
                $respondedAt = $ticket->responded_at;
                $resolvedAt = $ticket->resolved_at;
                $ticketResponseT = $ticket->priority->max_response_time;
                $ticketResolveT = $ticket->priority->max_resolve_time;
    
                $responseTime = $respondedAt ? $respondedAt->diffInMinutes($createdAt) : null;
                $resolveTime = $resolvedAt ? $resolvedAt->diffInMinutes($createdAt) / 60 : null;
    
                if ($responseTime <= $ticketResponseT && $resolveTime <= $ticketResolveT) {
                    $ticket['meeting_requirements'] = true;
                } else {
                    $ticket['meeting_requirements'] = false;
                }
            }

        return $tickets;
    }

    public function map($ticket): array
    {
        return [
            'Pembuat Ticket' => $ticket->createdBy->full_name,
            'Direspon Oleh' => $ticket->handler->full_name,
            'Judul' => $ticket->title,
            'Deskripsi' => $ticket->description,
            'Prioritas' => $ticket->priority->priority,
            'Aset' => $ticket->asset ? $ticket->asset->name : '#N/A',
            'Status' => $ticket->status->status,
            'Dibuat Pada' => $ticket->created_at->format('d-m-Y | H:i y'),
            'Direspon Pada' => $ticket->responded_at ? $ticket->responded_at->format('d-m-Y | H:i y') : '#N/A',
            'Kategori Aset' => $ticket->category->category,
            'Terselesaikan Pada' => $ticket->resolved_at ? $ticket->resolved_at->format('d-m-Y | H:i y') : '#N/A',
            'Memenuhi SLA' => $ticket->meeting_requirements ? 'Ya' : 'Tidak'
        ];
    }

    public function headings(): array
    {
        return [
            'Pembuat Ticket',
            'Direspon Oleh',
            'Judul',
            'Deskripsi',
            'Prioritas',
            'Aset',
            'Status',
            'Dibuat Pada',
            'Direspon Pada',
            'Kategori Tiket',
            'Terselesaikan Pada',
            'Memenuhi SLA'
        ];
    }
}
