<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Product;
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

        // Send the email notification as done in the StripeController
        try {
            Notification::route('mail', $order->customer_email)
                ->notify(new OrderPaidNotification($order));
            \Log::info('Order paid email resent successfully', ['order_id' => $order->id, 'email' => $order->customer_email]);

            FilamentNotification::make()
                ->title('Success')
                ->body('Order email resent successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Log::error('Failed to resend order paid email', [
                'order_id' => $order->id,
                'email' => $order->customer_email,
                'error_message' => $e->getMessage(),
            ]);

            FilamentNotification::make()
                ->title('Error')
                ->body('Failed to resend order email.')
                ->danger()
                ->send();
        }
    }




}
