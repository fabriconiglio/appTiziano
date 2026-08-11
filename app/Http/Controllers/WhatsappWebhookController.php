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
    /** Teléfono de la peluquería que se le pasa al cliente en cada respuesta. */
    private const TELEFONO_CONTACTO = '+54 9 3516 19-7836';

    public function __invoke(Request $request)
    {
        $from = (string) $request->input('From', '');   // whatsapp:+549...
        $body = strtoupper(trim((string) $request->input('Body', '')));

        $respuesta = $this->interpretar($body);

        if ($respuesta === null) {
            return $this->twiml(
                'No entendimos tu respuesta. Respondé SI para confirmar o NO para cancelar. '
                . 'Cualquier consulta comunicate al ' . self::TELEFONO_CONTACTO . '.'
            );
        }

        $turno = $this->turnoDeCliente($from);

        if (! $turno) {
            Log::info('Whatsapp webhook: sin turno próximo para el número', ['from' => $from]);
            return $this->twiml('No encontramos un turno próximo asociado a este número.');
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

        $mensaje .= ' Cualquier consulta comunicate al ' . self::TELEFONO_CONTACTO . '.';

        return $this->twiml($mensaje);
    }

    /**
     * Interpreta la respuesta del cliente.
     *
     * Busca la palabra dentro del mensaje en vez de exigir que sea exacta: la
     * gente contesta "Sí", "dale" o "perdón pero voy a tener que cancelar, NO",
     * no un SI pelado. También normaliza acentos, porque strtoupper() no toca
     * las vocales acentuadas y "Sí" nunca llegaba a coincidir con "SÍ".
     */
    private function interpretar(string $body): ?string
    {
        $texto = mb_strtolower(trim($body), 'UTF-8');
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
        // Fuera signos y emojis, que si no quedan pegados a la palabra.
        $texto = preg_replace('/[^a-z0-9\s]/u', ' ', $texto);
        $palabras = preg_split('/\s+/', trim($texto), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $afirmativas = ['si', 's', 'confirmar', 'confirmo', 'confirmado', 'dale', 'ok', 'okey', 'listo', 'perfecto', 'asisto'];
        $negativas = ['no', 'n', 'cancelar', 'cancelo', 'cancela', 'anular', 'anulo', 'suspender'];

        $diceSi = (bool) array_intersect($palabras, $afirmativas);
        $diceNo = (bool) array_intersect($palabras, $negativas);

        // Si dice las dos cosas ("no sé si puedo") o ninguna, se vuelve a
        // preguntar: cancelar un turno por adivinar mal es peor que repreguntar.
        if ($diceSi === $diceNo) {
            return null;
        }

        return $diceSi ? 'SI' : 'NO';
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

        // Pendientes y confirmados: el recordatorio sale para los dos estados, así
        // que el que ya estaba confirmado tiene que poder cancelar respondiendo NO.
        return Turno::whereIn('client_id', $clientIds)
            ->whereIn('estado', ['pendiente', 'confirmado'])
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
