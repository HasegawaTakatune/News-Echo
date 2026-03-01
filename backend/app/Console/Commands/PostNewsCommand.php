<?php

namespace App\Console\Commands;

use App\Services\NewsBatchService;
use Illuminate\Console\Command;

class PostNewsCommand extends Command
{
    protected $signature = 'news:post';
    protected $description = 'Generate and post due news items to social media';

    public function handle(NewsBatchService $batch)
    {
        $batch->processDueNews();
        $this->info('News post batch completed');
    }
}
