<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;

class MassNamingAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:mass-naming-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'for generating asset name';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assets = Asset::where('name', null)->get();
        foreach ($assets as $asset) {
            $year = $asset->created_at->year;
            $month = $asset->created_at->month;
            $id = $asset->id;

            $name = "SM-{$year}-{$month}-{$id}";

            $asset->update([
                'name' => $name
            ]);
        }
        $this->info('Mass naming completed');
    }
}
