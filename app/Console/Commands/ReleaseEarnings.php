<?php

namespace App\Console\Commands;

use App\Services\EarningsService;
use Illuminate\Console\Command;

/**
 * Daily release of earnings that survived the post-delivery hold
 * window (default 14 days): pending_balance -> available balance.
 */
class ReleaseEarnings extends Command
{
    protected $signature = 'soukelkom:release-earnings';

    protected $description = 'Release seller earnings whose hold period has ended';

    public function handle(EarningsService $earnings): int
    {
        $result = $earnings->releaseDue();

        $this->info("Released {$result['count']} earning(s) totalling \${$result['total']}.");

        return self::SUCCESS;
    }
}
