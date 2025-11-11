<?php

public function rules(): array
{
    return [
        'inicio' => 'sometimes|date',
        'fim' => 'sometimes|date|after:inicio',
        'estado' => 'sometimes|in:pendente,confirmada,cancelada',
    ];
}
