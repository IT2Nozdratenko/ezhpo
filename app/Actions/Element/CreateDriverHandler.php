<?php

namespace App\Actions\Element;

use App\Anketa;
use App\Company;
use App\Driver;
use App\Enums\UserActionTypesEnum;
use App\Events\Relations\Attached;
use App\Events\UserActions\ClientAddRecord;
use App\Instr;
use App\Models\Contract;
use App\Services\BriefingService;
use App\Services\UserService;
use App\User;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;

class CreateDriverHandler extends AbstractCreateElementHandler implements CreateElementHandlerInterface
{
    /**
     * @throws Exception
     */
    public function handle($data)
    {
        $companyId = $data['company_id'];
        /** @var Company|null $company */
        $company = Company::query()->find($companyId);
        if (!$company) {
            throw new Exception('Компания не найдена');
        }

        $existItem = Driver::query()
            ->where('company_id', $companyId)
            ->where('fio', trim($data['fio']))
            ->first();
        if ($existItem) {
            throw new Exception('Найден дубликат по ФИО Водителя');
        }

        $validator = function (int $hashId) {
            if (Driver::where('hash_id', $hashId)->first()) {
                return false;
            }

            if (User::where('login', $hashId)->first()) {
                return false;
            }

            return true;
        };

        $data['hash_id'] = $this->generateHashId(
            $validator,
            config('app.hash_generator.driver.min'),
            config('app.hash_generator.driver.max'),
            config('app.hash_generator.driver.tries')
        );

        $attributesToSync = ['products_id'];
        foreach ($attributesToSync as $attributeName) {
            $attributeValue = $company->getAttribute($attributeName);

            if (!$attributeValue) {
                continue;
            }

            $data[$attributeName] = $attributeValue;
        }

        /** @var Driver $created */
        $created = $this->createElement($data);

        $user = Auth::user();
        if ($user) {
            event(new ClientAddRecord($user, UserActionTypesEnum::ADD_DRIVER_VIA_FORM));
        }

        UserService::createUserFromDriver($created);

        /** @var Contract $contract */
        $contract = Contract::query()
            ->where('company_id', $companyId)
            ->where('main_for_company', 1)
            ->first();

        if ($contract) {
            $contract->drivers()->attach($created->id);
            event(new Attached($contract, [$created->id], Driver::class));
        }

        if ($company->required_type_briefing) {
            BriefingService::createFirstBriefingForDriver($created, $company);
        }

        return $created;
    }

    protected function createFirstBriefing(Driver $created)
    {
        $company = $created->company;
        $point = $company->point;

        $briefing = Instr::query()
            ->where('is_default', true)
            ->where('type_briefing', 'Вводный')
            ->first();

        $bddUser = User::query()
            ->with(['roles'])
            ->whereHas('roles', function ($queryBuilder) {
                return $queryBuilder->where('id', 7);
            })
            ->get()
            ->random();

        Anketa::create([
            "type_anketa" => "bdd",
            "complaint" => "Нет",
            "type_briefing" => 'Вводный',
            "signature" => "Подписано простой электронной подписью (ПЭП)",
            "condition_visible_sliz" => "Без особенностей",
            "condition_koj_pokr" => "Без особенностей",
            "date" => Carbon::now(),
            "type_view" => "Предрейсовый",

            "user_id" => $bddUser->id,
            "user_name" => $bddUser->name,
            'user_eds' => $bddUser->eds,
            'user_validity_eds_start' => $bddUser->validity_eds_start,
            'user_validity_eds_end' => $bddUser->validity_eds_start,

            "driver_id" => $created->hash_id,
            "driver_fio" => $created->fio,
            "driver_gender" => $created->gender,
            "driver_year_birthday" => $created->year_birthday,

            'pv_id' => $point->name ?? null,
            'point_id' => $point->id ?? null,

            "company_id" => $company->hash_id,
            "company_name" => $company->name,
            "briefing_name" => $briefing->name ?? '',
        ]);
    }

    protected function createUser(Driver $created)
    {
        $validator = function (int $hashId) {
            if (User::where('hash_id', $hashId)->first()) {
                return false;
            }

            return true;
        };

        $userHashId = $this->generateHashId(
            $validator,
            config('app.hash_generator.user.min'),
            config('app.hash_generator.user.max'),
            config('app.hash_generator.user.tries')
        );

        $driverHashId = $created->hash_id;
        $company = $created->company;

        $user = User::create([
            'hash_id' => $userHashId,
            'email' => $company->hash_id . '-' . $userHashId . '@ta-7.ru',
            'api_token' => Hash::make(date('H:i:s') . sha1($driverHashId)),
            'login' => $driverHashId,
            'password' => Hash::make($driverHashId),
            'name' => $created->fio,
            'role' => 3,
            'company_id' => $company->id
        ]);

        $user->roles()->attach(3);
    }
}
