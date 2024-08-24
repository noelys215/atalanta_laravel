<?php

namespace App\Filament\Resources\HeroCardResource\Pages;

use App\Filament\Resources\HeroCardResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHeroCard extends CreateRecord
{
    protected static string $resource = HeroCardResource::class;
}
