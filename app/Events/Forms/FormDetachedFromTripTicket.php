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
     * @var string
     */
    private $type;

    /**
     * @param User $user
     * @param Form $form
     * @param TripTicket $tripTicket
     * @param string $type
     */
    public function __construct(
        User       $user,
        Form       $form,
        TripTicket $tripTicket,
        string     $type
    ) {
        $this->user = $user;
        $this->form = $form;
        $this->tripTicket = $tripTicket;
        $this->type = $type;
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

    public function getType(): string
    {
        return $this->type;
    }
}
