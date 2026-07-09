<?php

namespace App\Http\Controllers;

use App\Jobs\SincronizarTurnoGoogleCalendar;
use App\Models\Client;
use App\Models\RecordatorioLog;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Webhook de Twilio para respuestas entrantes de WhatsApp (SI / NO).
 * Público (sin auth:sanctum), protegido por el middleware twilio.signature.
 */
class WhatsappWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $from = (string) $request->input('From', '');   // whatsapp:+549...
        $body = strtoupper(trim((string) $request->input('Body', '')));

        $respuesta = $this->interpretar($body);

        if ($respuesta === null) {
            return $this->twiml('No entendimos tu respuesta. Respondé SI para confirmar o NO para cancelar.');
        }

        $turno = $this->turnoDeCliente($from);

        if (! $turno) {
            Log::info('Whatsapp webhook: sin turno pendiente para el número', ['from' => $from]);
            return $this->twiml('No encontramos un turno pendiente asociado a este número.');
        }

        $nuevoEstado = $respuesta === 'SI' ? 'confirmado' : 'cancelado';
        $turno->update(['estado' => $nuevoEstado]);

        // Reflejar en Google Calendar.
        SincronizarTurnoGoogleCalendar::dispatch(
            $turno->id,
            $nuevoEstado === 'cancelado' ? 'eliminar' : 'actualizar',
            $turno->google_event_id
        );

        // Registrar la respuesta en el log del recordatorio.
        RecordatorioLog::where('turno_id', $turno->id)
            ->latest()
            ->first()
            ?->update(['respuesta' => $respuesta, 'respondido_en' => now()]);

        $mensaje = $nuevoEstado === 'confirmado'
            ? '¡Gracias! Tu turno quedó confirmado. Te esperamos.'
            : 'Tu turno fue cancelado. ¡Esperamos verte pronto!';

        return $this->twiml($mensaje);
    }

    private function interpretar(string $body): ?string
    {
        if (in_array($body, ['SI', 'SÍ', 'S', 'CONFIRMAR', 'CONFIRMO'], true)) {
            return 'SI';
        }
        if (in_array($body, ['NO', 'N', 'CANCELAR', 'CANCELO'], true)) {
            return 'NO';
        }
        return null;
    }

    /**
     * Busca el próximo turno pendiente del cliente cuyo teléfono coincide con el From.
     */
    private function turnoDeCliente(string $from): ?Turno
    {
        $digitos = preg_replace('/\D/', '', $from);
        if (! $digitos) {
            return null;
        }

        $sufijo = substr($digitos, -8); // últimos 8 dígitos, tolerante al prefijo país/área

        // Puede haber más de un cliente con el mismo teléfono (fichas duplicadas):
        // se busca el próximo turno pendiente entre TODOS los que matchean.
        $clientIds = Client::whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$sufijo}"])
            ->pluck('id');

        if ($clientIds->isEmpty()) {
            return null;
        }

        return Turno::whereIn('client_id', $clientIds)
            ->where('estado', 'pendiente')
            ->where('inicia_en', '>=', Carbon::now()->startOfDay())
            ->orderBy('inicia_en')
            ->first();
    }

    private function twiml(string $mensaje)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response><Message>'
            . htmlspecialchars($mensaje, ENT_XML1)
            . '</Message></Response>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
