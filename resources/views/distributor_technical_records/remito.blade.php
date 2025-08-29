<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Remito - Ficha Técnica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #000;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .client-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .client-info h3 {
            margin: 0 0 10px 0;
            color: #000;
        }
        .client-details {
            display: flex;
            justify-content: space-between;
        }
        .client-details div {
            flex: 1;
        }
        .purchase-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9e9e9;
            border-radius: 5px;
        }
        .purchase-info h3 {
            margin: 0 0 10px 0;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #666;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        .total-row {
            background-color: #333 !important;
            color: white;
            font-weight: bold;
        }
        .total-row td {
            border-bottom: none;
        }

        .advance-row {
            background-color: #fff3cd !important;
            color: #856404;
            font-weight: bold;
        }

        .final-row {
            background-color: #d1ecf1 !important;
            color: #0c5460;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
        }
        .signature-box p {
            margin: 5px 0;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REMITO</h1>
        <p>Fecha de generación: {{ $generatedDate }}</p>
    </div>

    <div class="client-info">
        <h3>Información del Cliente</h3>
        <div class="client-details">
            <div>
                <strong>Nombre:</strong> {{ $distributorClient->name }}<br>
                <strong>DNI:</strong> {{ $distributorClient->dni }}<br>
                <strong>Teléfono:</strong> {{ $distributorClient->phone }}
            </div>
            <div>
                <strong>Email:</strong> {{ $distributorClient->email }}<br>
                <strong>Domicilio:</strong> {{ $distributorClient->domicilio }}<br>
                <strong>Fecha de Registro:</strong> {{ $distributorClient->created_at->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <div class="purchase-info">
        <h3>Información de la Compra</h3>
        <div class="client-details">
            <div>
                <strong>Número de Ficha:</strong> #{{ $technicalRecord->id }}<br>
                <strong>Fecha de Compra:</strong> {{ $technicalRecord->purchase_date->format('d/m/Y') }}<br>
                <strong>Tipo de Compra:</strong> 
                @switch($technicalRecord->purchase_type)
                    @case('al_por_mayor')
                        Al por Mayor
                        @break
                    @case('al_por_menor')
                        Al por Menor
                        @break
                    @case('especial')
                        Compra Especial
                        @break
                    @default
                        No especificado
                @endswitch
            </div>
            <div>
                <strong>Método de Pago:</strong> 
                @switch($technicalRecord->payment_method)
                    @case('efectivo')
                        Efectivo
                        @break
                    @case('tarjeta')
                        Tarjeta
                        @break
                    @case('transferencia')
                        Transferencia
                        @break
                    @case('cheque')
                        Cheque
                        @break
                    @default
                        No especificado
                @endswitch<br>
                <strong>Vendedor:</strong> {{ $technicalRecord->user->name }}<br>
                <strong>Total:</strong> ${{ number_format($technicalRecord->total_amount, 2) }}
                @if($technicalRecord->final_amount != $technicalRecord->total_amount)
                <br><strong>Ajuste CC:</strong> ${{ number_format(abs($technicalRecord->total_amount - $technicalRecord->final_amount), 2) }}
                @endif
                <br><strong>Monto Final:</strong> ${{ number_format($technicalRecord->final_amount, 2) }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Descripción - Marca</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product['name'] }}</td>
                <td>{{ $product['description'] }}</td>
                <td>{{ $product['quantity'] }}</td>
                <td>${{ number_format($product['unit_price'], 2) }}</td>
                <td>${{ number_format($product['total_price'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right;"><strong>TOTAL:</strong></td>
                <td><strong>${{ number_format($technicalRecord->total_amount, 2) }}</strong></td>
            </tr>
            @if($technicalRecord->final_amount != $technicalRecord->total_amount)
            <tr class="advance-row">
                <td colspan="4" style="text-align: right;"><strong>AJUSTE CUENTA CORRIENTE:</strong></td>
                <td><strong>${{ number_format(abs($technicalRecord->total_amount - $technicalRecord->final_amount), 2) }}</strong></td>
            </tr>
            <tr class="final-row">
                <td colspan="4" style="text-align: right;"><strong>MONTO FINAL:</strong></td>
                <td><strong>${{ number_format($technicalRecord->final_amount, 2) }}</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    @if($technicalRecord->observations)
    <div style="margin-bottom: 20px;">
        <h3>Observaciones:</h3>
        <p style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">
            {!! strip_tags($technicalRecord->observations) !!}
        </p>
    </div>
    @endif

    @if($technicalRecord->next_purchase_notes)
    <div style="margin-bottom: 20px;">
        <h3>Notas para Próxima Compra:</h3>
        <p style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">
            {!! strip_tags($technicalRecord->next_purchase_notes) !!}
        </p>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Firma del Cliente</strong></p>
            <p>_____________________</p>
        </div>
        <div class="signature-box">
            <p><strong>Firma del Vendedor</strong></p>
            <p>_____________________</p>
        </div>
    </div>

    <div class="footer">
        <p>Documento generado automáticamente por App Tiziano</p>
        <p>Total de productos: {{ count($products) }}</p>
    </div>
</body>
</html> 