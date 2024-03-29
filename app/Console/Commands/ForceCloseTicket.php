<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Ticket;
use Illuminate\Console\Command;

class ForceCloseTicket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:force-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "force closing tickets that has In Review status and hasn't been closed yet for more than 2 hours";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tickets = Ticket::where('status_id', 4)->get();

        foreach($tickets as $ticket) {
            if(Carbon::parse($ticket->resolved_at)->diffInHours(Carbon::now()) < 2) continue;
            $ticket->update(['status_id' => 5]);
            if($ticket->asset_id){
                $ticket->asset()->update(['status_id' => 2]);
            }
        }

        $this->info('Auto close tickets completed');
    }
}
