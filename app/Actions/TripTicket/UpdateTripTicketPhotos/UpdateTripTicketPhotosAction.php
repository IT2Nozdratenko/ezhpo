<?php

namespace App\Actions\TripTicket\UpdateTripTicketPhotos;

use App\Models\TripTicket;
use Illuminate\Http\UploadedFile;

final class UpdateTripTicketPhotosAction
{
    /**
     * @var TripTicket
     */
    private $tripTicket;

    /**
     * @var UploadedFile[]
     */
    private $photos;

    /**
     * @param TripTicket $tripTicket
     * @param UploadedFile[] $photos
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
