<?php

namespace App\Console\Commands;

use App\Models\Performance;
use App\Models\Priority;
use App\Models\Ticket;
use Illuminate\Console\Command;

class SlaCalculation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sla:calculation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'calculating monthly SLA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recentPeriod = now()->subMonth();
        $this->info($recentPeriod);
        $recentMonthPeriod = $recentPeriod->format('m');
        $recentYearPeriod = $recentPeriod->format('Y');

        if ($recentPeriod->month === '01') {
            $recentPeriod = $recentPeriod->subYear();
            $recentMonthPeriod = '12';
            $recentYearPeriod = $recentPeriod->format('Y');
        }

        $this->info('Last month: ' . $recentMonthPeriod . ' year: ' . $recentYearPeriod);

        $tickets = Ticket::whereMonth('created_at', $recentMonthPeriod)->get();

        $totalTickets = $tickets->count();
        $ticketMeetingRequirements = 0;
        $sla = 0.0;
        

        foreach ($tickets as $ticket) {
            $createdAt = $ticket->created_at;
            $respondedAt = $ticket->responded_at;
            $resolvedAt = $ticket->resolved_at;
            $ticketResponseT = $ticket->priority->max_response_time;
            $ticketResolveT = $ticket->priority->max_resolve_time;

            $responseTime = $respondedAt ? $respondedAt->diffInMinutes($createdAt) : null;
            $resolveTime = $resolvedAt ? $resolvedAt->diffInMinutes($createdAt) / 60 : null;

            if ($responseTime <= $ticketResponseT && $resolveTime <= $ticketResolveT) {
                $ticketMeetingRequirements++;
            }
        }

        $sla = ($ticketMeetingRequirements / $totalTickets) * 100;

        Performance::create([
            'month_period' => $recentMonthPeriod,
            'year_period' => $recentYearPeriod,
            'total_tickets' => $totalTickets,
            'tickets_meeting_requirements' => $ticketMeetingRequirements,
            'sla' => $sla
        ]);

        $this->info('SLA calculated');
    }
}
