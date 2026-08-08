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
        /* 6pt es el tamaño que usa el software de la 4BARCODE en su plantilla:
           en 40mm de ancho, 7pt ya corta los nombres largos. */
        .nombre {
            font-size: 6pt;
            font-weight: bold;
            margin: 1.5mm 0 0.5mm;
            padding: 0 1.5mm;
            white-space: nowrap;
            overflow: hidden;
        }
        /* 3mm de quiet zone a cada lado: sin margen blanco el lector no engancha. */
        .barcode {
            width: {{ $ancho - 6 }}mm;
            height: {{ $alto * 0.45 }}mm;
        }
        .codigo {
            font-size: 6pt;
            margin: 0.5mm 0 0;
            letter-spacing: 0.5pt;
        }
    </style>
</head>
<body>
    @for ($i = 0; $i < $cantidad; $i++)
        <div class="etiqueta{{ $i < $cantidad - 1 ? ' salto' : '' }}">
            <div class="nombre">{{ $producto->product_name }}</div>
            <img class="barcode" src="{{ $barcode }}" alt="{{ $producto->codigo_barra }}">
            <div class="codigo">{{ $producto->codigo_barra }}</div>
        </div>
    @endfor
</body>
</html>
