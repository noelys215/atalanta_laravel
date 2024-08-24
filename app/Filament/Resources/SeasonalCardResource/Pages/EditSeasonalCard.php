<?php

namespace App\Filament\Resources\SeasonalCardResource\Pages;

use App\Filament\Resources\SeasonalCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeasonalCard extends EditRecord
{
    protected static string $resource = SeasonalCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
