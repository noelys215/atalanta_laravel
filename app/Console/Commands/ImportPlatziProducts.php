<?php

namespace App\Console\Commands;

use App\Services\PlatziProductImporter;
use Illuminate\Console\Command;

class ImportPlatziProducts extends Command
{
    protected $signature = 'platzi:import-products
                            {--limit= : Total number of products to import}
                            {--page=1 : Starting page number}
                            {--download-images : Download and store images locally}';

    protected $description = 'Import products from Platzi Fake Store API';

    public function handle(PlatziProductImporter $importer): int
    {
        $limit = (int) ($this->option('limit') ?? config('seed_sources.platzi.default_limit', 50));
        $page = (int) ($this->option('page') ?? 1);
        $downloadImages = (bool) $this->option('download-images');

        $this->info("Importing up to {$limit} products from Platzi (page {$page})...");

        $result = $importer->import($limit, $page, $downloadImages, $this);

        $this->info("Created: {$result['created']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}");

        return self::SUCCESS;
    }
}
