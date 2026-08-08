<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Etiqueta - {{ $producto->product_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 16px; }
        .barra-acciones { margin-bottom: 14px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .btn {
            display: inline-block; padding: 9px 16px; border-radius: 6px; border: 1px solid #ccc;
            background: #f3f4f6; color: #222; text-decoration: none; cursor: pointer; font-size: 14px;
        }
        .btn-primary { background: #1f2d3d; color: #fff; border-color: #1f2d3d; font-weight: bold; }
        .cant-box { display: flex; align-items: center; gap: 6px; font-size: 14px; }
        .cant-box input { width: 70px; padding: 7px 8px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }

        .titulo { font-size: 15px; margin: 0 0 12px; color: #444; }
        .titulo strong { color: #111; }

        /* Vista previa de una etiqueta, con las mismas medidas que el PDF. */
        .etiqueta {
            width: {{ $ancho }}mm; height: {{ $alto }}mm;
            border: 1px dashed #bbb; text-align: center; overflow: hidden;
        }
        .etiqueta .nombre { font-size: 6pt; font-weight: bold; margin: 7.5mm 0 0.8mm; padding: 0 2mm;
            white-space: nowrap; overflow: hidden; }
        .etiqueta svg { width: {{ min($ancho - 15, 25) }}mm; height: 8mm; }
        .etiqueta .codigo { font-size: 7pt; letter-spacing: 0.3pt; margin: 0.5mm 0 0; }
    </style>
</head>
<body>
    <p class="titulo"><strong>{{ $producto->product_name }}</strong> — código {{ $producto->codigo_barra }}</p>

    <div class="barra-acciones">
        <a class="btn btn-primary" id="btnPdf" href="#" target="_blank">🖨 Imprimir etiqueta</a>
        <span class="cant-box">
            Cantidad:
            <input type="number" id="cantidad" min="1" max="100" value="{{ $cantidad }}">
        </span>
        <a class="btn" href="{{ route('supplier-inventories.show', $producto) }}">Volver</a>
    </div>

    <div class="etiqueta">
        <div class="nombre">{{ $producto->product_name }}</div>
        {!! $svg !!}
        <div class="codigo">{{ $producto->codigo_barra }}</div>
    </div>

    <script>
        const inputCant = document.getElementById('cantidad');
        const btnPdf = document.getElementById('btnPdf');
        const baseUrl = @json(route('supplier-inventories.etiqueta', $producto));
        const anchoActual = @json($ancho);
        const altoActual = @json($alto);

        function actualizar() {
            let n = parseInt(inputCant.value, 10);
            if (isNaN(n) || n < 1) n = 1;
            if (n > 100) n = 100;
            btnPdf.href = `${baseUrl}?formato=pdf&cant=${n}&ancho=${anchoActual}&alto=${altoActual}`;
        }

        inputCant.addEventListener('input', actualizar);
        actualizar();
    </script>
</body>
</html>
