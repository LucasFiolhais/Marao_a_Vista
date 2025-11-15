<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EasypayService
{
    protected $baseURL;
    protected $apiKey;
    protected $accountId;

    public function __construct()
    {
        $this->baseURL   = config('services.easypay.base_url');
        $this->apiKey    = config('services.easypay.api_key');
        $this->accountId = config('services.easypay.account_id'); 
    }

    /**
     * Criar pagamento Easypay (sandbox)
     */
    public function criarPagamento($reserva)
    {
        $endpoint = "{$this->baseURL}/single";

        $response = Http::withHeaders([
            'AccountId' => $this->accountId,
            'ApiKey'    => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            "value" => $reserva->preco_total,
            "currency" => "EUR",
            "method" => "mbw", // MBWAY
            "key" => "reserva-{$reserva->id}",
            "customer" => [
                "name"  => $reserva->user->name,
                "email" => $reserva->user->email,
                "key"   => "user-{$reserva->user->id}"
            ],
            "sandbox" => true,
            "redirect_url" => "http://localhost:8080/pagamento/sucesso",
            "failure_url"  => "http://localhost:8080/pagamento/falha"
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error'   => $response->json(),
            ];
        }

        return [
            'success' => true,
            'data'    => $response->json(),
        ];
    }

    /**
     * Verificar estado pagamento
     */
    public function verificarPagamento($paymentKey)
    {
        $endpoint = "{$this->baseURL}/payment/{$paymentKey}";

        $response = Http::withHeaders([
            'AccountId' => $this->accountId,
            'ApiKey'    => $this->apiKey,
        ])->get($endpoint);

        if ($response->failed()) {
            return ['success' => false, 'error' => $response->json()];
        }

        return ['success' => true, 'data' => $response->json()];
    }
}
