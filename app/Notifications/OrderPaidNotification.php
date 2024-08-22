<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification
{
    use Queueable;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Ensure order_items is decoded
        $orderItems = is_string($this->order->order_items) ? json_decode($this->order->order_items, true) : $this->order->order_items;

        \Log::info('Constructing OrderPaidNotification mail', ['order_id' => $this->order->id, 'email' => $this->order->customer_email]);

        try {
            return (new MailMessage)
                ->subject('Thank you for your payment')
                ->view(
                    'emails.order_paid', ['order' => $this->order, 'orderItems' => $orderItems]
                );
        } catch (\Exception $e) {
            \Log::error('Failed to construct OrderPaidNotification mail', [
                'order_id' => $this->order->id,
                'email' => $this->order->customer_email,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }


}
