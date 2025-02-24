<?php

namespace App\Enums;

final class TripTicketStatus
{
    const CREATED = 'created';
    const ACTIVATED = 'activated';
    const APPROVED = 'approved';
    const PRINTED = 'printed';

    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function created(): self
    {
        return new self(self::CREATED);
    }

    public static function activated(): self
    {
        return new self(self::ACTIVATED);
    }

    public static function approved(): self
    {
        return new self(self::APPROVED);
    }

    public static function printed(): self
    {
        return new self(self::PRINTED);
    }

    public static function fromString(string $value): self
    {
        switch ($value) {
            case self::CREATED:
                return self::created();
            case self::ACTIVATED:
                return self::activated();
            case self::APPROVED:
                return self::approved();
            case self::PRINTED:
                return self::printed();
            default:
                throw new \DomainException('Unknown trip ticket status: ' . $value);
        }
    }

    public static function labels(): array
    {
        return [
            self::CREATED => 'Создан',
            self::ACTIVATED => 'Активирован',
            self::APPROVED => 'Утвержден',
            self::PRINTED => 'Напечатан',
        ];
    }

    public static function getLabel(string $value): string
    {
        return self::labels()[$value];
    }
}
