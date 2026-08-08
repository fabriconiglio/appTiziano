<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 0; font-family: Helvetica, Arial, sans-serif; }

        /* Todas las etiquetas van en una sola página, apiladas. Nada de
           page-break: cada salto de página hace que la térmica avance el papel
           hasta el final de su hoja y salgan etiquetas en blanco de más. */
        /* El espacio de arriba va como padding del contenedor, no como margen del
           primer hijo: un margin-top se escapa del bloque, estira el total y
           empuja las últimas etiquetas a una segunda página.
           height + padding suman los {{ $alto }}mm (dompdf usa content-box). */
        .etiqueta {
            width: {{ $ancho }}mm;
            height: {{ $alto - 7.5 }}mm;
            margin: 0;
            padding: 7.5mm 0 0;
            text-align: center;
            overflow: hidden;
        }

        .nombre {
            font-size: 6pt;
            font-weight: bold;
            margin: 0 0 0.8mm;
            padding: 0 2mm;
            white-space: nowrap;
            overflow: hidden;
        }
        /* 25mm es el piso: un EAN-13 son 95 módulos, y más angosto que esto las
           barras bajan de los 0.264mm que necesita un lector para engancharlo. */
        .barcode {
            width: {{ min($ancho - 15, 25) }}mm;
            height: 8mm;
        }
        .codigo {
            font-size: 7pt;
            margin: 0.5mm 0 0;
            letter-spacing: 0.3pt;
        }
    </style>
</head>
<body>
    @for ($i = 0; $i < $cantidad; $i++)
        <div class="etiqueta">
            <div class="nombre">{{ $producto->product_name }}</div>
            <img class="barcode" src="{{ $barcode }}" alt="{{ $producto->codigo_barra }}">
            <div class="codigo">{{ $producto->codigo_barra }}</div>
        </div>
    @endfor
</body>
</html>
