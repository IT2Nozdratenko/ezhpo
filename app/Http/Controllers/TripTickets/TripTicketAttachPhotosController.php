<?php

namespace App\Http\Controllers\TripTickets;

use App\Actions\TripTicket\UpdateTripTicketPhotos\UpdateTripTicketPhotosAction;
use App\Actions\TripTicket\UpdateTripTicketPhotos\UpdateTripTicketPhotosHandler;
use App\Http\Controllers\Controller;
use App\Models\TripTicket;
use Illuminate\Http\Request;
use Throwable;

class TripTicketAttachPhotosController extends Controller
{
    public function __invoke(string $tripTicketId, Request $request, UpdateTripTicketPhotosHandler  $handler)
    {
        try {
//            $request->validate([
//                'photos' => 'required|array',
//                'photos.*' => 'image|mimes:jpeg,png|max:8192',
//            ]);

            $tripTicket = TripTicket::where('uuid', '=', $tripTicketId)->firstOrFail();

            if ($request->hasFile('photos')) {
                $handler->handle(new UpdateTripTicketPhotosAction(
                    $tripTicket,
                    $request->file('photos'),
                ));
            }
        } catch (Throwable $exception) {
            return redirect()
                ->back()
                ->withErrors([$exception->getMessage()]);
        }

        return redirect()->route('trip-tickets.attach-photos-page', ['id' => $tripTicketId]);
    }
}
