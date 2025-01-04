<?php

namespace App\Console\Commands\Forms;

use App\Enums\FormFixStatusEnum;
use InvalidArgumentException;

class FormFIxStatusConverter
{
    public static function fromStatuses(array $statuses = []): int
    {
        if (count($statuses) === 0) {
            return FormFixStatusEnum::FIXED;
        }

        $statuses = array_unique($statuses);

        $statusBits = [
            FormFixStatusEnum::FIXED => 0,
            FormFixStatusEnum::INVALID_POINT_ID => 0,
            FormFixStatusEnum::INVALID_DRIVER_ID => 0,
            FormFixStatusEnum::INVALID_CAR_ID => 0,
            FormFixStatusEnum::INVALID_COMPANY_ID => 0,
            FormFixStatusEnum::INVALID_USER_ID => 0,
            FormFixStatusEnum::INVALID_TERMINAL_ID => 0
        ];

        foreach ($statuses as $status) {
            $statusBits[$status] = 1;
        }

        return intval(base_convert(implode("", array_reverse(array_values($statusBits))), 2, 10));
    }

    public static function toStatuses(int $status): array
    {
        if ($status < FormFixStatusEnum::UNPROCESSED) throw new InvalidArgumentException("Статус не может быть меньше " . FormFixStatusEnum::UNPROCESSED);

        if ($status === FormFixStatusEnum::UNPROCESSED) return [];

        if ($status === FormFixStatusEnum::FIXED) return [];

        $allStatuses = [
            FormFixStatusEnum::FIXED,
            FormFixStatusEnum::INVALID_POINT_ID,
            FormFixStatusEnum::INVALID_DRIVER_ID,
            FormFixStatusEnum::INVALID_CAR_ID,
            FormFixStatusEnum::INVALID_COMPANY_ID,
            FormFixStatusEnum::INVALID_USER_ID,
            FormFixStatusEnum::INVALID_TERMINAL_ID
        ];

        $statuses = [];

        $statusBits = str_split(strrev(base_convert("$status", 10, 2)));

        foreach ($statusBits as $index => $bit) {
            if ($bit === '1' && isset($allStatuses[$index])) {
                $statuses[] = $allStatuses[$index];
            }
        }

        return $statuses;
    }
}
