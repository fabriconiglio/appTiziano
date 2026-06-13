<?php

namespace App\Jobs;

use App\Models\RecordatorioLog;
use App\Models\Turno;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envía el recordatorio de un turno: primero WhatsApp; si falla o no está
 * habilitado, cae a email (Brevo). Registra el resultado en recordatorios_log.
 */
class EnviarRecordatorioTurno implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $turnoId;

    public function __construct(int $turnoId)
    {
        $this->turnoId = $turnoId;
    }

    public function handle(WhatsappService $whatsapp): void
    {
        $turno = Turno::with(['client', 'peluquera', 'servicio'])->find($this->turnoId);
        if (! $turno || $turno->estado === 'cancelado') {
            return;
        }

        // 1) Intento por WhatsApp.
        if ($whatsapp->enviarRecordatorio($turno)) {
            $this->registrar($turno, 'whatsapp', 'enviado');
            return;
        }

        // 2) Fallback por email.
        if ($this->enviarEmail($turno)) {
            $this->registrar($turno, 'email', 'enviado');
            return;
        }

        $this->registrar($turno, 'whatsapp', 'fallido');
    }

    private function enviarEmail(Turno $turno): bool
    {
        $email = $turno->client?->email;
        if (! $email) {
            return false;
        }

        $cliente = $turno->client?->name ?? 'cliente';
        $fecha = $turno->inicia_en->locale('es')->isoFormat('dddd D/MM');
        $hora = $turno->inicia_en->format('H:i');
        $peluquera = $turno->peluquera?->nombre ?? '';

        $cuerpo = "Hola {$cliente},\n\nTe recordamos tu turno en Tiziano el {$fecha} a las {$hora} hs"
            . ($peluquera ? " con {$peluquera}" : '') . ".\n\n¡Te esperamos!";

        try {
            Mail::raw($cuerpo, function ($message) use ($email) {
                $message->to($email)->subject('Recordatorio de tu turno - Tiziano');
            });
            return true;
        } catch (\Throwable $e) {
            Log::error('Recordatorio: error al enviar email', [
                'turno_id' => $turno->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function registrar(Turno $turno, string $canal, string $estado): void
    {
        RecordatorioLog::create([
            'turno_id' => $turno->id,
            'canal' => $canal,
            'estado_envio' => $estado,
            'enviado_en' => $estado === 'enviado' ? now() : null,
        ]);
    }
}
