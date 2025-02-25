<?php

namespace App\Http\Controllers\TripTickets;

use App\Http\Controllers\Controller;
use App\Models\TripTicket;

class TripTicketAttachPhotosPageController extends Controller
{
    public function __invoke(string $tripTicketId)
    {
        $tripTicket = TripTicket::where('uuid', '=', $tripTicketId)->firstOrFail();

        return redirect()->route('anketa.verification.page', ['uuid' => $tripTicket->medicForm->uuid]);
    }
}
