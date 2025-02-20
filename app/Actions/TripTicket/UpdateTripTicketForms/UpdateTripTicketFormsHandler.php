<?php

namespace App\Actions\TripTicket\UpdateTripTicketForms;

use App\Enums\FormLogActionTypesEnum;
use App\Enums\TripTicketActionType;
use App\Events\Forms\FormAction;
use App\Events\Forms\FormDetachedFromTripTicket;
use App\Events\TripTickets\TripTicketAction;
use App\Models\Forms\Form;
use App\Models\TripTicket;
use Auth;
use Carbon\Carbon;

final class UpdateTripTicketFormsHandler
{
    public function handle(UpdateTripTicketFormsAction $action): TripTicket
    {
        $user = Auth::user();
        $tripTicket = $action->getTripTicket();
        $oldMedicForm = $tripTicket->medic_form_id;
        $oldTechForm = $tripTicket->tech_form_id;
        $startDate = $tripTicket->start_date;
        $driver = $tripTicket->driver_id;
        $car = $tripTicket->car_id;

        if ($action->getMedicForm() && $action->getMedicForm()->date && $startDate === null) {
            $tripTicket->start_date = $action->getMedicForm()->date;
            $tripTicket->period_pl = Carbon::parse($action->getMedicForm()->date)->format('Y-m');
        }

        if ($action->getMedicForm() && $action->getMedicForm()->driver_id && $driver === null) {
            $tripTicket->driver_id = $action->getMedicForm()->driver_id;
        }

        $tripTicket->fill([
            'medic_form_id' => $action->getMedicForm()
                ? $action->getMedicForm()->id
                : null,
        ]);

        if ($oldMedicForm && $action->getMedicForm() === null) {
            event(new TripTicketAction($user, $tripTicket, TripTicketActionType::detachMedicForm()));
            event(new FormDetachedFromTripTicket($user, Form::findOrFail($oldMedicForm), $tripTicket, FormLogActionTypesEnum::DETACH_FROM_TRIP_TICKET));
        }

        if ($action->getMedicForm() !== null && $action->getMedicForm()->id !== $oldMedicForm) {
            event(new TripTicketAction($user, $tripTicket, TripTicketActionType::attachMedicForm()));
            if ($oldMedicForm !== null) {
                event(new FormDetachedFromTripTicket($user, Form::findOrFail($oldMedicForm), $tripTicket, FormLogActionTypesEnum::DETACH_FROM_TRIP_TICKET));
            }
            event(new FormDetachedFromTripTicket($user, $action->getMedicForm(), $tripTicket, FormLogActionTypesEnum::ATTACH_TO_TRIP_TICKET));
        }

        $tripTicket->save();

        if ($action->getTechForm() && $action->getTechForm()->date && $startDate === null) {
            $tripTicket->start_date = $action->getTechForm()->date;
            $tripTicket->period_pl = Carbon::parse($action->getTechForm()->date)->format('Y-m');
        }

        if ($action->getTechForm() && $action->getTechForm()->driver_id && $driver === null) {
            $tripTicket->driver_id = $action->getTechForm()->driver_id;
        }

        if ($action->getTechForm() && $action->getTechForm()->details->car_id && $car === null) {
            $tripTicket->car_id = $action->getTechForm()->details->car_id;
        }

        $tripTicket->fill([
            'tech_form_id' => $action->getTechForm()
                ? $action->getTechForm()->id
                : null,
        ]);

        if ($oldTechForm && $action->getTechForm() === null) {
            event(new TripTicketAction($user, $tripTicket, TripTicketActionType::detachTechForm()));
            event(new FormDetachedFromTripTicket($user, Form::findOrFail($oldTechForm), $tripTicket, FormLogActionTypesEnum::DETACH_FROM_TRIP_TICKET));
        }

        if ($action->getTechForm() !== null && $action->getTechForm()->id !== $oldTechForm) {
            event(new TripTicketAction($user, $tripTicket, TripTicketActionType::attachTechForm()));
            if ($oldTechForm !== null) {
                event(new FormDetachedFromTripTicket($user, Form::findOrFail($oldTechForm), $tripTicket, FormLogActionTypesEnum::DETACH_FROM_TRIP_TICKET));
            }
            event(new FormDetachedFromTripTicket($user, $action->getTechForm(), $tripTicket, FormLogActionTypesEnum::ATTACH_TO_TRIP_TICKET));
        }

        $tripTicket->save();

        return $tripTicket;
    }
}
