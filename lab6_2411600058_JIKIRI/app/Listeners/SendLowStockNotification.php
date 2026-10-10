<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotification implements ShouldQueue
{
    // Only queue the job after the DB transaction commits, so the worker never
    // reads half-saved data.
    public $afterCommit = true;

    public $tries = 3;

    public function handle(LowStockDetected $event): void
    {
        // The product is re-loaded fresh when the job runs. If stock has recovered
        // (restocked or the movement was undone), the alert is no longer true.
        if (! $event->product->isLowStock()) {
            return;
        }

        $recipients = User::alertRecipients()->get(); // admins + managers

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new LowStockNotification($event->product));
    }
}