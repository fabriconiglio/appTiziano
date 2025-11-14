<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto {{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .document-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .quotation-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-section {
            flex: 1;
        }
        .info-section h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .products-table th,
        .products-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .products-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .products-table .text-center {
            text-align: center;
        }
        .products-table .text-right {
            text-align: right;
        }
        .summary-section {
            margin-bottom: 30px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .summary-total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        .observations-section {
            margin-bottom: 30px;
        }
        .observations-section h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        .page-break {
            page-break-before: always;
        }
        .photos-section {
            margin-top: 20px;
        }
        .photos-section h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .photo-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .photo-item {
            width: 150px;
            height: 150px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9;
        }
        .photo-placeholder {
            color: #999;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <div class="company-name">TIZIANO</div>
        <div class="document-title">PRESUPUESTO</div>
        <div>N° {{ $quotation->quotation_number }}</div>
    </div>

    <!-- Información del Presupuesto -->
    <div class="quotation-info">
        <div class="info-section">
            <h3>Información del Cliente</h3>
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span>{{ $quotation->nombre ?? 'Cliente no especificado' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Teléfono:</span>
                <span>{{ $quotation->telefono ?? 'No especificado' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span>{{ $quotation->email ?? 'No especificado' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dirección:</span>
                <span>{{ $quotation->direccion ?? 'No especificado' }}</span>
            </div>
        </div>

        <div class="info-section">
            <h3>Detalles del Presupuesto</h3>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span>{{ $quotation->quotation_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Válido hasta:</span>
                <span>{{ $quotation->valid_until->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipo:</span>
                <span>{{ $quotation->getTypeFormattedAttribute() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span>{{ $quotation->getStatusFormattedAttribute() }}</span>
            </div>
        </div>

        <div class="info-section">
            <h3>Condiciones</h3>
            <div class="info-row">
                <span class="info-label">IVA:</span>
                <span>{{ $quotation->tax_percentage }}%</span>
            </div>
            <div class="info-row">
                <span class="info-label">Descuento:</span>
                <span>{{ $quotation->discount_percentage ?? 0 }}%</span>
            </div>
            <div class="info-row">
                <span class="info-label">Pago:</span>
                <span>{{ $quotation->payment_terms ?? 'No especificado' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Entrega:</span>
                <span>{{ $quotation->delivery_terms ?? 'No especificado' }}</span>
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="summary-section">
        <h3>Productos Cotizados</h3>
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 35%">Producto</th>
                    <th style="width: 15%">Marca</th>
                    <th style="width: 10%" class="text-center">Cantidad</th>
                    <th style="width: 15%" class="text-right">Precio Unit.</th>
                    <th style="width: 20%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->products_quoted as $index => $product)
                    @php
                        $productInfo = App\Models\SupplierInventory::with('distributorBrand')->find($product['product_id']);
                        $productName = $productInfo ? $productInfo->product_name : 'Producto no encontrado';
                        $productDescription = $productInfo && $productInfo->description ? $productInfo->description : '';
                        $productBrand = $productInfo && $productInfo->distributorBrand ? $productInfo->distributorBrand->name : ($productInfo && $productInfo->brand ? $productInfo->brand : 'N/A');
                        
                        $displayText = $productName;
                        if ($productDescription) $displayText .= ' - ' . $productDescription;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $displayText }}</td>
                        <td>{{ $productBrand }}</td>
                        <td class="text-center">{{ $product['quantity'] }}</td>
                        <td class="text-right">${{ number_format($product['price'], 2) }}</td>
                        <td class="text-right">${{ number_format($product['quantity'] * $product['price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Resumen de Totales -->
    <div class="summary-section">
        <h3>Resumen de Totales</h3>
        <div class="summary-row">
            <span>Subtotal:</span>
            <span>${{ number_format($quotation->subtotal, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>IVA ({{ $quotation->tax_percentage }}%):</span>
            <span>${{ number_format($quotation->tax_amount, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Total con IVA:</span>
            <span>${{ number_format($quotation->total_amount, 2) }}</span>
        </div>
        @if($quotation->discount_percentage > 0)
            <div class="summary-row">
                <span>Descuento ({{ $quotation->discount_percentage }}%):</span>
                <span>-${{ number_format($quotation->discount_amount, 2) }}</span>
            </div>
        @endif
        <div class="summary-row summary-total">
            <span><strong>TOTAL FINAL:</strong></span>
            <span><strong>${{ number_format($quotation->final_amount, 2) }}</strong></span>
        </div>
    </div>

    <!-- Observaciones -->
    @if($quotation->observations)
        <div class="observations-section">
            <h3>Observaciones</h3>
            <div>{!! nl2br(e($quotation->observations)) !!}</div>
        </div>
    @endif

    <!-- Términos y Condiciones -->
    @if($quotation->terms_conditions)
        <div class="observations-section">
            <h3>Términos y Condiciones</h3>
            <div>{!! nl2br(e($quotation->terms_conditions)) !!}</div>
        </div>
    @endif

    <!-- Fotos -->
    @if(!empty($quotation->photos))
        <div class="photos-section">
            <h3>Fotos del Presupuesto</h3>
            <div class="photo-grid">
                @foreach($quotation->photos as $photo)
                    <div class="photo-item">
                        <img src="{{ Storage::url($photo) }}" 
                             alt="Foto del presupuesto" 
                             style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pie de Página -->
    <div class="footer">
        <p><strong>Presupuesto generado el {{ $generatedAt }}</strong></p>
        <p>Este presupuesto es válido hasta el {{ $quotation->valid_until->format('d/m/Y H:i') }}</p>
        <p>Para consultas o modificaciones, contacte a nuestro equipo comercial</p>
    </div>
</body>
</html> 