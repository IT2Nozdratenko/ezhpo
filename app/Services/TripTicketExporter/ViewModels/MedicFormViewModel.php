<?php

namespace App\Services\TripTicketExporter\ViewModels;

use Carbon\Carbon;

final class MedicFormViewModel
{
    /**
     * @var Carbon|null
     */
    private $date;
    /**
     * @var string|null
     */
    private $username;
    /**
     * @var string|null
     */
    private $stamp;

    /**
     * @param Carbon|null $date
     * @param string|null $username
     * @param StampViewModel|null $stamp
     */
    public function __construct(
        ?Carbon   $date,
        ?string   $username,
        ?StampViewModel   $stamp
    ) {
        $this->date = $date;
        $this->username = $username;
        $this->stamp = $stamp ?? StampViewModel::default();
    }

    public function getDate(): ?Carbon
    {
        return $this->date;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getStamp(): StampViewModel
    {
        return $this->stamp;
    }
}
