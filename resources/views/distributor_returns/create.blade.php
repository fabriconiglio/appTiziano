@extends('layouts.app')

@section('title', 'Nueva Devolución')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Nueva Devolución</h1>
        <a href="{{ route('distributor-returns.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('distributor-returns.store') }}" id="formDevolucion">
        @csrf

        {{-- Paso 1: cliente --}}
        <div class="card mb-3">
            <div class="card-header bg-light"><strong>1. Cliente</strong></div>
            <div class="card-body">
                {{-- Buscador escribible. No usa Choices.js a propósito: la app carga
                     la v10 por CDN y el bundle trae la v11 con su CSS, y esa mezcla
                     rompe el desplegable (ya pasó en el modal de la agenda). --}}
                <div class="position-relative">
                    <input type="text" id="clienteBuscar" class="form-control" autocomplete="off"
                           placeholder="Escribí el nombre del cliente...">
                    <input type="hidden" id="cliente">

                    <div id="clienteOpciones" class="list-group position-absolute w-100 shadow d-none"
                         style="z-index:1000; max-height:280px; overflow-y:auto;">
                        @foreach($clientes as $cliente)
                            <button type="button" class="list-group-item list-group-item-action cliente-opcion"
                                    data-id="{{ $cliente->id }}"
                                    data-nombre="{{ trim($cliente->name . ' ' . $cliente->surname) }}">
                                {{ trim($cliente->name . ' ' . $cliente->surname) }}
                            </button>
                        @endforeach
                    </div>
                    <div class="form-text d-none" id="clienteElegido"></div>
                </div>
            </div>
        </div>

        {{-- Paso 2: compra --}}
        <div class="card mb-3 d-none" id="cardCompras">
            <div class="card-header bg-light"><strong>2. ¿De qué compra?</strong></div>
            <div class="card-body">
                <div id="listaCompras"></div>
                <input type="hidden" name="distributor_technical_record_id" id="compraId">

                <div class="alert alert-warning mt-3 d-none" id="avisoPlazo">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="autorizar_fuera_plazo"
                               value="1" id="autorizarPlazo">
                        <label class="form-check-label" for="autorizarPlazo">
                            <strong>Autorizar fuera de plazo.</strong>
                            <span id="textoPlazo"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Paso 3: productos --}}
        <div class="card mb-3 d-none" id="cardProductos">
            <div class="card-header bg-light"><strong>3. ¿Qué productos vuelven?</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px"></th>
                                <th>Producto</th>
                                <th class="text-center">Compró</th>
                                <th class="text-center" style="width:130px">Devuelve</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="listaProductos"></tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="5" class="text-end">Total a devolver</th>
                                <th class="text-end fs-5" id="totalDevolucion">$0,00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paso 4: datos finales --}}
        <div class="card mb-3 d-none" id="cardDatos">
            <div class="card-header bg-light"><strong>4. Datos de la devolución</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="return_date" class="form-control"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Destino del importe</label>
                        <input type="hidden" name="destino" value="cuenta_corriente">
                        <div class="form-control-plaintext">
                            <span class="badge bg-info text-dark">Cuenta corriente</span>
                            <small class="text-muted ms-2">
                                Le baja la deuda. Si devuelve más de lo que debía, queda a su favor.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Motivo <small class="text-muted">(opcional)</small></label>
                        <input type="text" name="motivo" class="form-control"
                               placeholder="Fallado, equivocado, no le gustó...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Observaciones <small class="text-muted">(opcional)</small></label>
                        <input type="text" name="observations" class="form-control">
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle"></i>
                    Al confirmar, el stock vuelve al inventario y el importe se descuenta de la cuenta del cliente.
                    <strong>Una compra admite una sola devolución.</strong>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5 d-none" id="acciones">
            <a href="{{ route('distributor-returns.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="btnGuardar">
                <i class="fas fa-check"></i> Confirmar devolución
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const DIAS_PLAZO = @json($diasPlazo);
const urlCompras = @json(route('distributor-returns.compras', ['distributorClient' => 'CLIENTE']));
const urlProductos = @json(route('distributor-returns.productos', ['distributorTechnicalRecord' => 'COMPRA']));

const $cliente = document.getElementById('cliente');
const $clienteBuscar = document.getElementById('clienteBuscar');
const $clienteOpciones = document.getElementById('clienteOpciones');
const $clienteElegido = document.getElementById('clienteElegido');
const $cardCompras = document.getElementById('cardCompras');
const $listaCompras = document.getElementById('listaCompras');
const $compraId = document.getElementById('compraId');
const $avisoPlazo = document.getElementById('avisoPlazo');
const $textoPlazo = document.getElementById('textoPlazo');
const $autorizarPlazo = document.getElementById('autorizarPlazo');
const $cardProductos = document.getElementById('cardProductos');
const $listaProductos = document.getElementById('listaProductos');
const $cardDatos = document.getElementById('cardDatos');
const $acciones = document.getElementById('acciones');
const $total = document.getElementById('totalDevolucion');

const money = n => '$' + n.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

function ocultarDesdeCompras() {
    $cardProductos.classList.add('d-none');
    $cardDatos.classList.add('d-none');
    $acciones.classList.add('d-none');
    $avisoPlazo.classList.add('d-none');
    $autorizarPlazo.checked = false;
    $compraId.value = '';
    $listaProductos.innerHTML = '';
}

// Filtrar la lista mientras escribe.
$clienteBuscar.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    let visibles = 0;

    document.querySelectorAll('.cliente-opcion').forEach(op => {
        const coincide = !q || op.dataset.nombre.toLowerCase().includes(q);
        op.classList.toggle('d-none', !coincide);
        if (coincide) visibles++;
    });

    $clienteOpciones.classList.toggle('d-none', visibles === 0);
});

