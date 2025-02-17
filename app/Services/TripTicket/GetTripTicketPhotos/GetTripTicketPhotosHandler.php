<?php

namespace App\Services\TripTicket\GetTripTicketPhotos;

use Illuminate\Support\Facades\Storage;

final class GetTripTicketPhotosHandler
{
    public function handle(GetTripTicketPhotosCommand $command): array
    {
        return array_reduce($command->getTripTicket()->photos ?: [], function ($carry, $photo) {
            $photo['url'] = Storage::disk('public')->url($photo['path']);

            $carry[] = $photo;

            return $carry;
        }, []);
    }
}
