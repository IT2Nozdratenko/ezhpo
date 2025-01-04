<?php

namespace App\Actions\User;

use App\GenerateHashIdTrait;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateUserHandler
{
    use GenerateHashIdTrait;

    public function handle(array $data): User
    {
        $userIsClient = array_search(6, $data['roles'] ?? []);
        if ($userIsClient) {
            $pv = null;
            $company = $data['company'];
        } else {
            $company = null;
            $pv = $data['pv'] ?? null;
        }

        $userId = $data['user_id'] ?? null;

        $rules = [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:191'
            ],
            'password' => [
                'required_without:user_id',
                'nullable',
                'string',
                'min:1',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'min:1',
                'max:191',
            ],
            'login' => [
                'required',
                'string',
                'min:1',
                'max:191',
                empty($userId)
                    ? Rule::unique('users')
                    : Rule::unique('users')->ignore($userId),
            ]
        ];

        $validator = Validator::make(
            $data,
            $rules,
            [
                'password.required_without' => 'Поле пароль обязательно при добавлении сотрудника'
            ],
            [
                'name' => 'ФИО',
                'password' => 'пароль',
                'email' => 'e-mail',
                'login' => 'login',
            ]
        );

        $validator->validate();

        if (empty($userId)) {
            $user = new User();

            $validator = function (int $hashId) {
                if (User::where('hash_id', $hashId)->first()) {
                    return false;
                }

                return true;
            };

            $user->hash_id = $this->generateHashId(
                $validator,
                config('app.hash_generator.user.min'),
                config('app.hash_generator.user.max'),
                config('app.hash_generator.user.tries')
            );
        } else {
            $user = User::withTrashed()->find($userId);
        }

        if ($password = $data['password'] ?? null) {
            $password = Hash::make($password);
            $apiToken = Hash::make(date('H:i:s') . sha1($password));

            $user->password = $password;
            $user->api_token = $apiToken;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->eds = $data['eds'] ?? null;
        $user->timezone = $data['timezone'] ?? null;
        $user->blocked = $data['blocked'] ?? 0;
        $user->validity_eds_start = $data['validity_eds_start'] ?? null;
        $user->validity_eds_end = $data['validity_eds_end'] ?? null;
        $user->login = $data['login'] ?? $data['email'];

        $user->save();

        $user->roles()->sync($data['roles'] ?? []);
        $user->permissions()->sync($data['permissions'] ?? []);
        $user->points()->sync($data['pvs'] ?? []);
        $user->company()->associate($company);
        $user->pv()->associate($pv);
        $user->save();

        /** @var User $user */
        $user = User::withTrashed()
            ->with([
                'roles',
                'permissions',
                'pv'
            ])
            ->find($user->id);

        return $user;
    }
}
