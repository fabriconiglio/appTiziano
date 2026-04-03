<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TacaTacaService
{
    private string $clientId;
    private string $clientSecret;
    private string $authUrl;
    private string $checkoutUrl;

    public function __construct()
    {
        $this->clientId = config('services.tacataca.client_id');
        $this->clientSecret = config('services.tacataca.client_secret');
        $this->authUrl = config('services.tacataca.auth_url');
        $this->checkoutUrl = config('services.tacataca.checkout_url');
    }

    private function getAccessToken(): string
    {
        return Cache::remember('tacataca_access_token', 3500, function () {
            $response = Http::post("{$this->authUrl}/oauth/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => '*',
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('No se pudo obtener el token de Taca Taca: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    public function createPaymentIntent(Order $order): string
    {
        $token = $this->getAccessToken();

        $items = $order->items->map(fn ($item) => [
            'id' => $item->product_id,
            'name' => $item->product_name,
            'unitPrice' => [
                'currency' => '032',
                'amount' => (int) ($item->unit_price * 100),
            ],
            'quantity' => $item->quantity,
        ])->toArray();

        $response = Http::withHeaders([
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            'Authorization' => "Bearer {$token}",
        ])->post("{$this->checkoutUrl}/api/v2/orders", [
            'data' => [
                'attributes' => [
                    'currency' => '032',
                    'items' => $items,
                ],
            ],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Error al crear intención de pago en Taca Taca: ' . $response->body());
        }

        $checkoutLink = $response->json('data.links.checkout');

        $order->update([
            'taca_taca_order_id' => $response->json('data.id'),
        ]);

        return $checkoutLink;
    }
}
