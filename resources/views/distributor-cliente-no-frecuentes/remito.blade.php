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
        .total-section {
            margin-top: 10px;
        }
        .totals-table {
            width: 50%;
            border-collapse: collapse;
            margin-left: 50%;
        }
        .totals-table td {
            padding: 4px 8px;
            border: none;
            font-size: 11px;
        }
        .totals-table .total-final td {
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #000;
            padding-top: 8px;
        }
        .discount-info {
            font-size: 9px;
            color: #666;
            font-style: italic;
        }
        .footer {
            margin-top: 25px;
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
                <div class="doc-info"><strong>Cliente:</strong> {{ $cliente->nombre ?: 'Sin nombre' }}</div>
                <div class="doc-info"><strong>Teléfono:</strong> {{ $cliente->telefono ?: '-' }}</div>
                <div class="doc-info"><strong>ID:</strong> {{ $cliente->id }}</div>
            </td>
            <td class="header-right">
                <img src="{{ public_path('images/tiziano-logo-final.jpeg') }}" alt="Tiziano">
            </td>
        </tr>
    </table>

    <div class="doc-title">REMITO DE VENTA - Cliente No Frecuente</div>

    <!-- Información de la Venta -->
    <div class="info-section" style="background-color: #e9e9e9;">
        <h3>Información de la Venta</h3>
        <table class="info-grid">
            <tr>
                <td style="width: 50%;">
                    <strong>Fecha de Venta:</strong> {{ $cliente->fecha->format('d/m/Y') }}<br>
                    <strong>Tipo de Compra:</strong> {{ $cliente->purchase_type === 'al_por_mayor' ? 'Al por Mayor' : 'Al por Menor' }}
                </td>
                <td style="width: 50%;">
                    <strong>Valor Total:</strong> ${{ number_format($cliente->monto, 2) }}<br>
                    <strong>Registrado por:</strong> {{ $cliente->user->name }}<br>
                    <strong>Fecha de Registro:</strong> {{ $cliente->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    @if(count($products) > 0)
        <!-- Productos -->
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 10%; text-align: center;"></th>
                    <th style="width: 35%;">Producto</th>
                    <th style="width: 8%; text-align: center;">Cant.</th>
                    <th style="width: 15%; text-align: right;">Precio Unit.</th>
                    <th style="width: 15%; text-align: right;">Descuento</th>
                    <th style="width: 17%; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td class="text-center">
                            @if(!empty($product['image_path']))
                                <img src="{{ $product['image_path'] }}" class="product-img" alt="{{ $product['product_name'] }}">
                            @else
                                <span class="no-img">Sin imagen</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $product['product_name'] }}</strong><br>
                            @if($product['description'])
                                <small>{{ $product['description'] }}</small><br>
                            @endif
                            <small><strong>Marca:</strong> {{ $product['brand'] ?: 'Sin marca' }}</small>
                        </td>
                        <td class="text-center">{{ $product['quantity'] }}</td>
                        <td class="text-right">${{ number_format($product['original_price'], 2) }}</td>
                        <td class="text-right">
                            @if($product['discount_type'] && $product['discount_value'])
                                @if($product['discount_type'] === 'percentage')
                                    -{{ $product['discount_value'] }}%
                                @else
                                    -${{ number_format($product['discount_value'], 2) }}
                                @endif
                                @if($product['discount_reason'])
                                    <br><small class="discount-info">{{ $product['discount_reason'] }}</small>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">${{ number_format($product['subtotal'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <table class="totals-table">
                <tr class="total-final">
                    <td style="text-align: right;">TOTAL (IVA INCLUIDO)</td>
                    <td style="text-align: right;">${{ number_format($cliente->monto, 2) }}</td>
                </tr>
            </table>
        </div>
    @else
        <div class="info-section">
            <h3>Productos</h3>
            <p>No se registraron productos específicos para esta venta.</p>
        </div>
    @endif

    @if($cliente->observaciones)
        <div style="margin: 12px 0;">
            <h3 style="font-size: 12px; margin-bottom: 5px;">Observaciones</h3>
            <p style="background-color: #f5f5f5; padding: 8px; margin: 0;">{{ $cliente->observaciones }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Documento generado automáticamente por Tiziano Distribuidora</p>
        <p>ID del Cliente: {{ $cliente->id }} | Generado el: {{ $generatedDate }}</p>
    </div>
</body>
</html>
