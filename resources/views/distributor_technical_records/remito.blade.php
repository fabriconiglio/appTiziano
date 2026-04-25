<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tiziano Distribuidora - Remito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 15px 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .header-left {
            width: 55%;
            font-size: 11px;
        }
        .header-right {
            width: 45%;
            text-align: right;
        }
        .header-right img {
            max-height: 110px;
        }
        .header-left .doc-info {
            margin-bottom: 3px;
        }
        .header-left .doc-info strong {
            display: inline-block;
            width: 110px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 8px 0;
            padding: 5px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        .info-section {
            margin-bottom: 12px;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .info-section h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid td {
            border: none;
            padding: 2px 5px;
            vertical-align: top;
            font-size: 11px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        .products-table th {
            background-color: #e8e8e8;
            border: 1px solid #999;
            padding: 6px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        .products-table td {
            border: 1px solid #ccc;
            padding: 5px;
            vertical-align: middle;
        }
        .products-table .text-center {
            text-align: center;
        }
        .products-table .text-right {
            text-align: right;
        }
        .product-img {
            width: 65px;
            height: 65px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        .no-img {
            width: 65px;
            height: 45px;
            background-color: #f5f5f5;
            border: 1px solid #e0e0e0;
            display: block;
            margin: 0 auto;
            text-align: center;
            line-height: 45px;
            color: #bbb;
            font-size: 8px;
        }
        .total-row {
            background-color: #333 !important;
            color: white;
            font-weight: bold;
        }
        .total-row td {
            border-bottom: none;
            color: white;
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
        .discount-row {
            background-color: #fff3cd !important;
            color: #856404;
            font-weight: bold;
        }
        .discount-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #ffc107;
        }
        .discount-section h3 {
            margin: 0 0 10px 0;
            color: #856404;
            font-size: 13px;
        }
        .discount-item {
            margin-bottom: 8px;
            padding: 8px;
            background-color: white;
            border: 1px solid #dee2e6;
        }
        .discount-item:last-child {
            margin-bottom: 0;
        }
        .discount-header {
            font-weight: bold;
            color: #495057;
            margin-bottom: 3px;
        }
        .discount-details {
            font-size: 10px;
            color: #6c757d;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .signature-table td {
            border: none;
            width: 45%;
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid #333;
            font-size: 11px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Encabezado estilo TANGENTE -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="doc-info"><strong>Fecha:</strong> {{ $generatedDate }}</div>
                <div class="doc-info"><strong>Cliente:</strong> {{ $distributorClient->name }}</div>
                <div class="doc-info"><strong>DNI:</strong> {{ $distributorClient->dni }}</div>
                <div class="doc-info"><strong>Ficha N°:</strong> #{{ $technicalRecord->id }}</div>
            </td>
            <td class="header-right">
                <img src="{{ public_path('images/tiziano-logo-final.jpeg') }}" alt="Tiziano">
            </td>
        </tr>
    </table>

    <div class="doc-title">REMITO</div>

    <!-- Información del Cliente -->
    <div class="info-section">
        <h3>Información del Cliente</h3>
        <table class="info-grid">
            <tr>
                <td style="width: 50%;">
                    <strong>Teléfono:</strong> {{ $distributorClient->phone }}<br>
                    <strong>Email:</strong> {{ $distributorClient->email }}<br>
                    <strong>Domicilio:</strong> {{ $distributorClient->domicilio }}
                </td>
                <td style="width: 50%;">
                    <strong>Fecha de Registro:</strong> {{ $distributorClient->created_at->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Información de la Compra -->
    <div class="info-section" style="background-color: #e9e9e9;">
        <h3>Información de la Compra</h3>
        <table class="info-grid">
            <tr>
                <td style="width: 50%;">
                    <strong>Fecha de Compra:</strong> {{ $technicalRecord->purchase_date->format('d/m/Y') }}<br>
                    <strong>Tipo de Compra:</strong>
                    @switch($technicalRecord->purchase_type)
                        @case('al_por_mayor') Al por Mayor @break
                        @case('al_por_menor') Al por Menor @break
                        @case('especial') Compra Especial @break
                        @default No especificado
                    @endswitch
                </td>
                <td style="width: 50%;">
                    <strong>Método de Pago:</strong>
                    @switch($technicalRecord->payment_method)
                        @case('efectivo') Efectivo @break
                        @case('tarjeta') Tarjeta @break
                        @case('transferencia') Transferencia @break
                        @case('deuda') Deuda/Deudor @break
                        @default No especificado
                    @endswitch<br>
                    <strong>Vendedor:</strong> {{ $technicalRecord->user->name }}<br>
                    <strong>Total:</strong> ${{ number_format($technicalRecord->total_amount, 2) }}
                    @if($technicalRecord->final_amount != $technicalRecord->total_amount)
                    <br><strong>Ajuste CC:</strong> ${{ number_format(abs($technicalRecord->total_amount - $technicalRecord->final_amount), 2) }}
                    @endif
                    <br><strong>Monto Final:</strong> ${{ number_format($technicalRecord->final_amount, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Productos -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;"></th>
                <th style="width: 25%;">Producto</th>
                <th style="width: 25%;">Descripción - Marca</th>
                <th style="width: 8%; text-align: center;">Cant.</th>
                <th style="width: 16%; text-align: right;">Precio Unit.</th>
                <th style="width: 16%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td class="text-center">
                    @if(!empty($product['image_path']))
                        <img src="{{ $product['image_path'] }}" class="product-img" alt="{{ $product['name'] }}">
                    @else
                        <span class="no-img">Sin imagen</span>
                    @endif
                </td>
                <td>{{ $product['name'] }}</td>
                <td>{{ $product['description'] }}</td>
                <td class="text-center">{{ $product['quantity'] }}</td>
                <td class="text-right">
                    @if($product['has_discount'])
                        <span style="text-decoration: line-through; color: #6c757d;">${{ number_format($product['original_unit_price'], 2) }}</span><br>
                        <strong style="color: #28a745;">${{ number_format($product['unit_price'], 2) }}</strong>
                    @else
                        ${{ number_format($product['unit_price'], 2) }}
                    @endif
                </td>
                <td class="text-right">
                    @if($product['has_discount'])
                        <span style="text-decoration: line-through; color: #6c757d;">${{ number_format($product['original_total_price'], 2) }}</span><br>
                        <strong style="color: #28a745;">${{ number_format($product['total_price'], 2) }}</strong>
                    @else
                        ${{ number_format($product['total_price'], 2) }}
                    @endif
                </td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" style="text-align: right;"><strong>TOTAL:</strong></td>
                <td style="text-align: right;"><strong>${{ number_format($technicalRecord->total_amount, 2) }}</strong></td>
            </tr>
            @if(!empty($manualDiscounts))
            @php
                $totalSavings = array_sum(array_column($manualDiscounts, 'savings'));
            @endphp
            <tr class="discount-row">
                <td colspan="5" style="text-align: right;"><strong>DESCUENTOS MANUALES:</strong></td>
                <td style="text-align: right;"><strong>-${{ number_format($totalSavings, 2) }}</strong></td>
            </tr>
            @endif
            @if($technicalRecord->final_amount != $technicalRecord->total_amount)
            <tr class="advance-row">
                <td colspan="5" style="text-align: right;"><strong>AJUSTE CUENTA CORRIENTE:</strong></td>
                <td style="text-align: right;"><strong>${{ number_format(abs($technicalRecord->total_amount - $technicalRecord->final_amount), 2) }}</strong></td>
            </tr>
            <tr class="final-row">
                <td colspan="5" style="text-align: right;"><strong>MONTO FINAL:</strong></td>
                <td style="text-align: right;"><strong>${{ number_format($technicalRecord->final_amount, 2) }}</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    @if(!empty($manualDiscounts))
    <div class="discount-section">
        <h3>Descuentos Manuales Aplicados</h3>
        @foreach($manualDiscounts as $discount)
        <div class="discount-item">
            <div class="discount-header">
                {{ $discount['product_name'] }} - Descuento: {{ $discount['discount_value'] }}{{ $discount['discount_type'] }}
            </div>
            <div class="discount-details">
                <strong>Motivo:</strong> {{ $discount['discount_reason'] }}<br>
                <strong>Precio original:</strong> ${{ number_format($discount['original_price'], 2) }} --
                <strong>Precio con descuento:</strong> ${{ number_format($discount['discounted_price'], 2) }}<br>
                <strong>Ahorro total:</strong> ${{ number_format($discount['savings'], 2) }}
                ({{ number_format(($discount['savings'] / $discount['original_total']) * 100, 1) }}%)
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($technicalRecord->observations)
    <div style="margin-bottom: 12px;">
        <h3 style="font-size: 12px; margin-bottom: 5px;">Observaciones:</h3>
        <p style="background-color: #f5f5f5; padding: 8px; margin: 0;">
            {!! strip_tags($technicalRecord->observations) !!}
        </p>
    </div>
    @endif

    @if($technicalRecord->next_purchase_notes)
    <div style="margin-bottom: 12px;">
        <h3 style="font-size: 12px; margin-bottom: 5px;">Notas para Próxima Compra:</h3>
        <p style="background-color: #f5f5f5; padding: 8px; margin: 0;">
            {!! strip_tags($technicalRecord->next_purchase_notes) !!}
        </p>
    </div>
    @endif

    <table class="signature-table">
        <tr>
            <td><strong>Firma del Cliente</strong><br>_____________________</td>
            <td style="width: 10%; border: none;"></td>
            <td><strong>Firma del Vendedor</strong><br>_____________________</td>
        </tr>
    </table>

    <div class="footer">
        <p>Documento generado automáticamente por Tiziano Distribuidora</p>
        <p>Total de productos: {{ count($products) }}</p>
    </div>
</body>
</html>
