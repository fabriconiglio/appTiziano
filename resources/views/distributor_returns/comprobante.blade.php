<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Devolución {{ $devolucion->return_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 25px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 14px; margin-bottom: 18px; }
        .header h1 { margin: 0 0 4px; font-size: 20px; }
        .header .numero { font-size: 15px; font-weight: bold; }
        .aviso { font-size: 10px; color: #666; margin-top: 4px; }
        .datos { width: 100%; margin-bottom: 16px; }
        .datos td { padding: 3px 0; }
        .datos .etiqueta { color: #666; width: 130px; }
        table.detalle { width: 100%; border-collapse: collapse; margin-bottom: 14px; table-layout: fixed; }
        table.detalle th { background: #f5f5f5; border: 1px solid #ddd; padding: 7px; text-align: left; }
        table.detalle td { border: 1px solid #ddd; padding: 7px; word-wrap: break-word; }
        .der { text-align: right; }
        .centro { text-align: center; }
        .total { font-size: 15px; font-weight: bold; }
        .anulada { color: #c00; border: 2px solid #c00; padding: 8px; text-align: center;
                   font-weight: bold; margin-bottom: 16px; }
        .firma { margin-top: 45px; }
        .firma td { padding-top: 30px; border-top: 1px solid #333; text-align: center; width: 45%; font-size: 11px; }
        .footer { margin-top: 26px; border-top: 1px solid #ddd; padding-top: 8px;
                  font-size: 10px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tiziano Distribuidora</h1>
        <div class="numero">COMPROBANTE DE DEVOLUCIÓN {{ $devolucion->return_number }}</div>
        <div class="aviso">Documento interno — no válido como factura ni nota de crédito fiscal</div>
    </div>

    @if($devolucion->estaAnulada())
        <div class="anulada">ANULADA el {{ $devolucion->anulada_en->format('d/m/Y') }}</div>
    @endif

    <table class="datos">
        <tr>
            <td class="etiqueta">Cliente:</td>
            <td><strong>{{ $devolucion->nombreCliente() }}</strong></td>
            <td class="etiqueta">Fecha:</td>
            <td>{{ $devolucion->return_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Compra de origen:</td>
            <td>{{ $devolucion->technicalRecord?->purchase_date?->format('d/m/Y') ?? '-' }}</td>
            <td class="etiqueta">Destino:</td>
            <td>
                @switch($devolucion->destino)
                    @case('cuenta_corriente') Cuenta corriente @break
                    @case('efectivo') Efectivo @break
                    @default Vale a favor
                @endswitch
            </td>
        </tr>
        @if($devolucion->motivo)
        <tr>
            <td class="etiqueta">Motivo:</td>
            <td colspan="3">{{ $devolucion->motivo }}</td>
        </tr>
        @endif
    </table>

    <table class="detalle">
        <thead>
            <tr>
                <th style="width:48%">Producto</th>
                <th style="width:14%" class="centro">Cantidad</th>
                <th style="width:19%" class="der">Precio unit.</th>
                <th style="width:19%" class="der">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devolucion->products_returned as $item)
                <tr>
                    <td>{{ $item['nombre'] }}</td>
                    <td class="centro">{{ $item['cantidad'] }}</td>
                    <td class="der">${{ number_format($item['precio_unitario'], 2, ',', '.') }}</td>
                    <td class="der">${{ number_format($item['subtotal'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" class="der total">TOTAL</td>
                <td class="der total">${{ number_format($devolucion->total_amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="firma" width="100%">
        <tr>
            <td>Firma del cliente</td>
            <td style="width:10%; border:0"></td>
            <td>Firma y aclaración</td>
        </tr>
    </table>

    <div class="footer">
        Emitido el {{ now()->format('d/m/Y H:i') }} por {{ $devolucion->user?->name }}
    </div>
</body>
</html>
