<?php

namespace App\Http\Controllers\TripTickets;

use App\Http\Controllers\Controller;
use App\Models\TripTicket;
use App\Services\TripTicket\GetTripTicketPhotos\GetTripTicketPhotosCommand;
use App\Services\TripTicket\GetTripTicketPhotos\GetTripTicketPhotosHandler;
use Illuminate\Http\Request;

class GetTripTicketPhotosController extends Controller
{
    public function __invoke(Request $request, GetTripTicketPhotosHandler $handler)
    {
        $tripTicket = TripTicket::where('uuid', '=', $request->query('id'))->firstOrFail();

        $photos = $handler->handle(new GetTripTicketPhotosCommand($tripTicket));

        return response()->json(['photos' => $photos]);
    }
}
