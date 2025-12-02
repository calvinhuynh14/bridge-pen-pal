<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SimulateLetterDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'letters:simulate-delivery 
                            {--hours=0 : Reduce delivery time by this many hours (default: 0)}
                            {--minutes=0 : Reduce delivery time by this many minutes (default: 0)}
                            {--all : Apply to all pending letters}
                            {--user= : Apply to letters for a specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate letter delivery by reducing delivery time for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $minutes = (int) $this->option('minutes');
        $all = $this->option('all');
        $userId = $this->option('user');

        if ($hours === 0 && $minutes === 0) {
            $this->error('Please specify --hours and/or --minutes to reduce delivery time.');
            $this->info('Example: php artisan letters:simulate-delivery --hours=7 --minutes=30');
            return 1;
        }

        $totalReduction = ($hours * 60) + $minutes; // Total minutes to reduce
        $this->info("Reducing delivery time by {$hours} hours and {$minutes} minutes ({$totalReduction} minutes total)...");

        // Build query
        $query = "
            UPDATE letters
            SET delivered_at = DATE_SUB(delivered_at, INTERVAL ? MINUTE),
                updated_at = ?
            WHERE deleted_at IS NULL
            AND delivered_at > ?
        ";

        $params = [$totalReduction, now(), now()];

        // Add user filter if specified
        if ($userId) {
            $query .= " AND receiver_id = ?";
            $params[] = $userId;
        }

        // Execute update
        $affected = DB::update($query, $params);

        $this->info("✓ Updated {$affected} letter(s) delivery time.");
        $this->info("Letters that were scheduled to arrive later will now arrive sooner.");
        
        return 0;
    }
}
