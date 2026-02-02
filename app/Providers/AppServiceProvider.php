<?php

namespace App\Providers;

use App\Ingest\ProductImporter;
use Illuminate\Support\ServiceProvider;
use LaravelIngest\IngestServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag([
            ProductImporter::class,
        ], IngestServiceProvider::INGEST_DEFINITION_TAG);
    }

    public function boot(): void
    {
        //
    }
}
