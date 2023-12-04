<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Priority;
use Illuminate\Console\Command;

class CheckTicketResolveTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-ticket-resolve-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check tickets that exceeded resolve time limit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = now()->format('m/d/Y H:i');
        $priorities = Priority::all();
        foreach ($priorities as $priority) {
            $tickets = Ticket::where('status_id', '<>', 5)
                ->where('priority_id', $priority->id)
                ->whereNull('resolved_at')
                ->where('responded_at', '<=', Carbon::now()->subHours($priority->max_resolve_time))
                ->get();

                foreach ($tickets as $ticket) {
                    // Update flag_id for tickets exceeding resolve time
                    $ticket->update(['flag_id' => 2]);
                }
        }
        
        $this->info('Resolve time check completed.' . '|' . $date);
    }
}
