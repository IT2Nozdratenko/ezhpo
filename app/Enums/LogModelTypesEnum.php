<?php

namespace App\Enums;

use App\Car;
use App\Company;
use App\Driver;
use App\Models\Contract;
use App\Models\TripTicket;
use App\Product;
use App\Stamp;
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
            Product::class => 'product',
            User::class => 'users',
            Stamp::class => 'stamps',
            TripTicket::class => 'trip_ticket'
        ];
    }

    public static function labels(): array
    {
        return [
            Driver::class => 'Водители',
            Company::class => 'Компании',
            Car::class => 'ТС',
            Contract::class => 'Договора',
            Product::class => 'Услуги',
            User::class => 'Пользователи',
            Stamp::class => 'Штампы',
            TripTicket::class => 'Путевой лист'
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
