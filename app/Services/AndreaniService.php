<?php

namespace App\Services;

use AlejoASotelo\Andreani;
use Illuminate\Support\Facades\Log;

class AndreaniService
{
    private Andreani $sdk;
    private string $contrato;
    private string $cpOrigen;

    public function __construct()
    {
        $user = config('services.andreani.user');
        $password = config('services.andreani.password');
        $cliente = config('services.andreani.cliente');
        $this->contrato = config('services.andreani.contrato') ?? '';
        $this->cpOrigen = config('services.andreani.cp_origen') ?? '5000';
        $debug = (bool) config('services.andreani.debug');

        if (empty($user) || empty($password) || empty($cliente)) {
            throw new \RuntimeException('Faltan credenciales de Andreani en .env (ANDREANI_USER, ANDREANI_PASSWORD, ANDREANI_CLIENTE)');
        }

        $this->sdk = new Andreani($user, $password, $cliente, $debug);
    }

    /**
     * Cotiza un envío con Andreani.
     *
     * @param string $cpDestino Código postal destino
     * @param array<array{peso_gramos: int, volumen_cm3: int, quantity: int, unit_price: float}> $items
     * @return array{cost: float, carrier: string, estimated_days: string}|null
     */
    public function cotizar(string $cpDestino, array $items): ?array
    {
        $pesoTotal = 0;
        $volumenTotal = 0;
        $valorDeclarado = 0;

        foreach ($items as $item) {
            if (empty($item['peso_gramos']) || empty($item['volumen_cm3'])) {
                return null;
            }
            $pesoTotal += $item['peso_gramos'] * $item['quantity'];
            $volumenTotal += $item['volumen_cm3'] * $item['quantity'];
            $valorDeclarado += ($item['unit_price'] ?? 0) * $item['quantity'];
        }

        $bultos = [
            [
                'volumen' => $volumenTotal,
                'kilos' => round($pesoTotal / 1000, 2),
                'pesoAforado' => round($pesoTotal / 1000, 2),
                'valorDeclarado' => round($valorDeclarado, 2),
            ],
        ];

        try {
            $result = $this->sdk->cotizarEnvio($cpDestino, $this->contrato, $bultos, config('services.andreani.cliente'));

            if (! $result) {
                Log::warning('Andreani cotización: respuesta vacía', [
                    'cp_destino' => $cpDestino,
                    'bultos' => $bultos,
                ]);
                return null;
            }

            $tarifa = $this->extractTarifa($result);
            if ($tarifa === null) {
                Log::warning('Andreani cotización: no se pudo extraer tarifa', [
                    'cp_destino' => $cpDestino,
                    'response' => json_encode($result),
                ]);
                return null;
            }

            return [
                'carrier' => 'Andreani',
                'cost' => $tarifa,
                'estimated_days' => '3 a 5 días hábiles',
            ];
        } catch (\Throwable $e) {
            Log::error('Andreani cotización error', [
                'cp_destino' => $cpDestino,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Intenta extraer el monto de tarifa de la respuesta del SDK de Andreani.
     * La estructura varía según la versión de la API.
     */
    private function extractTarifa(mixed $result): ?float
    {
        if (is_object($result)) {
            if (isset($result->tapiIndicarTransporte[0]->tarifa)) {
                return (float) $result->tapiIndicarTransporte[0]->tarifa;
            }
            if (isset($result->tarifa)) {
                return (float) $result->tarifa;
            }
            if (isset($result->tarifaConIva)) {
                return (float) $result->tarifaConIva->total;
            }
            if (isset($result->tarifaSinIva)) {
                return (float) $result->tarifaSinIva->total;
            }
        }

        if (is_array($result) && isset($result[0])) {
            $first = is_object($result[0]) ? $result[0] : (object) $result[0];
            if (isset($first->tarifa)) {
                return (float) $first->tarifa;
            }
            if (isset($first->tarifaConIva)) {
                return (float) $first->tarifaConIva->total;
            }
        }

        return null;
    }
}
