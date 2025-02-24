<?php

namespace App\Listeners\TripTickets;

use App\Events\TripTickets\ChangeTripTicketStatus;

class ChangeTripTicketStatusAction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ChangeTripTicketStatus $event
     * @return void
     */
    public function handle(ChangeTripTicketStatus $event)
    {
        $event->getTripTicket()->update([
            'status' => $event->getStatus(),
        ]);
    }
}
