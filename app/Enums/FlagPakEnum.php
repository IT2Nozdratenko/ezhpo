<?php

namespace App\Enums;

class FlagPakEnum
{
    const SDPO_A = 'СДПО А';
    const SDPO_R = 'СДПО Р';
    const INTERNAL = 'Очный';

    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        switch ($value) {
            case self::SDPO_A:
                return self::sdpoA();
            case self::SDPO_R:
                return self::sdpoR();
            case self::INTERNAL:
                return self::internal();
            default:
                throw new \DomainException('Unknown flag pak: ' . $value);
        }
    }

    public static function internal(): self
    {
        return new self(self::INTERNAL);
    }

    public static function sdpoA(): self
    {
        return new self(self::SDPO_A);
    }

    public static function sdpoR(): self
    {
        return new self(self::SDPO_R);
    }

    public function __toString(): string
    {
        return $this->value();
    }

    public static function cases(): array
    {
        return [
            self::SDPO_A,
            self::SDPO_R,
            self::INTERNAL,
        ];
    }
}
