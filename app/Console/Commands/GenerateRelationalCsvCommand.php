<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Tag;
use Faker\Factory as Faker;
use Illuminate\Console\Command;

class GenerateRelationalCsvCommand extends Command
{
    protected $signature = 'csv:generate-relational {--sizes=100,1000,10000,100000}';

    protected $description = 'Generates test CSV files with relational data (categories and tags)';

    public function handle(): int
    {
        // Pre-seed categories and tags
        $this->info('Pre-seeding categories...');
        Category::truncate();
        $this->call('db:seed', ['--class' => 'CategorySeeder']);

        $this->info('Pre-seeding tags...');
        Tag::truncate();
        $this->call('db:seed', ['--class' => 'TagSeeder']);

        $faker = Faker::create();
        $sizes = array_map('intval', explode(',', $this->option('sizes')));

        // Get categories and tags from database
        $categories = Category::pluck('name')->toArray();
        $tags = Tag::pluck('name')->toArray();

        $csvDir = storage_path('app/csv');
        if (! is_dir($csvDir) && ! mkdir($csvDir, 0755, true) && ! is_dir($csvDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $csvDir));
        }

        foreach ($sizes as $size) {
            $this->info("Generating {$size} rows...");
            $filename = "products_relational_{$size}.csv";
            $path = "{$csvDir}/{$filename}";

            $handle = fopen($path, 'wb');

            fputcsv($handle, ['SKU', 'Name', 'Description', 'Price', 'Category', 'Tags', 'Stock']);

            $bar = $this->output->createProgressBar($size);
            for ($i = 1; $i <= $size; $i++) {
                // Generate 0-3 random tags
                $numTags = $faker->numberBetween(0, 3);
                $selectedTags = [];
                if ($numTags > 0) {
                    $selectedTags = $faker->randomElements($tags, $numTags);
                }
                $tagsString = implode('|', $selectedTags);

                fputcsv($handle, [
                    sprintf('SKU-%06d', $i),
                    $faker->words(3, true),
                    $faker->sentence(10),
                    $faker->randomFloat(2, 1, 999),
                    $faker->randomElement($categories),
                    $tagsString,
                    $faker->numberBetween(0, 1000),
                ]);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            fclose($handle);
            $this->info("Created: {$filename}");
        }

        $this->info('All relational CSV files successfully generated!');

        return self::SUCCESS;
    }
}
