<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Etiqueta - {{ $producto->product_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 10px; }
        .barra-acciones { margin-bottom: 12px; }
        .btn {
            display: inline-block; padding: 8px 14px; border-radius: 6px; border: 1px solid #ccc;
            background: #f3f4f6; color: #222; text-decoration: none; cursor: pointer; font-size: 14px;
        }
        .btn-primary { background: #1f2d3d; color: #fff; border-color: #1f2d3d; }
        .hoja { display: flex; flex-wrap: wrap; gap: 8px; }
        .etiqueta {
            width: 5cm; border: 1px dashed #bbb; padding: 6px 8px; text-align: center;
            page-break-inside: avoid;
        }
        .etiqueta .nombre { font-size: 11px; font-weight: bold; margin: 0 0 2px; line-height: 1.1;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .etiqueta .precio { font-size: 12px; margin: 2px 0 0; }
        .etiqueta .codigo { font-size: 10px; letter-spacing: 1px; margin-top: 2px; }
        .etiqueta svg { max-width: 100%; height: auto; }

        @media print {
            .barra-acciones { display: none; }
            body { padding: 0; }
            .etiqueta { border: none; }
        }
    </style>
</head>
<body>
    <div class="barra-acciones">
        <button class="btn btn-primary" onclick="window.print()">🖨 Imprimir</button>
        <a class="btn" href="{{ route('supplier-inventories.show', $producto) }}">Volver</a>
        <span style="margin-left:10px; font-size:13px; color:#666;">
            Imprimiendo {{ $cantidad }} {{ $cantidad == 1 ? 'etiqueta' : 'etiquetas' }}.
            (Cambiá la cantidad agregando <code>?cant=N</code> a la URL.)
        </span>
    </div>

    <div class="hoja">
        @for ($i = 0; $i < $cantidad; $i++)
            <div class="etiqueta">
                <p class="nombre">{{ $producto->product_name }}</p>
                {!! $svg !!}
                <p class="codigo">{{ $producto->codigo_barra }}</p>
                @if($producto->precio_menor)
                    <p class="precio">$ {{ number_format($producto->precio_menor, 2, ',', '.') }}</p>
                @endif
            </div>
        @endfor
    </div>

    <script>
        // Abrir el diálogo de impresión automáticamente.
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
</body>
</html>
