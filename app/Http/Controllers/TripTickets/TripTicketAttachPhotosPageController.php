<?php

namespace App\Http\Controllers\TripTickets;

use App\Http\Controllers\Controller;
use App\Models\TripTicket;
use Illuminate\Support\Facades\Storage;

class TripTicketAttachPhotosPageController extends Controller
{
    public function __invoke(string $tripTicketId)
    {
        $tripTicket = TripTicket::where('uuid', '=', $tripTicketId)->firstOrFail();

        $photos = array_reduce($tripTicket->photos ?: [], function ($carry, $photo) {
            $photo['url'] = Storage::disk('public')->url($photo['path']);

            $carry[] = $photo;

            return $carry;
        });

        return view('trip-tickets.attach-photos', ['id' => $tripTicketId, 'photos' => $photos ?: []]);
    }
}
