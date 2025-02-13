<?php
declare(strict_types=1);

namespace Src\Terminals\ValueObjects;

final class SettingsContainer
{
    /** @var array  */
    private $settings = [];

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getSetting(string $key)
    {
        return $this->settings[$key] ?? null;
    }

    public function setSetting(string $key, $value): void
    {
        $this->settings[$key] = $value;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
