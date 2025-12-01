<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alojamento;
use App\Models\Foto; // 👈 IMPORTANTE
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // 👈 IMPORTANTE

class AlojamentoController extends Controller
{
 public function index()
{
    $alojamentos = Alojamento::with('fotos')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    $alojamentos->getCollection()->transform(function ($a) {
        $fotoPrincipal = $a->fotos->first();

        return [
            'id'             => $a->id,
            'titulo'         => $a->titulo,
            'descricao'      => $a->descricao,
            'preco_noite'    => $a->preco_noite,
            'created_at'     => $a->created_at,
            'foto_principal' => $fotoPrincipal
                ? asset('storage/' . $fotoPrincipal->path)
                : null,
            'fotos'          => $a->fotos->map(function ($foto) {
                return [
                    'id'  => $foto->id,
                    'url' => asset('storage/' . $foto->path),
                ];
            })->values(),
        ];
    });

    return response()->json($alojamentos);
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo'      => 'required|string|max:255',
            'descricao'   => 'required|string',
            'preco_noite' => 'required|numeric|min:0',

            // fotos opcionais no create
            'fotos.*'     => 'nullable|image|max:4096',
        ]);

        // 1) criar alojamento
        $alojamento = Alojamento::create([
            'titulo'      => $data['titulo'],
            'descricao'   => $data['descricao'],
            'preco_noite' => $data['preco_noite'],
        ]);

        // 2) guardar fotos (se existirem)
        $uploadedFotos = [];

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $file) {
                $path = $file->store('alojamentos', 'public');

                $foto = Foto::create([
                    'alojamento_id' => $alojamento->id,
                    'path'          => $path,
                    'descricao'     => null, // opcional
                    'ordem'         => 0,    // default da BD
                ]);

                $uploadedFotos[] = [
                    'id'  => $foto->id,
                    'url' => asset('storage/' . $foto->path),
                ];
            }
        }

        return response()->json([
            'id'          => $alojamento->id,
            'titulo'      => $alojamento->titulo,
            'descricao'   => $alojamento->descricao,
            'preco_noite' => $alojamento->preco_noite,
            'created_at'  => $alojamento->created_at,
            'fotos'       => $uploadedFotos,
        ], 201);
    }

    public function show(Alojamento $alojamento)
    {
        // garantir que carrega fotos
        $alojamento->load('fotos');

        return response()->json([
            'id'          => $alojamento->id,
            'titulo'      => $alojamento->titulo,
            'descricao'   => $alojamento->descricao,
            'preco_noite' => $alojamento->preco_noite,
            'created_at'  => $alojamento->created_at,
            'fotos'       => $alojamento->fotos->map(function ($foto) {
                return [
                    'id'  => $foto->id,
                    'url' => asset('storage/' . $foto->path),
                ];
            }),
        ]);
    }

    public function update(Request $request, Alojamento $alojamento)
    {
        $data = $request->validate([
            'titulo'      => 'sometimes|required|string|max:255',
            'descricao'   => 'sometimes|required|string',
            'preco_noite' => 'sometimes|required|numeric|min:0',
        ]);

        $alojamento->update($data);

        return response()->json([
            'id'          => $alojamento->id,
            'titulo'      => $alojamento->titulo,
            'descricao'   => $alojamento->descricao,
            'preco_noite' => $alojamento->preco_noite,
            'created_at'  => $alojamento->created_at,
        ]);
    }

    public function destroy(Alojamento $alojamento)
    {
        $alojamento->delete();

        return response()->json(['message' => 'Alojamento eliminado.']);
    }

    public function uploadFotos(Request $request, Alojamento $alojamento)
    {
        $request->validate([
            'fotos.*' => 'required|image|max:4096',
        ]);

        $uploaded = [];

        foreach ($request->file('fotos', []) as $file) {
            $path = $file->store('alojamentos', 'public');

            $foto = Foto::create([
                'alojamento_id' => $alojamento->id,
                'path'          => $path,
                'descricao'     => null,
                'ordem'         => 0,
            ]);

            $uploaded[] = [
                'id'  => $foto->id,
                'url' => asset('storage/' . $foto->path),
            ];
        }

        return response()->json([
            'message' => 'Fotos carregadas com sucesso.',
            'fotos'   => $uploaded,
        ]);
    }

    public function deleteFoto(Foto $foto)
    {
        if ($foto->path && Storage::disk('public')->exists($foto->path)) {
            Storage::disk('public')->delete($foto->path);
        }

        $foto->delete();

        return response()->json(['message' => 'Foto eliminada.']);
    }
}
