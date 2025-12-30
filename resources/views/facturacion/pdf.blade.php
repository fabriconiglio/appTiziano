<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->invoice_type }} - {{ $invoice->formatted_number }}</title>
    <style>
        @page {
            margin: 5mm;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            line-height: 1.2;
            color: #000;
            padding: 0;
            margin: 0;
        }
        
        .invoice-container {
            width: 90%;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 6px;
            min-height: 700px;
            margin-top: 20px;
        }
        
        /* Encabezado con tipo de factura */
        .invoice-header {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
        }
        
        .header-left {
            display: table-cell;
            width: 43%;
            vertical-align: top;
            padding-right: 5px;
        }
        
        .header-center {
            display: table-cell;
            width: 14%;
            vertical-align: middle;
            text-align: center;
        }
        
        .header-right {
            display: table-cell;
            width: 43%;
            vertical-align: top;
            padding-left: 5px;
        }
        
        .invoice-type-box {
            border: 3px solid #000;
            width: 60px;
            height: 60px;
            margin: 0 auto;
            text-align: center;
            padding-top: 6px;
        }
        
        .invoice-type-letter {
            font-size: 38px;
            font-weight: bold;
            line-height: 1;
        }
        
        .invoice-type-code {
            font-size: 6px;
            margin-top: 2px;
        }
        
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .company-info {
            font-size: 7px;
            line-height: 1.3;
            margin-bottom: 1px;
        }
        
        .invoice-type-text {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .invoice-number {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        /* Sección de información */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .info-box {
            display: table-cell;
            width: 50%;
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
            min-height: 100px;
        }
        
        .info-title {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
        }
        
        .info-row {
            font-size: 7px;
            margin-bottom: 6px;
            line-height: 1.4;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }
        
        /* Tabla de productos */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7px;
        }
        
        .products-table th {
            background-color: #e8e8e8;
            border: 1px solid #000;
            padding: 6px 3px;
            text-align: left;
            font-weight: bold;
            font-size: 7px;
        }
        
        .products-table td {
            border: 1px solid #000;
            padding: 5px 3px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        /* Sección de totales */
        .totals-section {
            width: 100%;
            margin-bottom: 12px;
        }
        
        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            font-size: 8px;
        }
        
        .totals-table td {
            padding: 6px 6px;
            border: 1px solid #000;
        }
        
        .totals-label {
            font-weight: bold;
            text-align: right;
            background-color: #f5f5f5;
        }
        
        .totals-amount {
            text-align: right;
            background-color: #fff;
        }
        
        .total-row td {
            background-color: #d0d0d0;
            font-weight: bold;
            font-size: 9px;
            padding: 5px;
        }
        
        /* Sección CAE */
        .cae-section {
            clear: both;
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 12px;
            background-color: #f9f9f9;
        }
        
        .cae-title {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
            text-transform: uppercase;
        }
        
        .cae-content {
            display: table;
            width: 100%;
        }
        
        .cae-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }
        
        .cae-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 8px;
        }
        
        .cae-data {
            font-size: 7px;
            margin-bottom: 8px;
        }
        
        .cae-value {
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        
        .qr-container {
            text-align: center;
            padding: 8px;
            background-color: #fff;
            border: 1px solid #000;
        }
        
        .qr-title {
            font-size: 7px;
            font-weight: bold;
            margin-bottom: 6px;
        }
        
        .qr-image {
            max-width: 80px;
            max-height: 80px;
        }
        
        .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 7px;
            letter-spacing: 0.2px;
            margin-top: 3px;
            word-wrap: break-word;
        }
        
        .barcode-note {
            font-size: 6px;
            color: #666;
            margin-top: 2px;
        }
        
        /* Footer */
        .invoice-footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #666;
            font-size: 6px;
            text-align: center;
            color: #666;
        }
        
        .footer-line {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Encabezado -->
        <div class="invoice-header">
            <div class="header-left">
                <div class="company-name">{{ $config['razon_social'] ?? 'TIZIANO' }}</div>
                <div class="company-info">
                    <strong>CUIT:</strong> {{ $config['cuit'] ?? '' }}
                </div>
                <div class="company-info">
                    <strong>Domicilio Comercial:</strong> {{ $config['domicilio_comercial'] ?? 'No especificado' }}
                </div>
                <div class="company-info">
                    <strong>Condición frente al IVA:</strong> {{ $config['condicion_iva'] ?? 'Responsable Inscripto' }}
                </div>
                @if(isset($config['inicio_actividades']) && $config['inicio_actividades'])
                <div class="company-info">
                    <strong>Inicio de Actividades:</strong> {{ $config['inicio_actividades'] }}
                </div>
                @endif
            </div>
            
            <div class="header-center">
                <div class="invoice-type-box">
                    <div class="invoice-type-letter">{{ $invoice->invoice_type }}</div>
                    <div class="invoice-type-code">COD. {{ str_pad($invoice->point_of_sale, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
            
            <div class="header-right">
                <div class="invoice-type-text">
                    FACTURA {{ $invoice->invoice_type }}
                </div>
                <div class="invoice-number">
                    Nro: {{ $invoice->formatted_number }}
                </div>
                <div class="company-info" style="margin-top: 10px;">
                    <strong>Fecha de Emisión:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}
                </div>
                <div class="company-info">
                    <strong>Punto de Venta:</strong> {{ str_pad($invoice->point_of_sale, 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>
        
        <div class="clear"></div>

        <!-- Datos del Cliente -->
        <div class="info-section">
            <div class="info-box">
                <div class="info-title">Datos del Receptor</div>
                @php
                    $client = $invoice->getClient();
                @endphp
                @if($client)
                    @if($invoice->client_type === 'distributor_client' || $invoice->client_type === 'client' || !$invoice->client_type)
                    <div class="info-row">
                        <span class="info-label">Nombre/Razón Social:</span>
                        <span>{{ $client->full_name ?? ($client->name . ' ' . $client->surname) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Domicilio:</span>
                        <span>{{ $client->domicilio ?? 'No especificado' }}</span>
                    </div>
                    @if($client->dni)
                    <div class="info-row">
                        <span class="info-label">DNI:</span>
                        <span>{{ $client->dni }}</span>
                    </div>
                    @endif
                    @if($client->phone)
                    <div class="info-row">
                        <span class="info-label">Teléfono:</span>
                        <span>{{ $client->phone }}</span>
                    </div>
                    @endif
                    @else
                    <div class="info-row">
                        <span class="info-label">Nombre:</span>
                        <span>{{ $client->nombre ?? 'No especificado' }}</span>
                    </div>
                    @if($client->telefono)
                    <div class="info-row">
                        <span class="info-label">Teléfono:</span>
                        <span>{{ $client->telefono }}</span>
                    </div>
                    @endif
                    @endif
                @else
                <div class="info-row">
                    <span class="info-label">Cliente:</span>
                    <span>No disponible</span>
                </div>
                @endif
            </div>
            
            <div class="info-box">
                <div class="info-title">Condiciones de Venta</div>
                <div class="info-row">
                    <span class="info-label">Condición de Venta:</span>
                    <span>Contado</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Condición frente al IVA:</span>
                    <span>Consumidor Final</span>
                </div>
                @if($invoice->notes)
                <div class="info-row" style="margin-top: 5px;">
                    <span class="info-label">Observaciones:</span>
                    <span>{{ $invoice->notes }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <div class="clear"></div>

        <!-- Detalle de Productos -->
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 40%;">Producto / Servicio</th>
                    <th style="width: 10%;" class="text-center">Cantidad</th>
                    <th style="width: 15%;" class="text-right">Precio Unit.</th>
                    <th style="width: 10%;" class="text-right">% IVA</th>
                    <th style="width: 15%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td class="text-center">{{ $item->product->id ?? 'N/A' }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->tax_rate, 2) }}%</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="totals-label">Subtotal (IVA incluido):</td>
                    <td class="totals-amount">${{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
                </tr>
                @if($invoice->invoice_type !== 'C')
                <tr>
                    <td class="totals-label">IVA (21% - incluido):</td>
                    <td class="totals-amount">${{ number_format($invoice->tax_amount, 2, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td class="totals-label">TOTAL:</td>
                    <td class="totals-amount">${{ number_format($invoice->total, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="clear"></div>

        <!-- Sección CAE -->
        <div class="cae-section">
            <div class="cae-title">Comprobante Autorizado por AFIP</div>
            
            <div class="cae-content">
                <div class="cae-left">
                    <div class="cae-data">
                        <strong>CAE Nro:</strong>
                        <div class="cae-value">{{ $invoice->cae }}</div>
                    </div>
                    <div class="cae-data">
                        <strong>Fecha de Vto. de CAE:</strong> {{ $invoice->cae_expiration->format('d/m/Y') }}
                    </div>
                </div>
                
                <div class="cae-right">
                    <div class="qr-container">
                        @if($qrCode)
                            <div class="qr-title">Código QR:</div>
                            <img src="{{ $qrCode }}" alt="QR Code AFIP" class="qr-image">
                            <div class="barcode-note">
                                Escanear para verificar en AFIP
                            </div>
                        @else
                            <div class="qr-title">Código de barras:</div>
                            <div class="barcode-text">{{ $barcodeData }}</div>
                            <div class="barcode-note">
                                (Representación numérica del código de barras)
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-line"><strong>Documento generado el {{ $generatedAt }}</strong></div>
            <div class="footer-line">
                Esta es una representación impresa de una factura electrónica.
            </div>
            <div class="footer-line">
                Para verificar su autenticidad ingrese a www.afip.gob.ar con el código de autorización.
            </div>
        </div>
    </div>
</body>
</html>

