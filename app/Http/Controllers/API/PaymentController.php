<?php

namespace App\Http\Controllers\Api;


use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Pagamento;
use App\Services\EasypayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $easypay;

    public function __construct(EasypayService $easypay)
    {
        $this->easypay = $easypay;
    }

    /**
     * Criar pagamento real com ligação BD
     */
    public function checkout($reservaId)
    {
        
        try {
            Log::info('CHECKOUT START', [
                'reservaId' => $reservaId,
                'user_id' => auth()->id(),
            ]);

            $reserva = Reserva::with('user')->findOrFail($reservaId);

            if ($reserva->estado === 'confirmada') {
                return response()->json(['message' => 'Reserva já paga'], 400);
            }

            $pagamento = new Pagamento();
            $pagamento->reserva_id = $reserva->id;
            $pagamento->valor = $reserva->total;
            $pagamento->estado = 'pendente';
            $pagamento->save();

            \Log::info('ANTES DO EASYPAY');
            $result = $this->easypay->criarPagamento($reserva);
            \Log::info('DEPOIS DO EASYPAY', $result ?? []);

            if (!$result['success']) {
                Log::error('EASYPAY ERROR', $result);
                return response()->json([
                    'message' => 'Erro Easypay',
                    'easypay_error' => $result['error'] ?? null,
                ], 500);
            }

            return response()->json([
                'payment_url' => $result['data']['url'] ?? null,
            ]);

        } 
        catch (\Throwable $e) {
            Log::error('CHECKOUT ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            //return response()->json(['message' => 'Erro interno'], 500);
            return response()->json([
                'message' => 'Exception Easypay',
                'error' => $e->getMessage(),
            ], 500);
        } 

        // Ajusta conforme resposta real do Easypay
        return response()->json([
            'payment_url' => $result['data']['url'] ?? null,
        ]);
    }

    /**
     * Verificar estado do pagamento
     */
    public function status($paymentKey)
    {
        $pagamento = Pagamento::where('payment_key', $paymentKey)->firstOrFail();

        $result = $this->easypay->checkPayment($paymentKey);

        return response()->json($result);
    }

    /**
     * Webhook — Easypay confirma pagamento
     */
    public function webhook(Request $request)
    {
        $key = $request->input('key');
        $status = $request->input('status');

        $pagamento = Pagamento::where('payment_key', $key)->first();

        if (!$pagamento) {
            return response()->json(['error' => 'Pagamento não encontrado'], 404);
        }

        if ($status === 'paid') {
            $pagamento->update(['estado' => 'pago']);
            $pagamento->reserva->update(['estado' => 'confirmada']);
        } else {
            $pagamento->update(['estado' => 'falhou']);
        }

        return response()->json(['success' => true]);
    }

    public function index()
    {
        return response()->json(
            Pagamento::with('reserva.user', 'reserva.alojamento')->get()
        );
    }
    public function mbway(Request $request, $reservaId)
    {
        $reserva = Reserva::findOrFail($reservaId);

        $request->validate([
            'phone' => 'required|digits:9'
        ]);

        $result = $this->easypay->solicitarMBWay($reserva, $request->phone);

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json(['message' => 'Pedido MBWay enviado']);
    }
}
