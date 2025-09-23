<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Remito - Cliente No Frecuente</title>
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
        .purchase-details {
            display: flex;
            justify-content: space-between;
        }
        .purchase-details div {
            flex: 1;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .products-table th,
        .products-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .products-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .products-table .text-right {
            text-align: right;
        }
        .products-table .text-center {
            text-align: center;
        }
        .total-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .total-row.final {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .discount-info {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REMITO DE VENTA</h1>
        <p>Cliente No Frecuente - Distribuidora</p>
        <p>Generado el: {{ $generatedDate }}</p>
    </div>

    <div class="client-info">
        <h3>Información del Cliente</h3>
        <div class="client-details">
            <div>
                <strong>Nombre:</strong> {{ $cliente->nombre ?: 'Sin nombre' }}<br>
                <strong>Teléfono:</strong> {{ $cliente->telefono ?: 'Sin teléfono' }}
            </div>
            <div>
                <strong>Fecha de Venta:</strong> {{ $cliente->fecha->format('d/m/Y') }}<br>
                <strong>Tipo de Compra:</strong> {{ $cliente->purchase_type === 'al_por_mayor' ? 'Al por Mayor' : 'Al por Menor' }}
            </div>
        </div>
    </div>

    <div class="purchase-info">
        <h3>Información de la Venta</h3>
        <div class="purchase-details">
            <div>
                <strong>Valor Total:</strong> ${{ number_format($cliente->monto, 2) }}
            </div>
            <div>
                <strong>Registrado por:</strong> {{ $cliente->user->name }}<br>
                <strong>Fecha de Registro:</strong> {{ $cliente->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    @if(count($products) > 0)
        <h3>Productos Comprados</h3>
        <table class="products-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Descuento</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
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
                                Sin descuento
                            @endif
                        </td>
                        <td class="text-right">${{ number_format($product['subtotal'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row final">
                <span><strong>TOTAL: ${{ number_format($cliente->monto, 2) }}</strong></span>
            </div>
        </div>
    @else
        <div class="purchase-info">
            <h3>Productos</h3>
            <p>No se registraron productos específicos para esta venta.</p>
        </div>
    @endif

    @if($cliente->observaciones)
        <div class="purchase-info">
            <h3>Observaciones</h3>
            <p>{{ $cliente->observaciones }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Este remito fue generado automáticamente por el sistema.</p>
        <p>ID del Cliente: {{ $cliente->id }} | Generado el: {{ $generatedDate }}</p>
    </div>
</body>
</html>
