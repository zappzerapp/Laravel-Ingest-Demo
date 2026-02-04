<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class BenchmarkRelationalCommand extends Command
{
    protected $signature = 'benchmark:ingest-relational
                            {--sizes=100,1000,10000,100000 : Comma-separated list of sizes to test}
                            {--clear : Clear tables before each test}
                            {--json : Output results as JSON instead of table}';

    protected $description = 'Runs benchmark comparing Flat vs Relational CSV imports';

    public function handle(): int
    {
        $sizes = array_map('intval', explode(',', $this->option('sizes')));
        $results = [];
        $jsonMode = $this->option('json');

        if (! $jsonMode) {
            $this->info('╔══════════════════════════════════════════╗');
            $this->info('║   Flat vs Relational Benchmark Suite     ║');
            $this->info('╚══════════════════════════════════════════╝');
            $this->newLine();
        }

        foreach ($sizes as $size) {
            $flatFilename = "products_{$size}.csv";
            $relationalFilename = "products_relational_{$size}.csv";
            $flatFilepath = storage_path("app/csv/{$flatFilename}");
            $relationalFilepath = storage_path("app/csv/{$relationalFilename}");

            if (! file_exists($flatFilepath) || ! file_exists($relationalFilepath)) {
                if (! $jsonMode) {
                    $this->warn("Skipping size {$size} - CSV files not found");
                }

                continue;
            }

            if (! $jsonMode) {
                $this->info("► Benchmark: {$size} rows");
            }

            if ($this->option('clear')) {
                Product::truncate();
                DB::table('product_tag')->truncate();
                if (! $jsonMode) {
                    $this->line('  Database cleared');
                }
            }

            $flatStartTime = microtime(true);
            Artisan::call('ingest:run', [
                'slug' => 'productimporter',
                '--file' => "csv/{$flatFilename}",
            ]);
            $flatEndTime = microtime(true);
            $flatDuration = $flatEndTime - $flatStartTime;

            if (! $jsonMode) {
                $this->line('  ✓ Flat import completed in '.number_format($flatDuration, 2).'s');
            }

            Product::truncate();
            DB::table('product_tag')->truncate();

            $relationalStartTime = microtime(true);
            Artisan::call('ingest:run', [
                'slug' => 'relationalproductimporter',
                '--file' => "csv/{$relationalFilename}",
            ]);
            $relationalEndTime = microtime(true);
            $relationalDuration = $relationalEndTime - $relationalStartTime;

            if (! $jsonMode) {
                $this->line('  ✓ Relational import completed in '.number_format($relationalDuration, 2).'s');
            }

            $overhead = (($relationalDuration - $flatDuration) / $flatDuration) * 100;

            $results[] = [
                'size' => $size,
                'flat' => round($flatDuration, 2),
                'relational' => round($relationalDuration, 2),
                'overhead' => round($overhead, 1),
            ];

            if (! $jsonMode) {
                $this->newLine();
            }
        }

        if ($jsonMode) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('                 FLAT vs RELATIONAL COMPARISON                 ');
        $this->info('═══════════════════════════════════════════════════════════════');

        $tableData = array_map(function ($result) {
            $overheadStr = $result['overhead'] >= 0 ? '+'.$result['overhead'].'%' : $result['overhead'].'%';

            return [
                $result['size'],
                $result['flat'],
                $result['relational'],
                $overheadStr,
            ];
        }, $results);

        $this->table(
            ['CSV Size', 'Flat (s)', 'Relational (s)', 'Overhead'],
            $tableData
        );

        return self::SUCCESS;
    }
}
