<?php

<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Http\Requests\StoreComentarioRequest;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    // 🔹 Listar todos os comentários
    public function index()
    {
        return response()->json(Comentario::all());
    }

    // 🔹 Mostrar um comentário específico
    public function show($id)
    {
        return response()->json(Comentario::findOrFail($id));
    }

    // 🔹 Criar um novo comentário (com validação)
    public function store(StoreComentarioRequest $request)
    {
        $dados = $request->validated();

        $comentario = Comentario::create($dados);

        return response()->json($comentario, 201);
    }

    // 🔹 Atualizar um comentário (opcional)
    public function update(Request $request, $id)
    {
        $comentario = Comentario::findOrFail($id);

        $request->validate([
            'conteudo' => 'sometimes|string|max:1000'
        ]);

        $comentario->update($request->all());

        return response()->json($comentario);
    }

    // 🔹 Eliminar um comentário
    public function destroy($id)
    {
        $comentario = Comentario::findOrFail($id);
        $comentario->delete();

        return response()->json(['message' => 'Comentário eliminado com sucesso.']);
    }
}

