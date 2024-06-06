<?php

namespace App\Enums;

use App\Car;
use App\Company;
use App\Driver;
use App\Models\Contract;
use App\Models\Service;
use App\Product;
use App\User;

class LogModelTypesEnum
{
    public static function fieldPromptsTypeMap(): array
    {
        return [
            Driver::class => 'driver',
            Company::class => 'company',
            Car::class => 'car',
            Contract::class => 'contracts',
            Service::class => 'product',
            Product::class => 'product',
            User::class => 'users'
        ];
    }

    public static function labels(): array
    {
        return [
            Driver::class => 'Водители',
            Company::class => 'Компании',
            Car::class => 'ТС',
            Contract::class => 'Договора',
            Service::class => 'Услуги',
            Product::class => 'Услуги',
            User::class => 'Пользователи'
        ];
    }

    public static function label(string $value = null): string
    {
        if (empty($value)) {
            return '';
        }

        return self::labels()[$value] ?? $value;
    }
}
