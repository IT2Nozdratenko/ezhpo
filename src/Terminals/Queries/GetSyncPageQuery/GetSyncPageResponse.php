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

    /** @var array<MedicTerminalViewModel> */
    private $medics;

    /**
     * @param TerminalViewModel[] $terminals
     * @param Settings $settings
     * @param array $medics
     */
    public function __construct(array $terminals, Settings $settings, array $medics)
    {
        $this->terminals = $terminals;
        $this->settings = $settings;
        $this->medics = $medics;
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

    public function getMedicsArray(): array
    {
        return array_map(function (MedicTerminalViewModel $viewModel) {
            return $viewModel->toArray();
        }, $this->medics);
    }

    public function isDefault(): bool
    {
        return count($this->terminals) === 0;
    }
}
