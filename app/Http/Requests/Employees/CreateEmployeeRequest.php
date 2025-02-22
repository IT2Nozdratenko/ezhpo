<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;

final class CreateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:191'
            ],
            'login' => [
                'required',
                'string',
                'min:1',
                'max:191',
            ],
            'email' => [
                'required',
                'string',
                'min:1',
                'max:191',
            ],
            'eds' => [
                'nullable',
                'string',
                'min:1',
                'max:191',
            ],
            'timezone' => [
                'nullable',
                'string',
                'min:1',
                'max:191',
            ],
            'password' => [
                'required',
                'string',
                'min:1',
                'max:255'
            ],
            'pv_id' => [
                'required',
                'integer',
            ],
            'pvs' => [
                'array',
            ],
            'pvs.*' => [
                'integer'
            ],
            'blocked' => [
                'required',
                'integer',
            ],
            'validity_eds_start' => [
                'nullable',
                'date',
            ],
            'validity_eds_end' => [
                'nullable',
                'date',
            ],
            'roles' => [
                'array',
            ],
            'roles.*' => [
                'integer',
            ],
            'permissions' => [
                'array',
            ],
            'permissions.*' => [
                'integer'
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'ФИО',
            'password' => 'пароль',
            'email' => 'e-mail',
            'login' => 'login',
            'pv_id' => 'Пункт выпуска'
        ];
    }
}