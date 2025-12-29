<?php

namespace App\Http\Controllers\Api;




use App\Models\Alojamento;

use Illuminate\Http\Request;

use Inertia\Inertia;

use App\Http\Controllers\Controller;



class AlojamentoController extends Controller

{

    // Método para exibir a lista de todos os alojamentos

    public function index()

    {
        // Buscar todos os alojamentos
        //$alojamentos = Alojamento::all(); // Você pode usar .get() ou outros métodos de consulta
        $alojamentos = Alojamento::with('fotos')->get();

        return Inertia::render('Alojamentos', [
            'alojamentos' => $alojamentos,
        ]);
    }



    // Método para exibir os detalhes de um alojamento

    public function show($id)

    {
        // Buscar o alojamento pelo ID

        //$alojamento = Alojamento::findOrFail($id);

        $alojamento = Alojamento::with('fotos')->findOrFail($id);

        return Inertia::render('AlojamentoDetalhes', [
            'alojamento' => $alojamento,
        ]);
    }

}