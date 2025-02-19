<?php

namespace App\Actions\Terminals\UpdateTerminalDevices;

final class TerminalDeviceItem
{
    /**
     * @var string
     */
    private $slug;
    /**
     * @var string
     */
    private $serialNumber;

    /**
     * @param string $slug
     * @param string $serialNumber
     */
    public function __construct(string $slug, string $serialNumber)
    {
        $this->slug = $slug;
        $this->serialNumber = $serialNumber;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }
}