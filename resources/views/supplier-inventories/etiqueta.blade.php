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
            width: {{ $ancho }}mm; height: {{ $alto }}mm;
            border: 1px dashed #bbb; padding: 1.5mm; text-align: center;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            overflow: hidden;
        }
        .etiqueta .nombre { font-size: 7pt; font-weight: bold; margin: 0 0 1mm; line-height: 1.1;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
        .etiqueta .codigo { font-size: 7pt; letter-spacing: 0.5px; margin: 1mm 0 0; }
        .etiqueta svg { width: {{ $ancho - 12 }}mm; height: auto; max-height: {{ $alto / 2 }}mm; }

        .ayuda {
            background: #fff8e1; border: 1px solid #f0c36d; border-radius: 6px;
            padding: 10px 12px; font-size: 13px; color: #6b4f00; margin-bottom: 12px;
            max-width: 720px; line-height: 1.6;
        }
        .ayuda strong { color: #4a3600; }
        .ayuda code { background: #fff; padding: 1px 5px; border-radius: 3px; border: 1px solid #e5d5a8; }
        .calib { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
        .calib a {
            font-size: 12px; padding: 5px 9px; border-radius: 5px; text-decoration: none;
            border: 1px solid #c9a227; background: #fff; color: #6b4f00;
        }
        .calib a.actual { background: #6b4f00; color: #fff; border-color: #6b4f00; }

        /* Tamaño físico de la etiqueta (configurable con ?ancho= y ?alto=) */
        @page {
            size: {{ $ancho }}mm {{ $alto }}mm;
            margin: 0;
        }

        @media print {
            html, body {
                width: {{ $ancho }}mm;
                margin: 0;
                padding: 0;
            }
            .barra-acciones, .ayuda { display: none !important; }
            .hoja { display: block; gap: 0; }

            .etiqueta {
                width: {{ $ancho }}mm;
                height: {{ $alto }}mm;
                margin: 0;
                border: none;
                /* Cada etiqueta ocupa una etiqueta física del rollo */
                page-break-after: always;
                break-after: page;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .etiqueta:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            @if($rotar)
            /* El driver tiene el papel al revés: giramos el contenido 90° */
            .etiqueta {
                width: {{ $alto }}mm;
                height: {{ $ancho }}mm;
            }
            .etiqueta > * {
                transform: rotate(-90deg);
                transform-origin: center;
            }
            .etiqueta {
                flex-direction: row;
            }
            @endif
        }
    </style>
</head>
<body>
    <div class="barra-acciones">
        <a class="btn btn-primary" id="btnPdf" href="#" target="_blank">📄 Descargar PDF para imprimir</a>
        <button class="btn" onclick="window.print()">🖨 Imprimir desde el navegador</button>
        <a class="btn" href="{{ route('supplier-inventories.show', $producto) }}">Volver</a>
        <span class="cant-box">
            Cantidad de etiquetas:
            <input type="number" id="cantidad" min="1" max="100" value="{{ $cantidad }}">
        </span>
    </div>

    <div class="ayuda">
        <strong>Usá el botón "Descargar PDF para imprimir"</strong> (el oscuro). El PDF ya viene con la medida
        exacta de la etiqueta, así que la impresora no la deforma. Se abre en otra pestaña: ahí apretás el
        ícono de imprimir, elegís la impresora <strong>4BARCODE</strong>, ponés
        <strong>Escala: Tamaño real</strong> (o 100%) y listo.

        <div style="margin-top:8px">
            Está configurado en <code>{{ $ancho }}mm x {{ $alto }}mm</code>, que es la medida del rollo
            que usa la 4BARCODE. <strong>Si algún día comprás etiquetas de otro tamaño</strong>, elegilo acá:
        </div>
        <div class="calib">
            @php
                // 40x30 es la medida real del rollo (sale del .ddl del software de
                // la 4BARCODE). Las demás quedan por si algún día cambia el rollo.
                $opciones = [
                    ['ancho' => 40, 'alto' => 30, 'rotar' => 0, 'txt' => '40 x 30 ✔'],
                    ['ancho' => 50, 'alto' => 30, 'rotar' => 0, 'txt' => '50 x 30'],
                    ['ancho' => 60, 'alto' => 30, 'rotar' => 0, 'txt' => '60 x 30'],
                    ['ancho' => 50, 'alto' => 25, 'rotar' => 0, 'txt' => '50 x 25'],
                    ['ancho' => 40, 'alto' => 25, 'rotar' => 0, 'txt' => '40 x 25'],
                    ['ancho' => 30, 'alto' => 20, 'rotar' => 0, 'txt' => '30 x 20'],
                ];
            @endphp
            @foreach($opciones as $o)
                @php
                    $esActual = (int) $ancho === $o['ancho'] && (int) $alto === $o['alto'] && (int) $rotar === $o['rotar'];
                @endphp
                <a class="{{ $esActual ? 'actual' : '' }}"
                   href="{{ route('supplier-inventories.etiqueta', $producto) }}?cant={{ $cantidad }}&ancho={{ $o['ancho'] }}&alto={{ $o['alto'] }}&rotar={{ $o['rotar'] }}">
                    {{ $o['txt'] }}
                </a>
            @endforeach
        </div>
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

        const btnPdf = document.getElementById('btnPdf');
        const baseUrl = @json(route('supplier-inventories.etiqueta', $producto));
        const anchoActual = @json($ancho);
        const altoActual = @json($alto);

        function render() {
            let n = parseInt(inputCant.value, 10);
            if (isNaN(n) || n < 1) n = 1;
            if (n > 100) n = 100;
            hoja.innerHTML = '';
            for (let i = 0; i < n; i++) {
                hoja.appendChild(tpl.content.cloneNode(true));
            }
            // El PDF respeta la cantidad y la medida que estén elegidas.
            btnPdf.href = `${baseUrl}?formato=pdf&cant=${n}&ancho=${anchoActual}&alto=${altoActual}`;
        }

        inputCant.addEventListener('input', render);
        render();
    </script>
</body>
</html>
