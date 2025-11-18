<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class WarmCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm 
                            {--clear : Clear existing cache before warming}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application cache for better performance';

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        if ($this->option('clear')) {
            $this->info('Clearing existing cache...');
            $this->cacheService->clearAllCache();
            
            if ($this->option('detailed')) {
                $this->line('✓ All cache cleared');
            }
        }

        $this->info('Warming up application cache...');

        try {
            // Warm up critical caches
            $this->warmUpWithProgress();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->info("Cache warming completed successfully in {$duration} seconds!");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Cache warming failed: ' . $e->getMessage());
            
            if ($this->option('detailed')) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }

    /**
     * Warm up cache with progress indication
     */
    protected function warmUpWithProgress(): void
    {
        $tasks = [
            'Prayer Times' => fn() => $this->cacheService->getTodaysPrayerTimes(),
            'Upcoming Events' => fn() => $this->cacheService->getUpcomingEvents(),
            'Recent Blogs' => fn() => $this->cacheService->getRecentBlogs(),
            'Active Fests' => fn() => $this->cacheService->getActiveFests(),
            'Event Statistics' => fn() => $this->cacheService->getEventStatistics(),
            'Registration Statistics' => fn() => $this->cacheService->getRegistrationStatistics(),
            'Gallery Statistics' => fn() => $this->cacheService->getGalleryStatistics(),
        ];

        $progressBar = $this->output->createProgressBar(count($tasks));
        $progressBar->start();

        foreach ($tasks as $taskName => $task) {
            try {
                $task();
                
                if ($this->option('detailed')) {
                    $this->newLine();
                    $this->line("✓ {$taskName} cached");
                }
                
            } catch (\Exception $e) {
                if ($this->option('detailed')) {
                    $this->newLine();
                    $this->error("✗ {$taskName} failed: " . $e->getMessage());
                }
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }
}