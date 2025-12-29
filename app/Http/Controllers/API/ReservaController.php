<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Alojamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ReservaController extends Controller
{
    public function index()
    {
        return Reserva::with(['user', 'alojamento'])->get();
    }

    public function show($id)
    {
        return Reserva::with(['user', 'alojamento'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'alojamento_id' => 'required|exists:alojamentos,id',
            'checkin' => 'required|date|after_or_equal:today',
            'checkout' => 'required|date|after:checkin',
            'hospedes' => 'required|integer|min:1',
            'observacoes' => 'nullable|string',
        ]);

        // Valida capacidade
        $alojamento = Alojamento::findOrFail($data['alojamento_id']);
        if (isset($alojamento->capacidade) && $data['hospedes'] > $alojamento->capacidade) {
            return response()->json(['error' => 'O número de hóspedes excede a capacidade.'], 422);
        }

        // Conflito de datas
        if ($this->hasDateConflict($data['alojamento_id'], $data['checkin'], $data['checkout'])) {
            return response()->json(['error' => 'Já existe uma reserva nesse período.'], 409);
        }

        // Dados extra
        $data['user_id'] = auth()->id();
        $data['estado'] = 'pendente';
        $data['referencia'] = $this->gerarReferencia();
        $data['total'] = $this->calcularPreco($data['checkin'], $data['checkout'], $alojamento->id);
        //$data['total'] = 10.00;

        $reserva = Reserva::create($data);
        \Log::info('AUTH CHECK', [
            'check' => auth()->check(),
            'id' => auth()->id(),
        ]);

        return response()->json([
            'reserva' => $reserva->load(['alojamento']),
            'redirect' => url("/checkout/{$reserva->id}"),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        if (auth()->id() !== $reserva->user_id && !auth()->user()->is_admin) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $data = $request->validate([
            'checkin' => 'sometimes|date|after_or_equal:today',
            'checkout' => 'sometimes|date|after:checkin',
            'hospedes' => 'sometimes|integer|min:1',
            'estado' => ['sometimes', Rule::in(['pendente', 'confirmado', 'cancelado'])],
            'observacoes' => 'nullable|string',
        ]);

        // Verificação de datas alteradas
        $inicio = $data['checkin'] ?? $reserva->checkin;
        $fim = $data['checkout'] ?? $reserva->checkout;

        if ($this->hasDateConflict($reserva->alojamento_id, $inicio, $fim, $id)) {
            return response()->json(['error' => 'Conflito de datas'], 409);
        }

        if (isset($data['checkin']) || isset($data['checkout'])) {
            $data['total'] = $this->calcularPreco($inicio, $fim, $reserva->alojamento);
        }

        $reserva->update($data);

        return $reserva->load(['alojamento']);
    }

    public function cancel(Reserva $reserva)
    {
        if (auth()->id() !== $reserva->user_id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        if (now()->greaterThanOrEqualTo($reserva->checkin)) {
            return response()->json(['message' => 'Já não é possível cancelar esta reserva.'], 400);
        }

        $reserva->update(['estado' => 'cancelado']);

        return response()->json(['message' => 'Reserva cancelada.']);
    }

    public function destroy($id)
    {
        $reserva = Reserva::findOrFail($id);
        $reserva->update(['estado' => 'cancelado']);
        return response()->json(['message' => 'Reserva cancelada.']);
    }


    public function indexAdmin()
    {
        return Reserva::with(['user', 'alojamento'])
            ->orderBy('checkin', 'desc')
            ->get();
    }

    public function updateStatus(Request $request, Reserva $reserva)
    {
        $request->validate([
            'estado' => ['required', Rule::in(['pendente', 'confirmado', 'cancelado', 'concluido', 'expirado'])],
        ]);

        $reserva->update(['estado' => $request->estado]);

        return response()->json([
            'message' => "Estado atualizado para {$request->estado}",
            'reserva' => $reserva->load(['user', 'alojamento']),
        ]);
    }

    public function available(Request $request, $alojamentoId)
    {
    $data = $request->validate([
        'checkin' => 'required|date|after_or_equal:today',
        'checkout' => 'required|date|after:checkin',
    ]);

    $available = !$this->hasDateConflict(
        $alojamentoId,
        $data['checkin'],
        $data['checkout']
    );

    return response()->json([
        'available' => $available,
        'message' => $available
            ? 'O alojamento está disponível!'
            : 'O alojamento não está disponível nesse período.'
    ]);
    }


    private function hasDateConflict($alojamentoId, $inicio, $fim, $excludeId = null)
    {
        return Reserva::where('alojamento_id', $alojamentoId)
            ->where('estado', '!=', 'cancelado')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('checkin', [$inicio, $fim])
                    ->orWhereBetween('checkout', [$inicio, $fim])
                    ->orWhere(function ($q2) use ($inicio, $fim) {
                        $q2->where('checkin', '<=', $inicio)
                            ->where('checkout', '>=', $fim);
                    });
            })
            ->exists();
    }

    private function calcularPreco($inicio, $fim, $alojamento)
    {
        $dias = (new \DateTime($inicio))->diff(new \DateTime($fim))->days;
        return $dias * ($alojamento->preco_noite ?? 40);
    }

    private function gerarReferencia()
    {
        do {
            $ref = 'RES-' . strtoupper(Str::random(8));
        } while (Reserva::where('referencia', $ref)->exists());

        return $ref;
    }

    public function minhasReservas(Request $request)
    {
        return Reserva::where('user_id', $request->user()->id)
            ->with(['alojamento.fotos'])
            ->orderBy('checkin', 'desc')
            ->get();
    }
}
