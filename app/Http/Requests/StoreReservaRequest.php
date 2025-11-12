<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // permite que qualquer user use esta request
    }

    public function rules(): array
    {
        return [
            'inicio' => 'required|date',
            'fim' => 'required|date|after:inicio',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}

