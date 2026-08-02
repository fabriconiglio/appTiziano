<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Etiqueta - {{ $producto->product_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 10px; }
        .barra-acciones { margin-bottom: 12px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .btn {
            display: inline-block; padding: 8px 14px; border-radius: 6px; border: 1px solid #ccc;
            background: #f3f4f6; color: #222; text-decoration: none; cursor: pointer; font-size: 14px;
        }
        .btn-primary { background: #1f2d3d; color: #fff; border-color: #1f2d3d; }
        .cant-box { display: flex; align-items: center; gap: 6px; font-size: 14px; }
        .cant-box input { width: 70px; padding: 6px 8px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        .hoja { display: flex; flex-wrap: wrap; gap: 8px; }
        .etiqueta {
            width: 5cm; border: 1px dashed #bbb; padding: 6px 8px; text-align: center;
            page-break-inside: avoid;
        }
        .etiqueta .nombre { font-size: 11px; font-weight: bold; margin: 0 0 2px; line-height: 1.1;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .etiqueta .codigo { font-size: 10px; letter-spacing: 1px; margin-top: 2px; }
        .etiqueta svg { max-width: 100%; height: auto; }

        .ayuda {
            background: #fff8e1; border: 1px solid #f0c36d; border-radius: 6px;
            padding: 8px 12px; font-size: 13px; color: #6b4f00; margin-bottom: 12px;
            max-width: 640px; line-height: 1.5;
        }
        .ayuda strong { color: #4a3600; }

        /* Tamaño físico real de la etiqueta de la impresora térmica: 6cm x 3cm */
        @page {
            size: 60mm 30mm;
            margin: 0;
        }

        @media print {
            html, body {
                width: 60mm;
                margin: 0;
                padding: 0;
            }
            .barra-acciones, .ayuda { display: none !important; }
            .hoja { display: block; gap: 0; }

            .etiqueta {
                width: 60mm;
                height: 30mm;
                margin: 0;
                padding: 1.5mm;
                border: none;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                overflow: hidden;
                /* Cada etiqueta ocupa una etiqueta física del rollo */
                page-break-after: always;
                break-after: page;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            /* Evita que salga una etiqueta en blanco al final */
            .etiqueta:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            .etiqueta .nombre {
                font-size: 6.5pt;
                line-height: 1.1;
                margin: 0 0 1mm;
                max-width: 100%;
            }
            /* 48mm de ancho deja ~4,5mm de zona muda a cada lado (la necesita el lector) */
            .etiqueta svg {
                width: 48mm;
                height: auto;
                max-height: 15mm;
            }
            .etiqueta .codigo {
                font-size: 6.5pt;
                letter-spacing: 0.5px;
                margin: 1mm 0 0;
            }
        }
    </style>
</head>
<body>
    <div class="barra-acciones">
        <button class="btn btn-primary" onclick="window.print()">🖨 Imprimir</button>
        <a class="btn" href="{{ route('supplier-inventories.show', $producto) }}">Volver</a>
        <span class="cant-box">
            Cantidad de etiquetas:
            <input type="number" id="cantidad" min="1" max="100" value="{{ $cantidad }}">
        </span>
    </div>

    <div class="ayuda">
        <strong>La primera vez que imprimas en esta computadora</strong>, en la ventana de impresión revisá que esté así:<br>
        • <strong>Destino:</strong> la impresora de etiquetas (4BARCODE)<br>
        • <strong>Márgenes:</strong> Ninguno &nbsp;•&nbsp; <strong>Encabezados y pies de página:</strong> destildado<br>
        Chrome lo recuerda para la próxima: después alcanza con apretar Imprimir.
    </div>

    <!-- Plantilla de una etiqueta -->
    <template id="tplEtiqueta">
        <div class="etiqueta">
            <p class="nombre">{{ $producto->product_name }}</p>
            {!! $svg !!}
            <p class="codigo">{{ $producto->codigo_barra }}</p>
        </div>
    </template>

    <div class="hoja" id="hoja"></div>

    <script>
        const tpl = document.getElementById('tplEtiqueta');
        const hoja = document.getElementById('hoja');
        const inputCant = document.getElementById('cantidad');

        function render() {
            let n = parseInt(inputCant.value, 10);
            if (isNaN(n) || n < 1) n = 1;
            if (n > 100) n = 100;
            hoja.innerHTML = '';
            for (let i = 0; i < n; i++) {
                hoja.appendChild(tpl.content.cloneNode(true));
            }
        }

        inputCant.addEventListener('input', render);
        render();
    </script>
</body>
</html>
