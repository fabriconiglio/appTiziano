<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida el header X-Twilio-Signature del webhook de WhatsApp.
 * Replica el algoritmo de Twilio: HMAC-SHA1(authToken, url + params ordenados),
 * en base64. Si no hay auth token configurado, deja pasar (modo dev).
 */
class VerifyTwilioSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.twilio.token');

        if (empty($token)) {
            return $next($request);
        }

        if (! $this->firmaValida($request, (string) $token)) {
            return response()->json(['error' => 'invalid signature'], 401);
        }

        return $next($request);
    }

    private function firmaValida(Request $request, string $token): bool
    {
        $firma = $request->header('X-Twilio-Signature');
        if (! $firma) {
            Log::warning('Twilio webhook: falta X-Twilio-Signature');
            return false;
        }

        $url = $request->fullUrl();
        $params = $request->post();
        ksort($params);

        $data = $url;
        foreach ($params as $clave => $valor) {
            $data .= $clave . $valor;
        }

        $esperada = base64_encode(hash_hmac('sha1', $data, $token, true));

        if (! hash_equals($esperada, $firma)) {
            Log::warning('Twilio webhook: firma inválida');
            return false;
        }

        return true;
    }
}
