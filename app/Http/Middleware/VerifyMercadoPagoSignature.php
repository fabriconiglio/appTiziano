<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyMercadoPagoSignature
{
    private const INVALID_SIGNATURE_ERROR = 'invalid signature';

    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.mercadopago.webhook_secret');

        // Sin secret configurado no validamos: cómodo en dev local. En prod
        // hay que setear MERCADOPAGO_WEBHOOK_SECRET sí o sí.
        if (empty($secret)) {
            return $next($request);
        }

        if (! $this->isSignatureValid($request, (string) $secret)) {
            return response()->json(['error' => self::INVALID_SIGNATURE_ERROR], 401);
        }

        return $next($request);
    }

    private function isSignatureValid(Request $request, string $secret): bool
    {
        $signatureHeader = $request->header('x-signature');
        $requestId = $request->header('x-request-id');
        $dataId = (string) ($request->input('data.id') ?? $request->query('data.id') ?? '');

        if (! $signatureHeader || ! $requestId || $dataId === '') {
            Log::warning('MP webhook: faltan headers o data.id para validar firma', [
                'has_signature' => (bool) $signatureHeader,
                'has_request_id' => (bool) $requestId,
                'has_data_id' => $dataId !== '',
            ]);
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            $kv = explode('=', $segment, 2);
            if (count($kv) === 2) {
                $parts[trim($kv[0])] = trim($kv[1]);
            }
        }

        $ts = $parts['ts'] ?? null;
        $v1 = $parts['v1'] ?? null;

        if (! $ts || ! $v1) {
            Log::warning('MP webhook: header x-signature mal formado');
            return false;
        }

        // MP normaliza data.id a lowercase cuando es UUID antes de firmar.
        $manifest = 'id:' . strtolower($dataId) . ';request-id:' . $requestId . ';ts:' . $ts . ';';
        $expected = hash_hmac('sha256', $manifest, $secret);

        if (! hash_equals($expected, $v1)) {
            Log::warning('MP webhook: firma inválida', ['data_id' => $dataId]);
            return false;
        }

        return true;
    }
}
