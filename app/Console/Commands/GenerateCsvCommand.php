<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;

class GenerateCsvCommand extends Command
{
    protected $signature = 'csv:generate {--sizes=100,1000,10000,100000}';

    protected $description = 'Generates test CSV files for benchmark';

    private array $categories = [
        'Electronics', 'Clothing', 'Household', 'Sports',
        'Books', 'Toys', 'Garden', 'Auto',
    ];

    public function handle(): int
    {
        $faker = Faker::create();
        $sizes = array_map('intval', explode(',', $this->option('sizes')));

        $csvDir = storage_path('app/csv');
        if (!is_dir($csvDir) && !mkdir($csvDir, 0755, true) && !is_dir($csvDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $csvDir));
        }

        foreach ($sizes as $size) {
            $this->info("Generating {$size} rows...");
            $filename = "products_{$size}.csv";
            $path = "{$csvDir}/{$filename}";

            $handle = fopen($path, 'wb');

            fputcsv($handle, ['SKU', 'Name', 'Description', 'Price', 'Category', 'Stock']);

            $bar = $this->output->createProgressBar($size);
            for ($i = 1; $i <= $size; $i++) {
                fputcsv($handle, [
                    sprintf('SKU-%06d', $i),
                    $faker->words(3, true),
                    $faker->sentence(10),
                    $faker->randomFloat(2, 1, 999),
                    $faker->randomElement($this->categories),
                    $faker->numberBetween(0, 1000),
                ]);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            fclose($handle);
            $this->info("Created: {$filename}");
        }

        $this->info('All CSV files successfully generated!');

        return self::SUCCESS;
    }
}
