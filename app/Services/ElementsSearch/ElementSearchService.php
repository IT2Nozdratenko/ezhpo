<?php

namespace App\Services\ElementsSearch;

use App\Car;
use App\Company;
use App\Driver;
use App\Dto\ElementDto;
use App\Enums\LogModelTypesEnum;
use App\Models\Contract;
use App\Models\TripTicket;
use App\Product;
use App\Stamp;
use App\User;
use Illuminate\Database\Eloquent\Model;

class ElementSearchService implements ElementsSearchServiceInterface
{
    const MODELS = [
        Car::class => [
            'select' => [
                'hash_id',
                'id',
                'gos_number as name'
            ],
            'fields' => [
                'hash_id',
            ]
        ],
        Company::class => [
            'select' => [
                'hash_id',
                'id',
                'name'
            ],
            'fields' => [
                'hash_id',
            ]
        ],
        Product::class => [
            'select' => [
                'hash_id',
                'id',
                'name'
            ],
            'fields' => [
                'hash_id',
            ]
        ],
        Driver::class => [
            'select' => [
                'hash_id',
                'id',
                'fio as name'
            ],
            'fields' => [
                'hash_id',
            ]
        ],
        //TODO: а почему тут нет?
        Contract::class => [
            'select' => [
                'id as hash_id',
                'id',
                'name'
            ],
            'fields' => []
        ],
        User::class => [
            'select' => [
                'id as hash_id',
                'id',
                'name'
            ],
            'fields' => [
                'hash_id',
            ]
        ],
        Stamp::class => [
            'select' => [
                'id as hash_id',
                'id',
                'company_name as name'
            ],
            'fields' => []
        ],
        TripTicket::class => [
            'select' => [
                'id as hash_id',
                'id',
                'ticket_number as name'
            ],
            'fields' => []
        ]
    ];

    /**
     * @inheritDoc
     */
    public function search(string $identifier): array
    {
        $identifier = trim($identifier);

        if (strlen($identifier) === 0) return [];

        $items = [];

        foreach (self::MODELS as $modelName => $config) {
            /** @var Model $model */
            $model = app($modelName);

            $query = $model::query()
                ->withTrashed()
                ->select($config['select'])
                ->where('id', $identifier);

            foreach ($config['fields'] as $fieldName) {
                $query->orWhere($fieldName, $identifier);
            }

            $items = $query
                ->get()
                ->reduce(function ($carry, $item) use ($modelName) {
                    $carry[] = new ElementDto(
                        $item->id,
                        $item->hash_id,
                        LogModelTypesEnum::label($modelName),
                        $item->name
                    );

                    return $carry;
                }, $items);
        }

        return $items;
    }
}