$clienteBuscar.addEventListener('focus', function () {
    document.querySelectorAll('.cliente-opcion').forEach(op => op.classList.remove('d-none'));
    $clienteOpciones.classList.remove('d-none');
});

// Cerrar al hacer clic afuera, pero no cuando el clic es en una opción.
document.addEventListener('click', function (e) {
    if (!e.target.closest('#clienteBuscar') && !e.target.closest('#clienteOpciones')) {
        $clienteOpciones.classList.add('d-none');
    }
});

document.querySelectorAll('.cliente-opcion').forEach(op => {
    op.addEventListener('click', function () {
        $cliente.value = this.dataset.id;
        $clienteBuscar.value = this.dataset.nombre;
        $clienteOpciones.classList.add('d-none');
        $clienteElegido.textContent = 'Cliente elegido: ' + this.dataset.nombre;
        $clienteElegido.classList.remove('d-none');
        cargarCompras(this.dataset.id);
    });
});

async function cargarCompras(clienteId) {
    ocultarDesdeCompras();
    $listaCompras.innerHTML = '';
    if (!clienteId) { $cardCompras.classList.add('d-none'); return; }

    $cardCompras.classList.remove('d-none');
    $listaCompras.innerHTML = '<div class="text-muted">Buscando compras...</div>';

    const res = await fetch(urlCompras.replace('CLIENTE', clienteId));
    const compras = await res.json();

    if (!compras.length) {
        $listaCompras.innerHTML = '<div class="text-muted">Este cliente no tiene compras registradas en los últimos 90 días.</div>';
        return;
    }

    $listaCompras.innerHTML = compras.map(c => {
        // Una compra ya devuelta no se puede volver a elegir: se admite una sola.
        const deshabilitada = c.ya_devuelta;
        let nota = '';
        if (c.ya_devuelta)        nota = `<span class="badge bg-secondary ms-2">Ya devuelta (${c.devolucion})</span>`;
        else if (c.fuera_de_plazo) nota = `<span class="badge bg-warning text-dark ms-2">Hace ${c.dias} días</span>`;

        return `
        <div class="form-check border-bottom py-2 ${deshabilitada ? 'opacity-50' : ''}">
            <input class="form-check-input compra-radio" type="radio" name="compra_sel"
                   id="compra${c.id}" value="${c.id}"
                   data-dias="${c.dias}" data-fuera="${c.fuera_de_plazo ? 1 : 0}"
                   ${deshabilitada ? 'disabled' : ''}>
            <label class="form-check-label w-100" for="compra${c.id}">
                <strong>${c.fecha}</strong>
                <span class="ms-3">${money(c.total)}</span>
                <span class="text-muted ms-3">${c.cantidad_productos} producto(s)</span>
                ${nota}
            </label>
        </div>`;
    }).join('');

    document.querySelectorAll('.compra-radio').forEach(r => r.addEventListener('change', elegirCompra));
}

async function elegirCompra() {
    ocultarDesdeCompras();
    $compraId.value = this.value;

    if (this.dataset.fuera === '1') {
        $textoPlazo.textContent = `La compra es de hace ${this.dataset.dias} días y el plazo es de ${DIAS_PLAZO}.`;
        $avisoPlazo.classList.remove('d-none');
    }

    const res = await fetch(urlProductos.replace('COMPRA', this.value));
    if (!res.ok) {
        const err = await res.json();
        $listaProductos.innerHTML = '';
        alert(err.error || 'No se pudieron cargar los productos de esa compra.');
        return;
    }

    const productos = await res.json();
    $listaProductos.innerHTML = productos.map((p, i) => `
        <tr>
            <td class="text-center">
                <input class="form-check-input chk-prod" type="checkbox" data-i="${i}">
            </td>
            <td>
                ${p.nombre}
                <input type="hidden" name="productos[${i}][product_id]" value="${p.product_id}" disabled>
            </td>
            <td class="text-center">${p.cantidad_comprada}</td>
            <td class="text-center">
                <input type="number" class="form-control form-control-sm text-center cant-prod"
                       name="productos[${i}][cantidad]" value="${p.cantidad_comprada}"
                       min="1" max="${p.cantidad_comprada}" data-i="${i}"
                       data-precio="${p.precio_unitario}" disabled>
            </td>
            <td class="text-end">${money(p.precio_unitario)}</td>
            <td class="text-end subtotal" data-i="${i}">$0,00</td>
        </tr>`).join('');

    document.querySelectorAll('.chk-prod').forEach(c => c.addEventListener('change', tildarProducto));
    document.querySelectorAll('.cant-prod').forEach(c => c.addEventListener('input', recalcular));

    $cardProductos.classList.remove('d-none');
    $cardDatos.classList.remove('d-none');
    $acciones.classList.remove('d-none');
    recalcular();
}

function tildarProducto() {
    const i = this.dataset.i;
    // Los campos deshabilitados no se mandan, así que tildar es lo que decide
    // qué productos entran en la devolución.
    document.querySelectorAll(`[name^="productos[${i}]"]`).forEach(el => el.disabled = !this.checked);
    recalcular();
}

function recalcular() {
    let total = 0;
    document.querySelectorAll('.cant-prod').forEach(input => {
        const i = input.dataset.i;
        const celda = document.querySelector(`.subtotal[data-i="${i}"]`);
        if (input.disabled) { celda.textContent = money(0); return; }

        const sub = (parseInt(input.value, 10) || 0) * parseFloat(input.dataset.precio);
        celda.textContent = money(sub);
        total += sub;
    });
    $total.textContent = money(total);
    document.getElementById('btnGuardar').disabled = total <= 0;
}
</script>
@endpush
@endsection
