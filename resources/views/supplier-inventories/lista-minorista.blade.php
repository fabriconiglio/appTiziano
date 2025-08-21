<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Precios por Menor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            color: #333;
        }
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p><strong>Fecha de Exportación:</strong> {{ $exportDate }}</p>
        <p><strong>Total de Productos:</strong> {{ count($products) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Nombre del Producto</th>
                <th style="width: 35%;">Descripción - Marca</th>
                <th style="width: 15%;">Precio por Menor</th>
                <th style="width: 10%;">Stock</th>
                <th style="width: 15%;">Categoría</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td><strong>{{ $product['name'] }}</strong></td>
                <td>{{ $product['description'] }}</td>
                <td class="text-right">{{ $product['precio_menor'] }}</td>
                <td class="text-center">{{ $product['stock'] }}</td>
                <td>{{ $product['category'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado automáticamente por el sistema de inventario</p>
        <p>© {{ date('Y') }} Tiziano - Todos los derechos reservados</p>
    </div>
</body>
</html> 