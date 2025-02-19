<?php

namespace App\Actions\Terminals\CreateTerminal;

final class CreateTerminalCommand
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $timezone;
    /**
     * @var int
     */
    private $companyId;
    /**
     * @var int
     */
    private $blocked;
    /**
     * @var int
     */
    private $pvId;
    /**
     * @var int|null
     */
    private $stampId;

    /**
     * @param string $name
     * @param string $timezone
     * @param int $companyId
     * @param int $blocked
     * @param int $pvId
     * @param int|null $stampId
     */
    public function __construct(
        string $name,
        string $timezone,
        int    $companyId,
        int    $blocked,
        int    $pvId,
        ?int   $stampId
    )
    {
        $this->name = $name;
        $this->timezone = $timezone;
        $this->companyId = $companyId;
        $this->blocked = $blocked;
        $this->pvId = $pvId;
        $this->stampId = $stampId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function getBlocked(): int
    {
        return $this->blocked;
    }

    public function getPvId(): int
    {
        return $this->pvId;
    }

    public function getStampId(): ?int
    {
        return $this->stampId;
    }
}