<?php

namespace App\Services\TripTicketExporter\ViewModels;

final class CompanyViewModel
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string|null
     */
    private $whereCall;
    /**
     * @var string|null
     */
    private $reqName;

    /**
     * @param string $name
     * @param string|null $whereCall
     * @param string|null $reqName
     * @param string|null $address
     * @param string|null $ogrn
     */
    public function __construct(string $name, ?string $whereCall, ?string $reqName, ?string $address, ?string $ogrn)
    {
        $this->name = $name;
        $this->whereCall = $whereCall;
        $this->reqName = $reqName;
        $this->address = $address;
        $this->ogrn = $ogrn;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWhereCall(): ?string
    {
        return $this->whereCall;
    }

    public function getReqName(): ?string
    {
        return $this->reqName;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getOgrn(): ?string
    {
        return $this->ogrn;
    }
}
