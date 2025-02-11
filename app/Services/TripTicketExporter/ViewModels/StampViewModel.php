<?php

namespace App\Services\TripTicketExporter\ViewModels;

use App\Stamp;

final class StampViewModel
{
    /**
     * @var string
     */
    private $reqName;

    /**
     * @var string
     */
    private $license;

    /**
     * @param string $reqName
     * @param string $license
     */
    public function __construct(string $reqName, string $license)
    {
        $this->reqName = $reqName;
        $this->license = $license;
    }

    /**
     * @return string
     */
    public function getReqName(): string
    {
        return $this->reqName;
    }

    /**
     * @return string
     */
    public function getLicense(): string
    {
        return $this->license;
    }

    private static function fromStamp(Stamp $stamp): self
    {
        return new self($stamp->company_name, $stamp->licence);
    }

    public static function fromStampOrDefault(?Stamp $stamp): self
    {
        if ($stamp) {
            return static::fromStamp($stamp);
        }

        return static::default();
    }

    public static function default(): self
    {
        return new self(
            config('trip-ticket.print.4s.stamps.medic.reqName'),
            config('trip-ticket.print.4s.stamps.medic.license'),
        );
    }

    public function toArray(): array
    {
        return [
            'stamp_head' => $this->getReqName(),
            'stamp_licence' => $this->getLicense()
        ];
    }
}
