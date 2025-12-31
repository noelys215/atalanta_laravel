<?php

namespace Database\Seeders;

use App\Services\PlatziProductImporter;
use Illuminate\Database\Seeder;

class PlatziProductSeeder extends Seeder
{
    public function run(): void
    {
        $limit = (int) config('seed_sources.platzi.default_limit', 50);
        $importer = app(PlatziProductImporter::class);

        $importer->import($limit, 1, false, $this->command);
    }
}
