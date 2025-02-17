<?php

namespace App\Http\Controllers\TripTickets;

use App\Http\Controllers\Controller;
use App\Models\TripTicket;
use App\Services\TripTicket\GetTripTicketPhotos\GetTripTicketPhotosCommand;
use App\Services\TripTicket\GetTripTicketPhotos\GetTripTicketPhotosHandler;

class TripTicketAttachPhotosPageController extends Controller
{
    public function __invoke(string $tripTicketId, GetTripTicketPhotosHandler $handler)
    {
        $tripTicket = TripTicket::where('uuid', '=', $tripTicketId)->firstOrFail();

        $photos = $handler->handle(new GetTripTicketPhotosCommand($tripTicket));

        return view('trip-tickets.attach-photos', ['id' => $tripTicketId, 'photos' => $photos]);
    }
}
