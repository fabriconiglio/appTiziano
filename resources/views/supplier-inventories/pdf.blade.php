<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventario de Proveedores</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .page-break {
            page-break-before: always;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .first-section {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <!-- Sección 1: Inventario Completo -->
    <div class="section first-section">
        <div class="section-title">Inventario de Proveedores - {{ $exportDate }}</div>
        <table>
            <thead>
                <tr>
                    <th>Nombre del Producto</th>
                    <th>Descripción - Marca</th>
                    <th>Precio por Mayor</th>
                    <th>Precio por Menor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($completeInventory as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['precio_mayor'] }}</td>
                    <td>{{ $item['precio_menor'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Sección 2: Precios por Mayor -->
    <div class="section page-break">
        <div class="section-title">2. Precios por Mayor</div>
        <table>
            <thead>
                <tr>
                    <th>Nombre del Producto</th>
                    <th>Descripción - Marca</th>
                    <th>Precio por Mayor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mayorPrices as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['precio_mayor'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Sección 3: Precios por Menor -->
    <div class="section page-break">
        <div class="section-title">3. Precios por Menor</div>
        <table>
            <thead>
                <tr>
                    <th>Nombre del Producto</th>
                    <th>Descripción - Marca</th>
                    <th>Precio por Menor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($menorPrices as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['precio_menor'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Documento generado automáticamente por App Tiziano</p>
        <p>Total de productos: {{ count($completeInventory) }}</p>
    </div>
</body>
</html> 