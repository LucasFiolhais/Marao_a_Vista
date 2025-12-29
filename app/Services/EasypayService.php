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
     * Criar pagamento (Multibanco + MBWay opcional)
     */
    public function criarPagamento($reserva)
    {
    $endpoint = "{$this->baseURL}/single";

    try {
        $response = Http::withHeaders([
            'AccountId' => $this->accountId,
            'ApiKey'    => $this->apiKey,
        ])->post($endpoint, [
            "key" => "reserva-{$reserva->id}",
            "type" => "sale",
            "value" => (float) $reserva->total,
            "currency" => "EUR",
            "method" => "mbw",
            "customer" => [
                "name"  => $reserva->user->name,
                "email" => $reserva->user->email,
            ],

            // SEM methods, SEM entity, SEM mbw
            "sandbox" => true,
        ]);

        if ($response->failed()) {
            \Log::error('EASYPAY RAW ERROR', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()
            ];
        }

        return [
            'success' => true,
            'data' => $response->json()
        ];

    } catch (\Throwable $e) {
        \Log::error('EASYPAY EXCEPTION', [
            'message' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

    /**
     * Pedir MBWay após o utilizador inserir o número
     */
    public function solicitarMBWay($reserva, $phone)
    {
        try {
            $response = Http::withHeaders([
                'AccountId' => $this->accountId,
                'ApiKey'    => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseURL}/single/mbw", [
                "key" => "reserva-{$reserva->id}",
                "phone" => $phone
            ]);

            if ($response->failed()) {
                return ['success' => false, 'error' => $response->json()];
            }

            return ['success' => true, 'data' => $response->json()];

        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    /**
     * Verificar estado de pagamento
     */
    public function verificarPagamento($paymentKey)
    {
        try {
            $response = Http::withHeaders([
                'AccountId' => $this->accountId,
                'ApiKey'    => $this->apiKey,
            ])->get("{$this->baseURL}/single/{$paymentKey}");

            if ($response->failed()) {
                return ['success' => false, 'error' => $response->json()];
            }

            return ['success' => true, 'data' => $response->json()];

        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
