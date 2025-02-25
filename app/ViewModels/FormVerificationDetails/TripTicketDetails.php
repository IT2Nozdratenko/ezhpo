<?php

namespace App\ViewModels\FormVerificationDetails;

use App\Models\TripTicket;

final class TripTicketDetails
{
    /**
     * @var TripTicket
     */
    private $tripTicket;

    /**
     * @var array
     */
    private $photos;

    /**
     * @param TripTicket $tripTicket
     * @param array $photos
     */
    public function __construct(TripTicket $tripTicket, array $photos)
    {
        $this->tripTicket = $tripTicket;
        $this->photos = $photos;
    }

    public function getTripTicket(): TripTicket
    {
        return $this->tripTicket;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }
}
