<?php
declare(strict_types=1);

namespace Src\Terminals\ValueObjects;

final class Settings
{
    /** @var SettingsContainer */
    private $main;

    /** @var SettingsContainer */
    private $system;

    public function __construct(SettingsContainer $main, SettingsContainer $system)
    {
        $this->main = $main;
        $this->system = $system;
    }

    public function getMain(): SettingsContainer
    {
        return $this->main;
    }

    public function getSystem(): SettingsContainer
    {
        return $this->system;
    }

    public function toArray(): array
    {
        return [
            'main' => $this->main->toArray(),
            'system' => $this->system->toArray(),
        ];
    }
}
