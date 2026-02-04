<?php

namespace App\Ingest;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use LaravelIngest\Contracts\IngestDefinition;
use LaravelIngest\Enums\DuplicateStrategy;
use LaravelIngest\Enums\SourceType;
use LaravelIngest\IngestConfig;

class RelationalProductImporter implements IngestDefinition
{
    public function getConfig(): IngestConfig
    {
        return IngestConfig::for(Product::class)
            ->fromSource(SourceType::FILESYSTEM, [
                'disk' => 'local',
                'path' => 'csv/',
            ])
            ->keyedBy('SKU')
            ->onDuplicate(DuplicateStrategy::UPDATE)
            ->setChunkSize(500)
            ->map('SKU', 'sku')
            ->map('Name', 'name')
            ->map('Description', 'description')
            ->map('Price', 'price')
            ->map('Stock', 'stock')
            ->relate('Category', 'category', Category::class, 'name', createIfMissing: false)
            ->relateMany('Tags', 'tags', Tag::class, 'name', '|');
    }
}
