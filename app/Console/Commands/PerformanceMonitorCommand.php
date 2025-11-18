<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PerformanceMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:monitor 
                            {--detailed : Show detailed performance metrics}
                            {--json : Output results in JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor application performance metrics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $metrics = $this->gatherPerformanceMetrics();

        if ($this->option('json')) {
            $this->line(json_encode($metrics, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        $this->displayMetrics($metrics);
        return Command::SUCCESS;
    }

    /**
     * Gather performance metrics
     */
    protected function gatherPerformanceMetrics(): array
    {
        return [
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'storage' => $this->getStorageMetrics(),
            'application' => $this->getApplicationMetrics(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get database performance metrics
     */
    protected function getDatabaseMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test database connection and query performance
        $connectionTest = DB::select('SELECT 1 as test');
        $connectionTime = (microtime(true) - $startTime) * 1000;

        // Get table sizes
        $tableSizes = DB::select("
            SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC
        ");

        // Get slow queries (if enabled)
        $slowQueries = [];
        try {
            $slowQueries = DB::select("
                SELECT query_time, sql_text 
                FROM mysql.slow_log 
                WHERE start_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY query_time DESC 
                LIMIT 5
            ");
        } catch (\Exception $e) {
            // Slow query log might not be enabled
        }

        return [
            'connection_time_ms' => round($connectionTime, 2),
            'connection_status' => !empty($connectionTest) ? 'healthy' : 'error',
            'table_sizes' => $tableSizes,
            'slow_queries_count' => count($slowQueries),
            'slow_queries' => $this->option('detailed') ? $slowQueries : [],
        ];
    }

    /**
     * Get cache performance metrics
     */
    protected function getCacheMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test cache performance
        $testKey = 'performance_test_' . time();
        $testValue = 'test_data';
        
        Cache::put($testKey, $testValue, 60);
        $retrieved = Cache::get($testKey);
        Cache::forget($testKey);
        
        $cacheTime = (microtime(true) - $startTime) * 1000;

        // Try to get Redis info if using Redis
        $redisInfo = [];
        try {
            if (config('cache.default') === 'redis') {
                $redis = app('redis')->connection();
                $info = $redis->info();
                $redisInfo = [
                    'memory_usage' => $info['used_memory_human'] ?? 'unknown',
                    'connected_clients' => $info['connected_clients'] ?? 'unknown',
                    'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                ];
                
                if ($redisInfo['keyspace_hits'] + $redisInfo['keyspace_misses'] > 0) {
                    $redisInfo['hit_ratio'] = round(
                        ($redisInfo['keyspace_hits'] / ($redisInfo['keyspace_hits'] + $redisInfo['keyspace_misses'])) * 100, 
                        2
                    );
                }
            }
        } catch (\Exception $e) {
            // Redis might not be available
        }

        return [
            'driver' => config('cache.default'),
            'operation_time_ms' => round($cacheTime, 2),
            'test_status' => $retrieved === $testValue ? 'healthy' : 'error',
            'redis_info' => $redisInfo,
        ];
    }

    /**
     * Get storage performance metrics
     */
    protected function getStorageMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test file system performance
        $testFile = 'performance_test_' . time() . '.txt';
        $testContent = 'Performance test content';
        
        Storage::disk('public')->put($testFile, $testContent);
        $retrieved = Storage::disk('public')->get($testFile);
        Storage::disk('public')->delete($testFile);
        
        $storageTime = (microtime(true) - $startTime) * 1000;

        // Get storage usage
        $storagePath = storage_path('app/public');
        $totalSize = $this->getDirectorySize($storagePath);
        
        // Get gallery statistics
        $galleryPath = $storagePath . '/gallery';
        $gallerySize = file_exists($galleryPath) ? $this->getDirectorySize($galleryPath) : 0;

        return [
            'driver' => config('filesystems.default'),
            'operation_time_ms' => round($storageTime, 2),
            'test_status' => $retrieved === $testContent ? 'healthy' : 'error',
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'gallery_size_mb' => round($gallerySize / 1024 / 1024, 2),
            'available_space_gb' => round(disk_free_space($storagePath) / 1024 / 1024 / 1024, 2),
        ];
    }

    /**
     * Get application performance metrics
     */
    protected function getApplicationMetrics(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'memory_limit' => ini_get('memory_limit'),
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false,
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
        ];
    }

    /**
     * Display metrics in a formatted way
     */
    protected function displayMetrics(array $metrics): void
    {
        $this->info('🚀 Performance Monitoring Report');
        $this->info('Generated at: ' . $metrics['timestamp']);
        $this->newLine();

        // Database Metrics
        $this->info('📊 Database Performance');
        $db = $metrics['database'];
        $this->line("  Connection: {$db['connection_status']} ({$db['connection_time_ms']}ms)");
        
        if ($this->option('detailed') && !empty($db['table_sizes'])) {
            $this->line('  Largest Tables:');
            foreach (array_slice($db['table_sizes'], 0, 5) as $table) {
                $this->line("    {$table->table_name}: {$table->size_mb}MB");
            }
        }
        
        if ($db['slow_queries_count'] > 0) {
            $this->warn("  ⚠️  {$db['slow_queries_count']} slow queries in the last hour");
        }
        $this->newLine();

        // Cache Metrics
        $this->info('🗄️  Cache Performance');
        $cache = $metrics['cache'];
        $this->line("  Driver: {$cache['driver']}");
        $this->line("  Status: {$cache['test_status']} ({$cache['operation_time_ms']}ms)");
        
        if (!empty($cache['redis_info'])) {
            $redis = $cache['redis_info'];
            $this->line("  Memory Usage: {$redis['memory_usage']}");
            $this->line("  Connected Clients: {$redis['connected_clients']}");
            if (isset($redis['hit_ratio'])) {
                $hitRatio = $redis['hit_ratio'];
                $status = $hitRatio >= 90 ? '✅' : ($hitRatio >= 70 ? '⚠️' : '❌');
                $this->line("  Hit Ratio: {$hitRatio}% {$status}");
            }
        }
        $this->newLine();

        // Storage Metrics
        $this->info('💾 Storage Performance');
        $storage = $metrics['storage'];
        $this->line("  Driver: {$storage['driver']}");
        $this->line("  Status: {$storage['test_status']} ({$storage['operation_time_ms']}ms)");
        $this->line("  Total Size: {$storage['total_size_mb']}MB");
        $this->line("  Gallery Size: {$storage['gallery_size_mb']}MB");
        $this->line("  Available Space: {$storage['available_space_gb']}GB");
        $this->newLine();

        // Application Metrics
        $this->info('⚙️  Application Performance');
        $app = $metrics['application'];
        $this->line("  PHP Version: {$app['php_version']}");
        $this->line("  Laravel Version: {$app['laravel_version']}");
        $this->line("  Memory Usage: {$app['memory_usage_mb']}MB / {$app['memory_peak_mb']}MB peak");
        $this->line("  Memory Limit: {$app['memory_limit']}");
        $this->line("  OPcache: " . ($app['opcache_enabled'] ? '✅ Enabled' : '❌ Disabled'));
        $this->line("  Environment: {$app['environment']}");
        
        if ($app['debug_mode'] && $app['environment'] === 'production') {
            $this->warn('  ⚠️  Debug mode is enabled in production!');
        }

        $this->newLine();
        $this->info('📈 Performance Summary');
        
        // Performance recommendations
        $recommendations = $this->getRecommendations($metrics);
        if (!empty($recommendations)) {
            $this->warn('💡 Recommendations:');
            foreach ($recommendations as $recommendation) {
                $this->line("  • {$recommendation}");
            }
        } else {
            $this->info('✅ All performance metrics look good!');
        }
    }

    /**
     * Get performance recommendations
     */
    protected function getRecommendations(array $metrics): array
    {
        $recommendations = [];

        // Database recommendations
        if ($metrics['database']['connection_time_ms'] > 100) {
            $recommendations[] = 'Database connection is slow. Consider optimizing database configuration.';
        }

        if ($metrics['database']['slow_queries_count'] > 10) {
            $recommendations[] = 'High number of slow queries detected. Review and optimize database queries.';
        }

        // Cache recommendations
        if ($metrics['cache']['operation_time_ms'] > 50) {
            $recommendations[] = 'Cache operations are slow. Check cache server performance.';
        }

        if (isset($metrics['cache']['redis_info']['hit_ratio']) && $metrics['cache']['redis_info']['hit_ratio'] < 70) {
            $recommendations[] = 'Low cache hit ratio. Review caching strategy and cache TTL settings.';
        }

        // Storage recommendations
        if ($metrics['storage']['operation_time_ms'] > 100) {
            $recommendations[] = 'File system operations are slow. Consider using SSD storage or optimizing file operations.';
        }

        if ($metrics['storage']['available_space_gb'] < 5) {
            $recommendations[] = 'Low disk space available. Consider cleaning up old files or expanding storage.';
        }

        // Application recommendations
        if ($metrics['application']['memory_usage_mb'] > 256) {
            $recommendations[] = 'High memory usage detected. Consider optimizing application code or increasing memory limit.';
        }

        if (!$metrics['application']['opcache_enabled']) {
            $recommendations[] = 'OPcache is disabled. Enable OPcache for better PHP performance.';
        }

        if ($metrics['application']['debug_mode'] && $metrics['application']['environment'] === 'production') {
            $recommendations[] = 'Debug mode is enabled in production. Disable debug mode for better performance and security.';
        }

        return $recommendations;
    }

    /**
     * Get directory size recursively
     */
    protected function getDirectorySize(string $directory): int
    {
        if (!is_dir($directory)) {
            return 0;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }
}