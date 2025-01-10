<?php

namespace App\Actions\Element;

use App\Company;
use App\User;
use App\ValueObjects\Phone;
use Exception;
use Illuminate\Support\Facades\Hash;

class CreateCompanyHandler extends AbstractCreateElementHandler implements CreateElementHandlerInterface
{
    /**
     * @throws Exception
     */
    public function handle($data)
    {
        $existItem = Company::query()
            ->where('name', trim($data['name']))
            ->first();
        if ($existItem) {
            throw new Exception('Найден дубликат по названию компании');
        }

        $phoneNumber = $data['where_call'] ?? null;
        if ($phoneNumber) {
            $phone = new Phone($phoneNumber);

            if (!$phone->isValid()) {
                throw new Exception('Некорректный формат телефона, введите телефон в формате 7ХХХХХХХХХХ');
            }

            $data['where_call'] = $phone->getSanitized();
        }

        $validator = function (int $hashId) {
            if (Company::where('hash_id', $hashId)->first()) {
                return false;
            }

            if (User::where('login', $this->getUserLogin($hashId))->first()) {
                return false;
            }

            return true;
        };

        $data['hash_id'] = $this->generateHashId(
            $validator,
            config('app.hash_generator.company.min'),
            config('app.hash_generator.company.max'),
            config('app.hash_generator.company.tries')
        );

        $created = $this->createElement($data);

        $this->createUser($created);

        return $created;
    }

    protected function getUserLogin(string $hashId): string
    {
        return '0' . $hashId;
    }

    /**
     * @throws Exception
     */
    protected function createUser(Company $created)
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

        $companyHashId = $created->hash_id;
        $userLogin = $this->getUserLogin($companyHashId);

        $user = User::create([
            'hash_id' => $userHashId,
            'email' => $companyHashId . '-' . $userHashId . '@ta-7.ru',
            'api_token' => Hash::make(date('H:i:s') . sha1($companyHashId)),
            'login' => $userLogin,
            'password' => Hash::make($userLogin),
            'name' => $created->name,
            'role' => 12,
            'company_id' => $created->id
        ]);

        $user->roles()->attach(6);
    }
}
