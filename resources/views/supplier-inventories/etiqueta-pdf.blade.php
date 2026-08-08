<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 0; font-family: Helvetica, Arial, sans-serif; }
        /* Sin alto fijo: si el bloque mide lo mismo que la página, dompdf lo
           desborda y genera una etiqueta en blanco por cada una impresa. */
        .etiqueta {
            width: {{ $ancho }}mm;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        /* dompdf no soporta :last-child, así que el salto se marca con una clase
           solo en las etiquetas que NO son la última (si no, imprime una de más). */
        .salto { page-break-after: always; }

        /* Medidas tomadas del .ddl del software de la 4BARCODE: el código
           arranca a 8.19mm del borde de arriba y mide 10mm de alto.
           El ancho sí difiere: el .ddl usa 19.77mm porque lleva un Code128 de 8
           dígitos, y un EAN-13 (95 módulos) a ese ancho da barras de 0.21mm,
           por debajo del mínimo legible de 0.264mm. A 28mm quedan en 0.29mm. */
        .barcode {
            width: {{ min($ancho - 12, 28) }}mm;
            height: 10mm;
            margin-top: 8.19mm;
        }
        .codigo {
            font-size: 6pt;
            margin: 0.5mm 0 0;
            letter-spacing: 0.3pt;
        }
    </style>
</head>
<body>
    @for ($i = 0; $i < $cantidad; $i++)
        <div class="etiqueta{{ $i < $cantidad - 1 ? ' salto' : '' }}">
            <img class="barcode" src="{{ $barcode }}" alt="{{ $producto->codigo_barra }}">
            <div class="codigo">{{ $producto->codigo_barra }}</div>
        </div>
    @endfor
</body>
</html>
