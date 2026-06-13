<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Agenda {{ $fecha->format('d/m/Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .sub { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f2f2f2; }
        .estado { text-transform: capitalize; }
        .vacio { color: #888; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Agenda — Tiziano</h1>
    <div class="sub">{{ $fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}</div>

    @if($turnos->isEmpty())
        <p class="vacio">No hay turnos para este día.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Peluquera</th>
                    <th>Estado</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($turnos as $turno)
                    <tr>
                        <td>{{ $turno->inicia_en->format('H:i') }} - {{ $turno->termina_en->format('H:i') }}</td>
                        <td>{{ $turno->client->full_name ?? '—' }}</td>
                        <td>{{ $turno->servicio->nombre ?? '—' }}</td>
                        <td>{{ $turno->peluquera->nombre ?? '—' }}</td>
                        <td class="estado">{{ $turno->estado }}</td>
                        <td>{{ $turno->notas }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
