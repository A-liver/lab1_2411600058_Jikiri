<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    /** Delivery channels: in-app bell (database) + email. */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $p = $this->product;
        $state = $p->isOutOfStock() ? 'is OUT OF STOCK' : 'is running low';

        return (new MailMessage)
            ->subject("Low stock alert: {$p->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$p->name} (SKU {$p->sku}) {$state}.")
            ->line("Current quantity: {$p->quantity}")
            ->line("Reorder level: {$p->reorder_level}")
            ->action('View Product', route('products.show', $p))
            ->line('Suggested action: reorder from ' . ($p->supplier ?: 'your supplier') . '.');
    }

    /** Stored in notifications.data (JSON) and shown in the bell dropdown. */
    public function toArray(object $notifiable): array
    {
        $p = $this->product;

        return [
            'product_id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'quantity' => $p->quantity,
            'reorder_level' => $p->reorder_level,
            'status' => $p->stock_status,
            'message' => "{$p->name} ({$p->sku}) is "
                . ($p->isOutOfStock() ? 'out of stock' : "low: {$p->quantity} left, reorder at {$p->reorder_level}"),
            'suggested_action' => 'Reorder',
        ];
    }
}