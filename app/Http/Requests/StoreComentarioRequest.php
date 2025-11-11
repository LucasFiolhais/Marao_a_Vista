<?php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Podes pôr lógica de autorização aqui (ex: apenas utilizadores autenticados)
        return true;
    }

    public function rules(): array
    {
        return [
            'reserva_id' => 'required|exists:reservas,id', // o comentário pertence a uma reserva existente
            'user_id' => 'required|exists:users,id',       // o autor deve ser um user válido
            'conteudo' => 'required|string|max:1000',      // texto do comentário
        ];
    }

    public function messages(): array
    {
        return [
            'reserva_id.required' => 'A reserva é obrigatória.',
            'reserva_id.exists' => 'A reserva indicada não existe.',
            'user_id.required' => 'O utilizador é obrigatório.',
            'user_id.exists' => 'O utilizador indicado não existe.',
            'conteudo.required' => 'O comentário não pode estar vazio.',
            'conteudo.max' => 'O comentário não pode ultrapassar 1000 caracteres.',
        ];
    }
}
