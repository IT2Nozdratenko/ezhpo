<?php

namespace App\Actions\Terminals\GetTerminalsToCheck;

final class TerminalsToCheckViewModel
{
    /**
     * @var string[]
     */
    private $lessMonth;
    /**
     * @var string[]
     */
    private $expired;

    /**
     * @param string[] $lessMonth
     * @param string[] $expired
     */
    public function __construct(array $lessMonth, array $expired)
    {
        $this->lessMonth = $lessMonth;
        $this->expired = $expired;
    }

    public function getLessMonth(): array
    {
        return $this->lessMonth;
    }

    public function getExpired(): array
    {
        return $this->expired;
    }
}