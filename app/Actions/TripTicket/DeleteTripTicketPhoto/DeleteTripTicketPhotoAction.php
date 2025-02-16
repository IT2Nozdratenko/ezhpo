<?php

namespace App\Actions\TripTicket\DeleteTripTicketPhoto;

use App\Models\TripTicket;

final class DeleteTripTicketPhotoAction
{
    /**
     * @var TripTicket
     */
    private $tripTicket;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @param TripTicket $tripTicket
     * @param string $filePath
     */
    public function __construct(TripTicket $tripTicket, string $filePath)
    {
        $this->tripTicket = $tripTicket;
        $this->filePath = $filePath;
    }

    public function getTripTicket(): TripTicket
    {
        return $this->tripTicket;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
