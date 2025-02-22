<?php

namespace App\Actions\Terminals\GetTerminalsConnectionStatus;

final class GetTerminalsConnectionStatusParams
{
    /**
     * @var int[]
     */
    private $ids;

    /**
     * @param int[] $ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}