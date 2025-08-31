<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas Diarias - Peluquería - {{ $today->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 200px;
            text-align: center;
        }
        .card.primary { background-color: #007bff; color: white; }
        .card.success { background-color: #28a745; color: white; }
        .card.info { background-color: #17a2b8; color: white; }
        .card.warning { background-color: #ffc107; color: #212529; }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
        }
        .card .amount {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .card .count {
            font-size: 14px;
            opacity: 0.8;
        }
        .comparison-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .comparison-section h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .comparison-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .comparison-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 18px;
        }
        .stats-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-section h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .popular-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .popular-section h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .popular-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .popular-item:last-child {
            margin-bottom: 0;
        }
        .popular-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .popular-info small {
            color: #666;
        }
        .popular-stats {
            text-align: right;
        }
        .popular-stats .badge {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
        }
        .hourly-table {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .hourly-table h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas Diarias - Peluquería</h1>
        <p>Fecha: {{ $today->format('d/m/Y') }} | Generado: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="summary-cards">
        <div class="card primary">
            <h3>Total del Día</h3>
            <div class="amount">${{ number_format($todaySales['total'], 2) }}</div>
        </div>
        
        <div class="card success">
            <h3>Cuentas Corrientes</h3>
            <div class="amount">${{ number_format($todaySales['client_accounts'], 2) }}</div>
            <div class="count">{{ $todaySales['count_client_accounts'] }} ventas</div>
        </div>
        
        <div class="card info">
            <h3>Servicios</h3>
            <div class="amount">${{ number_format($todaySales['technical_records'], 2) }}</div>
            <div class="count">{{ $todaySales['count_technical_records'] }} servicios</div>
        </div>
        
        <div class="card warning">
            <h3>Productos</h3>
            <div class="amount">${{ number_format($todaySales['product_sales'], 2) }}</div>
            <div class="count">{{ $todaySales['count_product_sales'] }} ventas</div>
        </div>
    </div>

    <!-- Servicios y productos más populares -->
    <div class="popular-section">
        <h2>Servicios Más Populares del Día</h2>
        @if($popularServices->count() > 0)
            @foreach($popularServices as $service)
                <div class="popular-item">
                    <div class="popular-info">
                        <h4>{{ $service->hair_treatments ?? 'Servicio General' }}</h4>
                        <small>Tratamiento de cabello</small>
                    </div>
                    <div class="popular-stats">
                        <span class="badge">{{ $service->total }}</span>
                    </div>
                </div>
            @endforeach
        @else
            <p style="text-align: center; color: #666;">No hay servicios registrados para este día</p>
        @endif
    </div>

    <div class="popular-section">
        <h2>Productos Más Vendidos del Día</h2>
        @if($popularProducts->count() > 0)
            @foreach($popularProducts as $product)
                <div class="popular-item">
                    <div class="popular-info">
                        <h4>{{ $product->name }}</h4>
                        <small>{{ $product->description }}</small>
                        <br>
                        <small style="color: #28a745;">${{ number_format($product->total_amount, 2) }}</small>
                    </div>
                    <div class="popular-stats">
                        <span class="badge">{{ $product->total_quantity }}</span>
                        <br>
                        <small>unidades</small>
                    </div>
                </div>
            @endforeach
        @else
            <p style="text-align: center; color: #666;">No hay productos vendidos para este día</p>
        @endif
    </div>

    <!-- Tabla de ventas por hora -->
    <div class="hourly-table">
        <h2>Ventas por Hora del Día</h2>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Total</th>
                    <th>Cuentas Corrientes</th>
                    <th>Servicios</th>
                    <th>Productos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hourlySales as $hour => $data)
                <tr>
                    <td><strong>{{ $data['label'] }}</strong></td>
                    <td><strong style="color: #007bff;">${{ number_format($data['total'], 2) }}</strong></td>
                    <td>${{ number_format($data['client_accounts'], 2) }}</td>
                    <td>${{ number_format($data['technical_records'], 2) }}</td>
                    <td>${{ number_format($data['product_sales'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema Tiziano</p>
        <p>Módulo de Ventas por Día - Peluquería</p>
    </div>
</body>
</html> 