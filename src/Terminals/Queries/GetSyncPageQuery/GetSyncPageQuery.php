<?php
declare(strict_types=1);

namespace Src\Terminals\Queries\GetSyncPageQuery;

final class GetSyncPageQuery
{
    /** @var array|null */
    private $terminalIds;

    public function __construct(?array $terminalIds)
    {
        $this->terminalIds = $terminalIds;
    }

    public function getTerminalIds(): ?array
    {
        return $this->terminalIds;
    }
}
