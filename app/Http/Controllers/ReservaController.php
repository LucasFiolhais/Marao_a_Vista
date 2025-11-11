<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReservaRequest;
use App\Http\Requests\UpdateReservaRequest;

class ReservaController extends Controller
{
    // 🔹 Listar todas as reservas
    public function index()
    {
        return response()->json(Reserva::all());
    }

    // 🔹 Mostrar uma reserva específica
    public function show($id)
    {
        return response()->json(Reserva::findOrFail($id));
    }

    // 🔹 Criar uma nova reserva
   public function store(StoreReservaRequest $request)
{
    $dados = $request->validated();

    // Verificar overlap
    $overlap = Reserva::where(function($query) use ($dados) {
        $query->whereBetween('inicio', [$dados['inicio'], $dados['fim']])
              ->orWhereBetween('fim', [$dados['inicio'], $dados['fim']]);
    })->where('estado', '!=', 'cancelada')->exists();

    if ($overlap) {
        return response()->json(['error' => 'Já existe uma reserva nesse intervalo.'], 409);
    }

    $preco = Reserva::calcularPreco($dados['inicio'], $dados['fim']);
    $dados['preco_total'] = $preco;
    $dados['estado'] = 'pendente';

    $reserva = Reserva::create($dados);
    return response()->json($reserva, 201);
}

    // 🔹 Atualizar reserva (estado, horários, etc.)
    public function update(UpdateReservaRequest $request, $id)
{
    $reserva = Reserva::findOrFail($id);
    $dados = $request->validated();

    if (isset($dados['inicio']) && isset($dados['fim'])) {
        // verificar overlap se alterarem datas
        $overlap = Reserva::where(function($query) use ($dados, $id) {
            $query->whereBetween('inicio', [$dados['inicio'], $dados['fim']])
                  ->orWhereBetween('fim', [$dados['inicio'], $dados['fim']]);
        })->where('id', '!=', $id)
          ->where('estado', '!=', 'cancelada')
          ->exists();

        if ($overlap) {
            return response()->json(['error' => 'Já existe uma reserva nesse intervalo.'], 409);
        }

        $dados['preco_total'] = Reserva::calcularPreco($dados['inicio'], $dados['fim']);
    }

    $reserva->update($dados);
    return response()->json($reserva);
}


    // 🔹 Cancelar reserva
    public function destroy($id)
    {
        $reserva = Reserva::findOrFail($id);
        $reserva->update(['estado' => 'cancelada']);
        return response()->json(['message' => 'Reserva cancelada com sucesso.']);
    }
    
}

