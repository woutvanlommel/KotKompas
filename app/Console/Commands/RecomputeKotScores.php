<?php

namespace App\Console\Commands;

use App\Services\KotScoreService;
use Illuminate\Console\Command;

class RecomputeKotScores extends Command
{
    protected $signature = 'app:recompute-kotscores';

    protected $description = 'Herbereken alle cached kot-, gebouw- en verhuurderscores (vangt recency-drift op)';

    public function handle(KotScoreService $kotScoreService): int
    {
        $kotScoreService->recomputeAll();

        $this->info('Kotscores herberekend.');

        return self::SUCCESS;
    }
}
