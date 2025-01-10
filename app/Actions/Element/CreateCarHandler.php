<?php

namespace App\Actions\Element;

use App\Car;
use App\Company;
use App\Enums\UserActionTypesEnum;
use App\Events\Relations\Attached;
use App\Events\UserActions\ClientAddRecord;
use App\Models\Contract;
use Auth;
use Exception;

class CreateCarHandler extends AbstractCreateElementHandler implements CreateElementHandlerInterface
{
    /**
     * @throws Exception
     */
    public function handle($data)
    {
        $companyId = $data['company_id'];
        $company = Company::query()->find($companyId);
        if (!$company) {
            throw new Exception('Компания не найдена');
        }

        $existItem = Car::query()
            ->where('company_id', $companyId)
            ->where('gos_number', trim($data['gos_number']))
            ->first();
        if ($existItem) {
            throw new Exception('Найден дубликат по гос.номеру Автомобиля');
        }

        $validator = function (int $hashId) {
            if (Car::where('hash_id', $hashId)->first()) {
                return false;
            }

            return true;
        };

        $data['hash_id'] = $this->generateHashId(
            $validator,
            config('app.hash_generator.car.min'),
            config('app.hash_generator.car.max'),
            config('app.hash_generator.car.tries')
        );

        $attributesToSync = ['products_id'];
        foreach ($attributesToSync as $attributeName) {
            $attributeValue = $company->getAttribute($attributeName);

            if (!$attributeValue) {
                continue;
            }

            $data[$attributeName] = $attributeValue;
        }

        $created = $this->createElement($data);

        $user = Auth::user();
        if ($user) {
            event(new ClientAddRecord($user, UserActionTypesEnum::ADD_CAR_VIA_FORM));
        }

        /** @var Contract $contract */
        $contract = Contract::query()
            ->where('company_id', $companyId)
            ->where('main_for_company', 1)
            ->first();

        if ($contract) {
            $contract->cars()->attach($created->id);
            event(new Attached($contract, [$created->id], Car::class));
        }

        return $created;
    }
}
