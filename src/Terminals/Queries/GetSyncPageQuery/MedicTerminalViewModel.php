<?php
declare(strict_types=1);

namespace Src\Terminals\Queries\GetSyncPageQuery;

use Carbon\Carbon;

final class MedicTerminalViewModel
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $eds;

    /** @var Carbon */
    private $validity_eds_start;

    /** @var Carbon */
    private $validity_eds_end;

    /** @var string|null */
    private $pvName;

    /**
     * @param int $id
     * @param string $name
     * @param string|null $eds
     * @param Carbon $validity_eds_start
     * @param Carbon|null $validity_eds_end
     * @param string|null $pvName
     */
    public function __construct(
        int    $id,
        string $name,
        $eds,
        Carbon $validity_eds_start,
        $validity_eds_end,
        $pvName
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->eds = $eds;
        $this->validity_eds_start = $validity_eds_start;
        $this->validity_eds_end = $validity_eds_end;
        $this->pvName = $pvName;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'eds' => $this->eds,
            'validity_eds_start' => optional($this->validity_eds_start)->format('Y-m-d'),
            'validity_eds_end' => optional($this->validity_eds_end)->format('Y-m-d'),
            'pv_name' => $this->pvName,
        ];
    }

}
