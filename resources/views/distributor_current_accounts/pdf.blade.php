<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Corriente - {{ $distributorClient->full_name }}</title>
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
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        .client-info {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .client-info h3 {
            margin: 0 0 15px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .info-value {
            flex: 1;
        }
        .balance-summary {
            margin-bottom: 30px;
            padding: 15px;
            border: 2px solid #333;
            border-radius: 5px;
            text-align: center;
        }
        .balance-amount {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .balance-status {
            font-size: 16px;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }
        .status-debt {
            background-color: #dc3545;
            color: white;
        }
        .status-credit {
            background-color: #28a745;
            color: white;
        }
        .status-balanced {
            background-color: #6c757d;
            color: white;
        }
        .totals-section {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .totals-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .totals-grid {
            display: flex;
            justify-content: space-around;
        }
        .total-item {
            text-align: center;
        }
        .total-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        .total-value {
            font-size: 18px;
            font-weight: bold;
        }
        .movements-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .movements-table th,
        .movements-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .movements-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .movements-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .type-debt {
            color: #dc3545;
            font-weight: bold;
        }
        .type-payment {
            color: #28a745;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
        .observations {
            font-style: italic;
            color: #666;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">TIZIANO</div>
        <div class="document-title">CUENTA CORRIENTE</div>
        <div class="subtitle">Distribuidora</div>
    </div>

    <div class="client-info">
        <h3>Información del Distribuidor</h3>
        <div class="info-row">
            <div class="info-label">Nombre:</div>
            <div class="info-value">{{ $distributorClient->full_name }}</div>
        </div>
        @if($distributorClient->email)
        <div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value">{{ $distributorClient->email }}</div>
        </div>
        @endif
        @if($distributorClient->phone)
        <div class="info-row">
            <div class="info-label">Teléfono:</div>
            <div class="info-value">{{ $distributorClient->phone }}</div>
        </div>
        @endif
        @if($distributorClient->dni)
        <div class="info-row">
            <div class="info-label">DNI:</div>
            <div class="info-value">{{ $distributorClient->dni }}</div>
        </div>
        @endif
        @if($distributorClient->domicilio)
        <div class="info-row">
            <div class="info-label">Domicilio:</div>
            <div class="info-value">{{ $distributorClient->domicilio }}</div>
        </div>
        @endif
    </div>

    <div class="balance-summary">
        <h3>Estado Actual de la Cuenta</h3>
        <div class="balance-amount">${{ $formattedBalance }}</div>
        @if($currentBalance > 0)
            <div class="balance-status status-debt">CON DEUDA</div>
        @elseif($currentBalance < 0)
            <div class="balance-status status-credit">A FAVOR</div>
        @else
            <div class="balance-status status-balanced">AL DÍA</div>
        @endif
    </div>

    <div class="totals-section">
        <h3>Resumen de Totales</h3>
        <div class="totals-grid">
            <div class="total-item">
                <div class="total-label">Total Deudas</div>
                <div class="total-value type-debt">${{ number_format($totalDebts, 2, ',', '.') }}</div>
            </div>
            <div class="total-item">
                <div class="total-label">Total Pagos</div>
                <div class="total-value type-payment">${{ number_format($totalPayments, 2, ',', '.') }}</div>
            </div>
            <div class="total-item">
                <div class="total-label">Saldo Actual</div>
                <div class="total-value {{ $currentBalance > 0 ? 'type-debt' : ($currentBalance < 0 ? 'type-payment' : '') }}">
                    ${{ $formattedBalance }}
                </div>
            </div>
        </div>
    </div>

    <div class="movements-section">
        <h3>Detalle de Movimientos</h3>
        @if($currentAccounts->count() > 0)
            <table class="movements-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Referencia</th>
                        <th>Monto</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currentAccounts as $account)
                        <tr>
                            <td>{{ $account->date->format('d/m/Y') }}</td>
                            <td class="text-center">
                                @if($account->type === 'debt')
                                    <span class="type-debt">DEUDA</span>
                                @else
                                    <span class="type-payment">PAGO</span>
                                @endif
                            </td>
                            <td>
                                {{ $account->description }}
                                @if($account->observations)
                                    <br><span class="observations">{{ $account->observations }}</span>
                                @endif
                            </td>
                            <td>{{ $account->reference ?? '-' }}</td>
                            <td class="text-center {{ $account->type === 'debt' ? 'type-debt' : 'type-payment' }}">
                                {{ $account->type === 'debt' ? '-' : '+' }}${{ number_format($account->amount, 2, ',', '.') }}
                            </td>
                            <td>{{ $account->user->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #666; font-style: italic;">
                No hay movimientos registrados en la cuenta corriente.
            </p>
        @endif
    </div>

    <div class="footer">
        <p><strong>Documento generado el:</strong> {{ $generatedAt }}</p>
        <p><strong>TIZIANO</strong> - Sistema de Gestión de Distribuidores</p>
        <p>Este documento es un extracto oficial de la cuenta corriente del distribuidor.</p>
    </div>
</body>
</html> 