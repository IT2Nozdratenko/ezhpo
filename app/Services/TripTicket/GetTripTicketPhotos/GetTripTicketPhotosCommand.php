<?php

namespace App\Services\TripTicket\GetTripTicketPhotos;

use App\Models\TripTicket;

final class GetTripTicketPhotosCommand
{
    /**
     * @var TripTicket
     */
    private $tripTicket;

    /**
     * @param TripTicket $tripTicket
     */
    public function __construct(TripTicket $tripTicket)
    {
        $this->tripTicket = $tripTicket;
    }

    public function getTripTicket(): TripTicket
    {
        return $this->tripTicket;
    }
}
