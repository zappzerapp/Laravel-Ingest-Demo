<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BenchmarkIngestCommand extends Command
{
    protected $signature = 'benchmark:ingest
                            {--sizes=100,1000,10000,100000 : Comma-separated list of sizes to test}
                            {--clear : Clear database before each test}';

    protected $description = 'Runs benchmark tests for CSV imports';

    public function handle(): int
    {
        $sizes = array_map('intval', explode(',', $this->option('sizes')));
        $results = [];

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   Laravel Ingest Benchmark Suite         ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        foreach ($sizes as $size) {
            $filename = "products_{$size}.csv";
            $filepath = storage_path("app/csv/{$filename}");

            if (! file_exists($filepath)) {
                $this->warn("Skipping {$filename} - file not found");

                continue;
            }

            $this->info("► Benchmark: {$size} rows");

            if ($this->option('clear')) {
                Product::truncate();
                $this->line('  Database cleared');
            }

            $startMemory = memory_get_usage(true);
            $startTime = microtime(true);
            $startCount = Product::count();

            Artisan::call('ingest:run', [
                'slug' => 'productimporter',
                '--file' => "csv/{$filename}",
            ]);

            $endTime = microtime(true);
            $endMemory = memory_get_peak_usage(true);
            $endCount = Product::count();

            $duration = $endTime - $startTime;
            $rowsImported = $endCount - $startCount;
            $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024;
            $rowsPerSecond = $duration > 0 ? $rowsImported / $duration : 0;

            $results[] = [
                'Size' => number_format($size),
                'Imported' => number_format($rowsImported),
                'Time (s)' => number_format($duration, 2),
                'Memory (MB)' => number_format($memoryUsed, 2),
                'Rows/s' => number_format($rowsPerSecond, 0),
            ];

            $this->line('  ✓ Completed in '.number_format($duration, 2).'s');
            $this->newLine();
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('                      BENCHMARK RESULTS                      ');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->table(
            ['CSV Size', 'Rows Imported', 'Duration (s)', 'Memory (MB)', 'Rows/Sec.'],
            $results
        );

        return self::SUCCESS;
    }
}
