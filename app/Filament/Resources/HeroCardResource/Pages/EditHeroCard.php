<?php

namespace App\Filament\Resources\HeroCardResource\Pages;

use App\Filament\Resources\HeroCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHeroCard extends EditRecord
{
    protected static string $resource = HeroCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
