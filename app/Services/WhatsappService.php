<?php

namespace App\Services;

use App\Models\PlantillaWhatsapp;
use App\Models\Turno;
use Illuminate\Support\Facades\Log;

/**
 * Envío de recordatorios por WhatsApp vía Twilio.
 *
 * Queda detrás del flag services.twilio.whatsapp_enabled y de class_exists() del
 * SDK, así que es no-op (devuelve false) mientras no se instale `twilio/sdk` o no
 * se apruebe la cuenta en Meta. El cliente responde SI/NO y eso lo procesa el
 * webhook (WhatsappWebhookController).
 */
class WhatsappService
{
    public function habilitado(): bool
    {
        return (bool) config('services.twilio.whatsapp_enabled')
            && config('services.twilio.sid')
            && config('services.twilio.token')
            && config('services.twilio.whatsapp_from')
            && class_exists(\Twilio\Rest\Client::class);
    }

    /**
     * Envía el recordatorio del turno. Devuelve true si Twilio aceptó el mensaje.
     */
    public function enviarRecordatorio(Turno $turno): bool
    {
        if (! $this->habilitado()) {
            return false;
        }

        $turno->loadMissing(['client', 'peluquera', 'servicios']);

        $destino = $this->formatearDestino($turno->client?->phone);
        if (! $destino) {
            Log::warning('Whatsapp: turno sin teléfono válido', ['turno_id' => $turno->id]);
            return false;
        }

        $plantilla = PlantillaWhatsapp::where('activa', true)->first();
        $variables = $this->variables($turno);

        try {
            $client = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $opciones = ['from' => config('services.twilio.whatsapp_from')];

            if ($plantilla && $plantilla->sid) {
                // Plantilla aprobada por Meta (Content SID de Twilio).
                $opciones['contentSid'] = $plantilla->sid;
                $opciones['contentVariables'] = json_encode($variables);
            } else {
                // Cuerpo libre (solo funciona dentro de la ventana de 24 hs / sandbox).
                $opciones['body'] = $this->reemplazar(
                    $plantilla->cuerpo ?? $this->cuerpoPorDefecto(),
                    $variables
                );
            }

            $client->messages->create($destino, $opciones);

            return true;
        } catch (\Throwable $e) {
            Log::error('Whatsapp: error al enviar recordatorio', [
                'turno_id' => $turno->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Variables de la plantilla aprobada por Meta (recordatorio_turno_tiziano):
     * {{1}} nombre, {{2}} fecha, {{3}} hora. WhatsApp rechaza el envío si alguna
     * variable va vacía, así que ninguna puede quedar sin valor.
     */
    private function variables(Turno $turno): array
    {
        $nombre = $turno->client?->name ?: $turno->cliente_nombre;

        return [
            '1' => $nombre ?: 'cliente',
            '2' => $turno->inicia_en->locale('es')->isoFormat('D/MM'),
            '3' => $turno->inicia_en->format('H:i'),
        ];
    }

    private function reemplazar(string $cuerpo, array $variables): string
    {
        foreach ($variables as $clave => $valor) {
            $cuerpo = str_replace('{{' . $clave . '}}', $valor, $cuerpo);
        }
        return $cuerpo;
    }

    private function cuerpoPorDefecto(): string
    {
        return 'Hola {{1}}, te recordamos tu turno en Tiziano Peluquería & Spa el {{2}} a las {{3}} hs. '
            . 'Respondé SI para confirmar o NO para cancelar.';
    }

    /**
     * Normaliza el teléfono al formato whatsapp:+E164. Best-effort para AR.
     */
    private function formatearDestino(?string $telefono): ?string
    {
        if (! $telefono) {
            return null;
        }

        $limpio = preg_replace('/[^0-9+]/', '', $telefono);
        if ($limpio === '' || $limpio === null) {
            return null;
        }

        if (str_starts_with($limpio, '+')) {
            return 'whatsapp:' . $limpio;
        }

        // Sin prefijo internacional: se asume celular de Argentina (+54 9).
        $soloDigitos = ltrim($limpio, '0');
        return 'whatsapp:+549' . $soloDigitos;
    }
}
