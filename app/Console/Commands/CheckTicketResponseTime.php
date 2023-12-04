<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Priority;
use Illuminate\Console\Command;

class CheckTicketResponseTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-ticket-response-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check tickets that exceeded response time limit';

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
                ->whereNull('responded_at')
                ->where('created_at', '<=', Carbon::now()->subMinutes($priority->max_response_time))
                ->get();

            foreach ($tickets as $ticket) {
                // Update flag_id for tickets exceeding response time
                $ticket->update(['flag_id' => 1]);
            }
        }

        $this->info('Response time check completed.' . '|' . $date);
    }
    
}
