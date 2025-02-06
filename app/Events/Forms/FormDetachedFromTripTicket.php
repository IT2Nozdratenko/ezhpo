<?php

namespace App\Events\Forms;

use App\Models\Forms\Form;
use App\Models\TripTicket;
use App\User;
use Illuminate\Queue\SerializesModels;

class FormDetachedFromTripTicket
{
    use SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Form
     */
    private $form;

    /**
     * @var TripTicket
     */
    private $tripTicket;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, Form $form, TripTicket $tripTicket)
    {
        $this->user = $user;
        $this->form = $form;
        $this->tripTicket = $tripTicket;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getTripTicket(): TripTicket
    {
        return $this->tripTicket;
    }
}
