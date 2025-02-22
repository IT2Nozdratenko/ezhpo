<?php

namespace App\Enums;

use LogicException;
use ReflectionClass;

abstract class Enum
{
    /** @var string */
    public $value;

    protected function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param static|string $value
     * @return bool
     */
    public function equal($value): bool
    {
        if ($value instanceof static) {
            return $this->equal($value->value());
        }

        return $value === $this->value;
    }

    /**
     * @param  string  $value
     * @return static
     * @throws LogicException
     */
    abstract public static function from(string $value): self;

    /**
     * @param  string  $value
     * @return static|null
     */
    public static function tryFrom(string $value)
    {
        try {
            return static::from($value);
        } catch (LogicException $exception) {
            return null;
        }
    }

    /**
     * @return array<string>
     */
    public static function cases(): array
    {
        $oClass = new ReflectionClass(static::class);

        return $oClass->getConstants();
    }
}
