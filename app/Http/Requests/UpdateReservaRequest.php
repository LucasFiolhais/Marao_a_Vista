<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inicio' => 'sometimes|date',
            'fim' => 'sometimes|date|after:inicio',
            'estado' => 'sometimes|in:pendente,confirmada,cancelada',
        ];
    }

    public function messages(): array
    {
        return [
            'inicio.date' => 'O campo início deve ser uma data válida.',
            'fim.date' => 'O campo fim deve ser uma data válida.',
            'fim.after' => 'A data de fim deve ser posterior à data de início.',
            'estado.in' => 'O estado deve ser pendente, confirmada ou cancelada.',
        ];
    }
}

