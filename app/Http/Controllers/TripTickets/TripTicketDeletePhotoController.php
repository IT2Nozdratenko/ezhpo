<?php

namespace App\Http\Controllers\TripTickets;

use App\Actions\TripTicket\DeleteTripTicketPhoto\DeleteTripTicketPhotoAction;
use App\Actions\TripTicket\DeleteTripTicketPhoto\DeleteTripTicketPhotoHandler;
use App\Http\Controllers\Controller;
use App\Models\TripTicket;
use Illuminate\Http\Request;

class TripTicketDeletePhotoController extends Controller
{
    public function __invoke(string $tripTicketId, Request $request, DeleteTripTicketPhotoHandler  $handler)
    {
        $tripTicket = TripTicket::where('uuid', '=', $tripTicketId)->firstOrFail();

        $handler->handle(new DeleteTripTicketPhotoAction(
            $tripTicket,
            $request->input('path'),
        ));

        return response()->noContent();
    }
}
