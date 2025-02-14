<?php
declare(strict_types=1);

namespace Src\Terminals\Queries\GetSyncPageQuery;

use Src\Terminals\ValueObjects\Settings;

final class GetSyncPageResponse
{
    /** @var array<TerminalViewModel> */
    private $terminals;

    /** @var Settings */
    private $settings;

    /**
     * @param TerminalViewModel[] $terminals
     * @param Settings $settings
     */
    public function __construct(array $terminals, Settings $settings)
    {
        $this->terminals = $terminals;
        $this->settings = $settings;
    }

    public function getTerminals(): array
    {
        return $this->terminals;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function getTerminalsArray(): array
    {
        return array_map(function (TerminalViewModel $terminal) {
            return $terminal->toArray();
        }, $this->terminals);
    }

    public function isDefault(): bool
    {
        return count($this->terminals) === 0;
    }
}
