<?php

namespace App\Actions\TripTicket\DeleteTripTickets;

use App\Events\Forms\FormDetachedFromTripTicket;
use App\Models\TripTicket;
use App\User;
use Illuminate\Support\Carbon;

final class TrashTripTicketHandler
{
    public function handle(TripTicket $tripTicket, $action, User $user)
    {
        if (! $action) {
            $this->restoreTripTicket($tripTicket, $user);
        } else {
            $this->deleteTripTicket($tripTicket, $user);
        }
    }

    private function deleteTripTicket(TripTicket $tripTicket, User $user)
    {
        /** Очистка связанных осмотров и сущностей для их разблокировки */
        if ($tripTicket->medicForm) {
            $tripTicket->medic_form_id = null;
            event(new FormDetachedFromTripTicket($user, $tripTicket->medicForm, $tripTicket));
        }

        if ($tripTicket->techForm) {
            $tripTicket->tech_form_id = null;
            event(new FormDetachedFromTripTicket($user, $tripTicket->techForm, $tripTicket));
        }

        $tripTicket->deleted_at = Carbon::now();
        $tripTicket->deleted_id = $user->id;

        $tripTicket->save();
    }

    private function restoreTripTicket(TripTicket $tripTicket, User $user)
    {
        $tripTicket->deleted_at = null;
        $tripTicket->deleted_id = $user->id;

        $tripTicket->save();
    }
}
