<?php

namespace App\Listeners\TripTickets;

use App\Events\TripTickets\LogTripTicket;
use App\Models\TripTicketLog;

class LogTripTicketAction
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
     * @param LogTripTicket $event
     * @return void
     */
    public function handle(LogTripTicket $event)
    {
        $logData = $this->logModel($event->getTripTicket());

        if (count($logData) === 0) {
            return;
        }

        TripTicketLog::create([
            'trip_ticket_id' => $event->getTripTicket()->uuid,
            'user_id' => $event->getUser()->id,
            'type' => $event->getType(),
            'payload' => $logData,
        ]);
    }

    private function logModel($form): array
    {
        $logData = [];

        foreach ($form->getDirty() as $attribute => $newValue) {
            if (empty($newValue) && empty($form->getOriginal($attribute))) {
                continue;
            }

            $logData[] = [
                'name' => $attribute,
                'oldValue' => $form->getOriginal($attribute),
                'newValue' => $newValue
            ];
        }

        return $logData;
    }
}
