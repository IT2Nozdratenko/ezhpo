<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TripTicketAttachPhotosRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'photos' => 'required|array',
            'photos.*' => 'file|mimes:jpeg,jpg,png,pdf|max:8192',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
