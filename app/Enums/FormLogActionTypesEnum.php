<?php

namespace App\Enums;

use Illuminate\Support\Collection;

class FormLogActionTypesEnum
{
    const UPDATING = 'updating';
    const DELETING = 'deleting';
    const RESTORING = 'restoring';
    const APPROVAL = 'approval';
    const QUEUE_PROCESSING = 'queue_processing';
    const SET_FEEDBACK = 'set_feedback';
    const ATTACH_TO_TRIP_TICKET = 'attach_to_trip_ticket';
    const DETACH_FROM_TRIP_TICKET = 'detach_from_trip_ticket';
    const DETACH_TRIP_TICKET = 'detach_trip_ticket';

    public static function labels(): array
    {
        return [
            self::UPDATING => 'Редактирование',
            self::DELETING => 'Удаление',
            self::RESTORING => 'Восстановление',
            self::APPROVAL => 'Утверждение',
            self::QUEUE_PROCESSING => 'Обработка очереди',
            self::SET_FEEDBACK => 'Оценка осмотра',
            self::ATTACH_TO_TRIP_TICKET => 'Привязан к путевому листу',
            self::DETACH_FROM_TRIP_TICKET => 'Отвязан от путевого листа',
            self::DETACH_TRIP_TICKET => 'Удаление из ПЛ'
        ];
    }

    public static function options(): Collection
    {
        return collect(self::labels())
            ->map(function ($value, $key) {
                return [
                    'id' => $key,
                    'text' => $value
                ];
            })
            ->values();
    }

    public static function label(string $value): string
    {
        return self::labels()[$value] ?? 'Неизвестное действие';
    }

    public static function values(): array
    {
        return [
            self::UPDATING,
            self::DELETING,
            self::RESTORING,
            self::APPROVAL,
            self::QUEUE_PROCESSING,
            self::SET_FEEDBACK,
            self::DETACH_TRIP_TICKET,
            self::ATTACH_TO_TRIP_TICKET,
            self::DETACH_FROM_TRIP_TICKET,
        ];
    }
}
