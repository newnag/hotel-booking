<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Jobs\SendLineNotificationJob;

class SendLineNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        // Dispatch job to send notification to staff
        SendLineNotificationJob::dispatch($event->booking, 'staff');

        // Dispatch job to send confirmation to guest (if they have Line integration)
        SendLineNotificationJob::dispatch($event->booking, 'guest');
    }
}
