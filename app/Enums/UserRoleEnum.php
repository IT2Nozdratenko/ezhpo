<?php

namespace App\Enums;

use ReflectionClass;

final class UserRoleEnum
{
    const TECH = 1; // Контролёр ТС
    const MEDIC = 2; // Медицинский сотрудник
    const DRIVER = 3; // Водитель
    const OPERATOR_SDPO = 4; // Оператор СДПО
    const MANAGER = 5; // Менеджер по работе с клиентами
    const CLIENT = 6; // Клиент
    const ENGINEER_BDD = 7; // Инженер по безопасности дорожного движения
    const ADMIN = 8; // Администратор
    const TERMINAL = 9; // Терминал
    const ROLE_223581000 = 24; // Руководитель филиала
    const ROLE_898491000 = 26; // Test asdiugasdilasgd asdiagsdiuasdg asdasudhgasiduhas dasduhasdiuahdia...
    const ROLE_18641000 = 27; // test
    const ROLE_268671000 = 28; // НИКТО
    const ROLE_262761000 = 29; // Коммерческий директор
    const HEAD_OPERATOR_SDPO = 30; // Старший оператор СДПО
    const ROLE_450231000 = 31; // Наблюдатель
    const INTEGRATION_1C = 32; // Интеграция 1С

    /**
     * @var int
     */
    private $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public static function from(int $value): UserRoleEnum
    {
        if (!array_key_exists($value, array_flip(self::cases()))) {
            throw new \LogicException("Unsupported user role value '{$value}'");
        }

        return new self($value);
    }

    private static function cases(): array
    {
        $oClass = new ReflectionClass(static::class);

        return $oClass->getConstants();
    }
}
