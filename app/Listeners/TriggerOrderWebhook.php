<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendOrderWebhook;

class TriggerOrderWebhook
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
    public function handle(OrderPlaced $event): void
    {
        dispatch(new SendOrderWebhook($event->order));
    }
}
