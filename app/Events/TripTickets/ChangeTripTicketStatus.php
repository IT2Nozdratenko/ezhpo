<?php

namespace App\Events\TripTickets;

use App\Enums\TripTicketStatus;
use App\Models\TripTicket;
use Illuminate\Queue\SerializesModels;

class ChangeTripTicketStatus
{
    use SerializesModels;

    /**
     * @var TripTicket
     */
    private $tripTicket;

    /**
     * @var TripTicketStatus
     */
    private $status;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TripTicket $tripTicket, TripTicketStatus $status)
    {
        $this->tripTicket = $tripTicket;
        $this->status = $status;
    }

    public function getTripTicket(): TripTicket
    {
        return $this->tripTicket;
    }

    public function getStatus(): TripTicketStatus
    {
        return $this->status;
    }
}
