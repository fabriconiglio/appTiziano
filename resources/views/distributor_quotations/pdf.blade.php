<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto {{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
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
            width: 90px;
        }
        .attention-bar {
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 10px;
            font-weight: bold;
        }
        .intro-text {
            font-size: 10px;
            color: #666;
            margin-bottom: 12px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            border: none;
            padding: 3px 8px 3px 0;
            vertical-align: top;
            font-size: 11px;
        }
        .info-table .section-title {
            font-weight: bold;
            font-size: 12px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
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
        .totals-table {
            width: 50%;
            border-collapse: collapse;
            margin-left: 50%;
            margin-bottom: 15px;
        }
        .totals-table td {
            padding: 4px 8px;
            border: none;
            font-size: 11px;
        }
        .totals-table .total-final td {
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #333;
            padding-top: 8px;
        }
        .observations-section {
            margin-bottom: 15px;
        }
        .observations-section h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 6px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .photos-section {
            margin-top: 15px;
        }
        .photos-section h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        .photo-item {
            display: inline-block;
            width: 140px;
            height: 140px;
            border: 1px solid #ccc;
            text-align: center;
            vertical-align: top;
            margin: 0 8px 8px 0;
            background-color: #f9f9f9;
        }
        .photo-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <!-- Encabezado estilo TANGENTE -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="doc-info"><strong>Fecha:</strong> {{ $quotation->quotation_date->format('d/m/Y') }}</div>
                <div class="doc-info"><strong>A:</strong> {{ $distributorClient->full_name }}</div>
                <div class="doc-info"><strong>DNI:</strong> {{ $distributorClient->dni ?? '-' }}</div>
                <div class="doc-info"><strong>PR:</strong> {{ $quotation->quotation_number }}</div>
            </td>
            <td class="header-right">
                <img src="{{ public_path('images/tiziano-logo-final.jpeg') }}" alt="Tiziano">
            </td>
        </tr>
    </table>

    <div class="attention-bar">
        Atención: Precios sujetos a cambio sin previo aviso.
    </div>

    <div class="intro-text">
        Por medio del presente tenemos el agrado de remitir a Uds. nuestra propuesta de suministro de acuerdo al siguiente detalle.
    </div>

    <!-- Info adicional -->
    <table class="info-table">
        <tr>
            <td style="width: 33%;">
                <span class="info-label">Teléfono:</span> {{ $distributorClient->phone ?? '-' }}<br>
                <span class="info-label">Email:</span> {{ $distributorClient->email ?? '-' }}<br>
                <span class="info-label">Domicilio:</span> {{ $distributorClient->domicilio ?? '-' }}
            </td>
            <td style="width: 33%;">
                <span class="info-label">Válido hasta:</span> {{ $quotation->valid_until->format('d/m/Y') }}<br>
                <span class="info-label">Tipo:</span> {{ $quotation->getTypeFormattedAttribute() }}<br>
                <span class="info-label">Estado:</span> {{ $quotation->getStatusFormattedAttribute() }}
            </td>
            <td style="width: 33%;">
                <span class="info-label">IVA:</span> {{ $quotation->tax_percentage }}%<br>
                <span class="info-label">Descuento:</span> {{ $quotation->discount_percentage ?? 0 }}%<br>
                <span class="info-label">Pago:</span> {{ $quotation->payment_terms ?? '-' }}<br>
                <span class="info-label">Entrega:</span> {{ $quotation->delivery_terms ?? '-' }}
            </td>
        </tr>
    </table>

    <!-- Productos -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">ITEM</th>
                <th style="width: 10%; text-align: center;"></th>
                <th style="width: 35%;">DESCRIPCIÓN</th>
                <th style="width: 10%;">COD.</th>
                <th style="width: 8%; text-align: center;">CANT.</th>
                <th style="width: 15%; text-align: right;">PRECIO U.</th>
                <th style="width: 17%; text-align: right;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->products_quoted as $index => $product)
                @php
                    $productInfo = App\Models\SupplierInventory::with('distributorBrand')->find($product['product_id']);
                    $productName = $productInfo ? $productInfo->product_name : 'Producto no encontrado';
                    $productDescription = $productInfo && $productInfo->description ? $productInfo->description : '';
                    $productBrand = $productInfo && $productInfo->distributorBrand ? $productInfo->distributorBrand->name : ($productInfo && $productInfo->brand ? $productInfo->brand : '');
                    $productCode = $productInfo && $productInfo->code ? $productInfo->code : ($productInfo && $productInfo->supplier_code ? $productInfo->supplier_code : '');

                    $displayText = $productName;
                    if ($productDescription) $displayText .= ' - ' . $productDescription;
                    if ($productBrand) $displayText .= ' - ' . $productBrand;

                    $productImage = null;
                    if ($productInfo && !empty($productInfo->images) && is_array($productInfo->images)) {
                        $imagePath = storage_path('app/public/' . $productInfo->images[0]);
                        if (file_exists($imagePath)) {
                            $productImage = $imagePath;
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center"><strong>{{ $index + 1 }}</strong></td>
                    <td class="text-center">
                        @if($productImage)
                            <img src="{{ $productImage }}" class="product-img" alt="{{ $productName }}">
                        @else
                            <span class="no-img">Sin imagen</span>
                        @endif
                    </td>
                    <td>{{ $displayText }}</td>
                    <td>{{ $productCode }}</td>
                    <td class="text-center">{{ $product['quantity'] }}</td>
                    <td class="text-right">${{ number_format($product['price'], 2) }}</td>
                    <td class="text-right">${{ number_format($product['quantity'] * $product['price'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales alineados a la derecha -->
    <table class="totals-table">
        <tr>
            <td style="text-align: right;">Subtotal:</td>
            <td style="text-align: right;">${{ number_format($quotation->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right;">IVA ({{ $quotation->tax_percentage }}%):</td>
            <td style="text-align: right;">${{ number_format($quotation->tax_amount, 2) }}</td>
        </tr>
        @if($quotation->discount_percentage > 0)
            <tr>
                <td style="text-align: right;">Descuento ({{ $quotation->discount_percentage }}%):</td>
                <td style="text-align: right;">-${{ number_format($quotation->discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="total-final">
            <td style="text-align: right;">TOTAL (IVA INCLUIDO)</td>
            <td style="text-align: right;">${{ number_format($quotation->final_amount, 2) }}</td>
        </tr>
    </table>

    <!-- Observaciones -->
    @if($quotation->observations)
        <div class="observations-section">
            <h3>Observaciones</h3>
            <div>{!! nl2br(e($quotation->observations)) !!}</div>
        </div>
    @endif

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
            @foreach($quotation->photos as $photo)
                <div class="photo-item">
                    <img src="{{ Storage::url($photo) }}" alt="Foto del presupuesto">
                </div>
            @endforeach
        </div>
    @endif

    <!-- Pie de Página -->
    <div class="footer">
        <p><strong>Presupuesto generado el {{ $generatedAt }}</strong></p>
        <p>Este presupuesto es válido hasta el {{ $quotation->valid_until->format('d/m/Y') }}</p>
        <p>Para consultas o modificaciones, contacte a nuestro equipo comercial</p>
    </div>
</body>
</html>
