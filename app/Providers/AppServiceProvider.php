<?php

namespace App\Providers;

use App\Ingest\ProductImporter;
use App\Ingest\RelationalProductImporter;
use Illuminate\Support\ServiceProvider;
use LaravelIngest\IngestServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag([
            ProductImporter::class,
            RelationalProductImporter::class,
        ], IngestServiceProvider::INGEST_DEFINITION_TAG);
    }

    public function boot(): void
    {
        //
    }
}
