<?php

namespace App\Events\TripTickets;

use App\Enums\TripTicketActionType;
use App\Models\TripTicket;
use App\User;
use Illuminate\Queue\SerializesModels;

class LogTripTicket
{
    use SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var TripTicket
     */
    private $tripTicket;

    /**
     * @var TripTicketActionType
     */
    private $type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, TripTicket $tripTicket, TripTicketActionType $type)
    {
        $this->user = $user;
        $this->tripTicket = $tripTicket;
        $this->type = $type;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTripTicket(): TripTicket
    {
        return $this->tripTicket;
    }

    public function getType(): TripTicketActionType
    {
        return $this->type;
    }
}
