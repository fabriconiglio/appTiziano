<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas Diarias - {{ $today->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2d3748;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #718096;
        }
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .card {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin: 5px;
            min-width: 150px;
            text-align: center;
        }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #4a5568;
        }
        .card .amount {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
        }
        .card .count {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }
        .comparison-section {
            margin-bottom: 30px;
        }
        .comparison-section h2 {
            color: #2d3748;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .comparison-grid {
            display: flex;
            justify-content: space-between;
        }
        .comparison-item {
            flex: 1;
            margin: 0 10px;
        }
        .comparison-item h3 {
            color: #4a5568;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .stat-label {
            color: #718096;
        }
        .stat-value {
            font-weight: bold;
            color: #2d3748;
        }
        .positive {
            color: #38a169;
        }
        .negative {
            color: #e53e3e;
        }
        .hourly-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .hourly-table th,
        .hourly-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        .hourly-table th {
            background: #f7fafc;
            font-weight: bold;
            color: #4a5568;
        }
        .hourly-table td {
            color: #2d3748;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #718096;
            font-size: 12px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas Diarias</h1>
        <p>Fecha: {{ $today->format('d/m/Y') }}</p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Resumen del día -->
    <div class="summary-cards">
        <div class="card">
            <h3>Total del Día</h3>
            <div class="amount">${{ number_format($todaySales['total'], 2) }}</div>
        </div>
        <div class="card">
            <h3>Presupuestos</h3>
            <div class="amount">${{ number_format($todaySales['quotations'], 2) }}</div>
            <div class="count">{{ $todaySales['count_quotations'] }} ventas</div>
        </div>
        <div class="card">
            <h3>Fichas Técnicas</h3>
            <div class="amount">${{ number_format($todaySales['technical_records'], 2) }}</div>
            <div class="count">{{ $todaySales['count_technical_records'] }} ventas</div>
        </div>
        <div class="card">
            <h3>Cuentas Corrientes</h3>
            <div class="amount">${{ number_format($todaySales['client_accounts'] + $todaySales['distributor_accounts'], 2) }}</div>
            <div class="count">{{ $todaySales['count_client_accounts'] + $todaySales['count_distributor_accounts'] }} ventas</div>
        </div>
    </div>

    <!-- Comparaciones -->
    <div class="comparison-section">
        <h2>Análisis Comparativo</h2>
        <div class="comparison-grid">
            <div class="comparison-item">
                <h3>Comparación con Ayer</h3>
                <div class="stat-row">
                    <span class="stat-label">Hoy:</span>
                    <span class="stat-value">${{ number_format($todaySales['total'], 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Ayer:</span>
                    <span class="stat-value">${{ number_format($yesterdaySales['total'], 2) }}</span>
                </div>
                @php
                    $difference = $todaySales['total'] - $yesterdaySales['total'];
                    $percentage = $yesterdaySales['total'] > 0 ? ($difference / $yesterdaySales['total']) * 100 : 0;
                @endphp
                <div class="stat-row">
                    <span class="stat-label">Diferencia:</span>
                    <span class="stat-value {{ $difference >= 0 ? 'positive' : 'negative' }}">
                        {{ $difference >= 0 ? '+' : '' }}${{ number_format($difference, 2) }}
                        ({{ $difference >= 0 ? '+' : '' }}{{ number_format($percentage, 1) }}%)
                    </span>
                </div>
            </div>
            <div class="comparison-item">
                <h3>Estadísticas del Mes</h3>
                <div class="stat-row">
                    <span class="stat-label">Total del mes:</span>
                    <span class="stat-value">${{ number_format($monthlyStats['total'], 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Promedio diario:</span>
                    <span class="stat-value">${{ number_format($monthlyStats['total'] / max(1, $today->day), 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Proyección mensual:</span>
                    <span class="stat-value">${{ number_format(($monthlyStats['total'] / max(1, $today->day)) * $today->daysInMonth, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de ventas por hora -->
    <div class="comparison-section">
        <h2>Ventas por Hora del Día</h2>
        <table class="hourly-table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Total</th>
                    <th>Presupuestos</th>
                    <th>Fichas Técnicas</th>
                    <th>Cuentas Corrientes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hourlySales as $hour => $data)
                <tr>
                    <td>{{ $data['label'] }}</td>
                    <td><strong>${{ number_format($data['total'], 2) }}</strong></td>
                    <td>${{ number_format($data['quotations'], 2) }}</td>
                    <td>${{ number_format($data['technical_records'], 2) }}</td>
                    <td>${{ number_format($data['client_accounts'] + $data['distributor_accounts'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Este reporte se genera automáticamente y se resetea cada día a las 00:00</p>
        <p>Sistema de Gestión Tiziano - Todos los derechos reservados</p>
    </div>
</body>
</html> 