<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Notifications\OrderPaidNotification;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('cancelOrder')
                ->label('Cancel Order')
                ->requiresConfirmation()
                ->modalHeading('Are you sure you want to cancel this order?')
                ->modalSubmitActionLabel('Yes, cancel the order')
                ->modalCancelActionLabel('No, keep the order')
                ->modalIconColor('warning')
                ->modalIcon('heroicon-o-trash')
                ->action(function () {
                    $this->cancelOrder();
                }),
            Action::make('resendEmail')
                ->label('Resend Email')
                ->action('resendEmail'),
        ];
    }

    public function cancelOrder()
    {
        $order = $this->record;
        OrderResource::handleOrderCancelled($order);
        $order->delete();

        FilamentNotification::make()
            ->title('Success')
            ->body('Order cancelled successfully.')
            ->success()
            ->send();

        return $this->redirect('/admin/orders');
    }

    public function resendEmail()
    {
        $order = $this->record;
        Notification::send($order->user, new OrderPaidNotification($order));
        FilamentNotification::make()
            ->title('Success')
            ->body('Order email resent successfully.')
            ->success()
            ->send();
    }
}
