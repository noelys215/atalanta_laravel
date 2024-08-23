<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterCreate()
    {
        if ($this->record->is_paid) {
            OrderResource::handleOrderPaid($this->record);
        }
    }

}
