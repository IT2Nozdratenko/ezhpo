<?php

namespace App\Listeners\Forms;

use App\Enums\FormLogActionTypesEnum;
use App\Events\Forms\FormAction;
use App\Events\Forms\FormDetachedFromTripTicket;
use App\Models\FormEvent;

class LogFormDetachFromTripTicket
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
     * @param FormAction $event
     * @return void
     */
    public function handle(FormDetachedFromTripTicket $event)
    {
        FormEvent::create([
            'user_id' => $event->getUser()->id,
            'event_type' => FormLogActionTypesEnum::DETACH_TRIP_TICKET,
            'payload' => [
                [
                    'name' => 'trip_ticket_id',
                    'oldValue' => $event->getTripTicket()->id,
                    'newValue' => null
                ]
            ],
            'form_uuid' => $event->getForm()->uuid,
            'model_type' => get_class($event->getForm()->details),
        ]);
    }
}
