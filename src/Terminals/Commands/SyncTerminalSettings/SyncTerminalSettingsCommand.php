<?php
declare(strict_types=1);

namespace Src\Terminals\Commands\SyncTerminalSettings;

use Src\Terminals\ValueObjects\Settings;

final class SyncTerminalSettingsCommand
{
    /** @var array<int> */
    private $terminalId;

    /** @var Settings */
    private $terminalSettings;

    /**
     * @param array<int> $terminalIds
     * @param Settings $terminalSettings
     */
    public function __construct(array $terminalIds, Settings $terminalSettings)
    {
        $this->terminalId = $terminalIds;
        $this->terminalSettings = $terminalSettings;
    }

    public function getTerminalId(): array
    {
        return $this->terminalId;
    }

    public function getTerminalSettings(): Settings
    {
        return $this->terminalSettings;
    }
}
