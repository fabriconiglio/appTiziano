<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        $accessToken = config('services.mercadopago.access_token');

        if (empty($accessToken)) {
            throw new \RuntimeException('Falta configurar MERCADOPAGO_ACCESS_TOKEN en .env');
        }

        MercadoPagoConfig::setAccessToken($accessToken);
    }

    /**
     * Crea una preferencia de pago en Mercado Pago (Checkout Pro).
     * Retorna el init_point al que hay que redirigir al usuario.
     */
    public function createPreference(Order $order): string
    {
        $frontendUrl = rtrim(config('services.frontend.url'), '/');
        $backendUrl = rtrim(config('app.url'), '/');

        $items = $order->items->map(fn ($item) => [
            'id' => (string) $item->product_id,
            'title' => $item->product_name,
            'quantity' => (int) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'currency_id' => 'ARS',
        ])->toArray();

        if ($order->shipping_cost && (float) $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping',
                'title' => 'Envío Andreani',
                'quantity' => 1,
                'unit_price' => (float) $order->shipping_cost,
                'currency_id' => 'ARS',
            ];
        }

        $payload = [
            'items' => $items,
            'external_reference' => (string) $order->id,
            'statement_descriptor' => 'TIZIANO',
            'back_urls' => [
                'success' => "{$frontendUrl}/checkout/confirmacion?order={$order->id}",
                'failure' => "{$frontendUrl}/checkout/confirmacion?order={$order->id}",
                'pending' => "{$frontendUrl}/checkout/confirmacion?order={$order->id}",
            ],
            'auto_return' => 'approved',
            'notification_url' => "{$backendUrl}/api/mercadopago/webhook",
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ];

        if ($order->shipping_name) {
            $parts = explode(' ', $order->shipping_name, 2);
            $payload['payer'] = [
                'name' => $parts[0] ?? '',
                'surname' => $parts[1] ?? '',
                'email' => $order->user->email ?? null,
                'phone' => ['number' => $order->shipping_phone],
                'address' => [
                    'zip_code' => $order->shipping_zip,
                    'street_name' => $order->shipping_address,
                ],
            ];
        }

        try {
            $client = new PreferenceClient();
            $preference = $client->create($payload);
        } catch (MPApiException $e) {
            Log::error('MP create preference error', [
                'order_id' => $order->id,
                'status' => $e->getApiResponse()->getStatusCode(),
                'body' => $e->getApiResponse()->getContent(),
            ]);
            throw new \RuntimeException('Error al crear preferencia en Mercado Pago: ' . $e->getMessage());
        }

        $order->update(['mercadopago_preference_id' => $preference->id]);

        return $preference->init_point;
    }

    /**
     * Procesa una notificación webhook de Mercado Pago (topic=payment).
     * Consulta el pago por ID y actualiza la orden asociada.
     */
    public function handlePaymentNotification(string $paymentId): void
    {
        try {
            $payment = (new PaymentClient())->get($paymentId);
        } catch (MPApiException $e) {
            Log::error('MP webhook: no se pudo obtener el pago', [
                'payment_id' => $paymentId,
                'status' => $e->getApiResponse()->getStatusCode(),
                'body' => $e->getApiResponse()->getContent(),
            ]);
            return;
        }

        $orderId = $payment->external_reference ?? null;
        if (! $orderId) {
            Log::warning('MP webhook: pago sin external_reference', ['payment_id' => $paymentId]);
            return;
        }

        $order = Order::find($orderId);
        if (! $order) {
            Log::warning('MP webhook: orden no encontrada', ['order_id' => $orderId, 'payment_id' => $paymentId]);
            return;
        }

        $order->update([
            'mercadopago_payment_id' => (string) $payment->id,
            'payment_status' => $this->mapPaymentStatus($payment->status),
        ]);
    }

    private function mapPaymentStatus(?string $mpStatus): string
    {
        return match ($mpStatus) {
            'approved' => 'paid',
            'rejected', 'cancelled' => 'failed',
            default => 'pending',
        };
    }
}
