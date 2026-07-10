<!-- resources/views/agenda/index.blade.php -->
@extends('layouts.app')

@section('title', 'Agenda')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
    <style>
        .choices { margin-bottom: 0; }
        #calendario { background: #fff; padding: 1rem; border-radius: .5rem; }
        .agenda-leyenda span { display: inline-flex; align-items: center; margin-right: 1rem; }
        .agenda-leyenda i { width: 14px; height: 14px; border-radius: 3px; display: inline-block; margin-right: .35rem; }
        .fc-event { cursor: pointer; }
        .color-swatch {
            width: 26px; height: 26px; border-radius: 50%;
            border: 2px solid transparent; cursor: pointer; padding: 0;
        }
        .color-swatch.selected { border-color: #1f2d3d; box-shadow: inset 0 0 0 2px #fff; }
        .color-auto {
            background: #fff; border: 1px dashed #999 !important;
            font-size: 11px; color: #666; line-height: 1;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0"><i class="fas fa-calendar-alt me-2"></i>Agenda</h1>
            <div class="d-flex gap-2">
                <a href="#" id="btnExportarPdf" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF del día
                </a>
            </div>
        </div>

        @if($sinAsignar > 0)
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Hay <strong>{{ $sinAsignar }}</strong> {{ $sinAsignar == 1 ? 'turno creado' : 'turnos creados' }} desde Google Calendar sin cliente asignado
                    (en naranja). Hacé click en cada uno para asignarle el cliente.
                </div>
            </div>
        @endif

        <div class="row">
            <!-- Calendario -->
            <div class="col-12 mb-3">
                <div id="calendario"></div>
            </div>
        </div>
    </div>

    <!-- Modal de turno -->
    <div class="modal fade" id="modalTurno" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTurno">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTurnoTitulo">Nuevo turno</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="turnoId">
                        <div id="modalAlert" class="alert alert-danger d-none"></div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-1">Cliente</label>
                                <a href="#" id="toggleNuevoCliente" class="small text-decoration-none">
                                    <i class="fas fa-user-plus"></i> Crear cliente
                                </a>
                            </div>
                            <select id="clientId" class="form-select" required>
                                <option value="">Buscar cliente...</option>
                            </select>

                            <!-- Alta rápida de cliente -->
                            <div id="nuevoClienteBox" class="border rounded p-2 mt-2 bg-light d-none">
                                <div id="nuevoClienteAlert" class="alert alert-danger py-1 px-2 small d-none"></div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" id="ncNombre" class="form-control form-control-sm" placeholder="Nombre">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" id="ncApellido" class="form-control form-control-sm" placeholder="Apellido">
                                    </div>
                                    <div class="col-12">
                                        <input type="text" id="ncTelefono" class="form-control form-control-sm" placeholder="Teléfono (para WhatsApp)">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" id="ncCancelar" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                                    <button type="button" id="ncGuardar" class="btn btn-sm btn-success">Crear y seleccionar</button>
                                </div>
                            </div>

                            <!-- Teléfono del cliente elegido: editable por si está mal o cambió -->
                            <div class="mt-2">
                                <label class="form-label small text-muted mb-1">Teléfono del cliente</label>
                                <input type="text" id="telefonoCliente" class="form-control form-control-sm" placeholder="Sin teléfono cargado">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Servicio</label>
                            <select id="servicioId" class="form-select">
                                <option value="">Sin especificar</option>
                                @foreach($servicios as $s)
                                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="form-label">Fecha y hora</label>
                                <input type="datetime-local" id="iniciaEn" class="form-control" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Estado</label>
                                <select id="estado" class="form-select">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-block">Color</label>
                            <input type="hidden" id="turnoColor">
                            <div id="paletaColores" class="d-flex flex-wrap gap-2 align-items-center">
                                <button type="button" class="color-swatch color-auto selected" data-color="" title="Automático">A</button>
                                @foreach($coloresGoogle as $c)
                                    <button type="button" class="color-swatch" data-color="{{ $c['hex'] }}"
                                            style="background: {{ $c['hex'] }}" title="{{ $c['nombre'] }}"></button>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea id="notas" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="btnEliminarTurno" class="btn btn-outline-danger me-auto d-none">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/es.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const urls = {
                eventos: @json(route('agenda.eventos')),
                store: @json(route('turnos.store')),
                exportar: @json(route('agenda.exportar-pdf')),
                base: @json(url('turnos')),
                clienteBuscar: @json(route('clients.buscar')),
                clienteRapido: @json(route('clients.quick-store')),
            };

            const modalEl = document.getElementById('modalTurno');
            const form = document.getElementById('formTurno');

            // Control de modal autónomo (no depende del global window.bootstrap).
            const modal = {
                show() {
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                    modalEl.removeAttribute('aria-hidden');
                    document.body.classList.add('modal-open');
                    const bd = document.createElement('div');
                    bd.className = 'modal-backdrop fade show';
                    bd.dataset.agendaBackdrop = '1';
                    document.body.appendChild(bd);
                },
                hide() {
                    modalEl.classList.remove('show');
                    modalEl.style.display = 'none';
                    modalEl.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('modal-open');
                    document.querySelectorAll('[data-agenda-backdrop]').forEach(b => b.remove());
                },
            };
            modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (btn) {
                btn.addEventListener('click', () => modal.hide());
            });
            const alertBox = document.getElementById('modalAlert');
            let calendar;

            // Select de cliente con búsqueda en el servidor (Choices.js + AJAX).
            const clienteSelect = document.getElementById('clientId');
            const clienteChoices = new Choices(clienteSelect, {
                searchEnabled: true,
                searchResultLimit: 20,
                searchChoices: false,        // no filtra local: usamos el endpoint
                shouldSort: false,
                itemSelectText: '',
                placeholder: true,
                noChoicesText: 'Escribí para buscar un cliente',
                searchPlaceholderValue: 'Buscar por nombre, teléfono o DNI...',
            });

            let buscarTimeout = null;
            clienteSelect.addEventListener('search', function (e) {
                const q = (e.detail.value || '').trim();
                clearTimeout(buscarTimeout);
                if (q.length < 2) return;
                buscarTimeout = setTimeout(async () => {
                    try {
                        const res = await fetch(`${urls.clienteBuscar}?q=${encodeURIComponent(q)}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await res.json();
                        clienteChoices.setChoices(
                            data.map(c => ({ value: String(c.id), label: c.label, customProperties: { phone: c.phone || '' } })),
                            'value', 'label', true
                        );
                    } catch (err) { /* ignorar errores de red transitorios */ }
                }, 250);
            });

            // Al elegir un cliente de la lista, precargar su teléfono guardado.
            const telefonoInput = document.getElementById('telefonoCliente');
            clienteSelect.addEventListener('choice', function (e) {
                const props = e.detail.choice.customProperties;
                telefonoInput.value = (props && props.phone) || '';
            });

            // Fija (o limpia) el cliente seleccionado + su teléfono. label/phone
            // necesarios al editar, porque la opción no está precargada.
            function setCliente(id, label, phone) {
                if (id) {
                    clienteChoices.setChoices(
                        [{ value: String(id), label: label || ('Cliente #' + id), selected: true }],
                        'value', 'label', true
                    );
                    telefonoInput.value = phone || '';
                } else {
                    clienteChoices.setChoices(
                        [{ value: '', label: 'Buscar cliente...', placeholder: true, selected: true }],
                        'value', 'label', true
                    );
                    telefonoInput.value = '';
                }
            }

            // --- Alta rápida de cliente ---
            const ncBox = document.getElementById('nuevoClienteBox');
            const ncAlert = document.getElementById('nuevoClienteAlert');

            function mostrarNuevoCliente(mostrar) {
                ncBox.classList.toggle('d-none', !mostrar);
                ncAlert.classList.add('d-none');
                if (!mostrar) {
                    ['ncNombre', 'ncApellido', 'ncTelefono'].forEach(id => document.getElementById(id).value = '');
                }
            }

            document.getElementById('toggleNuevoCliente').addEventListener('click', function (e) {
                e.preventDefault();
                mostrarNuevoCliente(ncBox.classList.contains('d-none'));
            });
            document.getElementById('ncCancelar').addEventListener('click', () => mostrarNuevoCliente(false));

            document.getElementById('ncGuardar').addEventListener('click', async function () {
                ncAlert.classList.add('d-none');
                const payload = {
                    name: document.getElementById('ncNombre').value.trim(),
                    surname: document.getElementById('ncApellido').value.trim(),
                    phone: document.getElementById('ncTelefono').value.trim(),
                };
                if (!payload.name || !payload.surname) {
                    ncAlert.textContent = 'Nombre y apellido son obligatorios.';
                    ncAlert.classList.remove('d-none');
                    return;
                }
                const { ok, data } = await enviar(urls.clienteRapido, 'POST', payload);
                if (!ok) {
                    ncAlert.textContent = data.message || 'No se pudo crear el cliente.';
                    ncAlert.classList.remove('d-none');
                    return;
                }
                // Sumar el cliente nuevo al select, seleccionarlo y precargar su teléfono.
                clienteChoices.setChoices([{ value: String(data.id), label: data.label, selected: true }], 'value', 'label', false);
                telefonoInput.value = data.phone || payload.phone || '';
                mostrarNuevoCliente(false);
            });

            function toLocalInput(date) {
                const d = new Date(date);
                d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
                return d.toISOString().slice(0, 16);
            }

            // Paleta de colores (misma que Google Calendar).
            const paleta = document.getElementById('paletaColores');
            function setColor(hex) {
                document.getElementById('turnoColor').value = hex || '';
                paleta.querySelectorAll('.color-swatch').forEach(function (b) {
                    b.classList.toggle('selected', (b.dataset.color || '') === (hex || ''));
                });
            }
            paleta.addEventListener('click', function (e) {
                const b = e.target.closest('.color-swatch');
                if (b) setColor(b.dataset.color);
            });

            function abrirNuevo(fecha) {
                form.reset();
                document.getElementById('turnoId').value = '';
                document.getElementById('modalTurnoTitulo').textContent = 'Nuevo turno';
                document.getElementById('btnEliminarTurno').classList.add('d-none');
                document.getElementById('estado').value = 'pendiente';
                document.getElementById('servicioId').value = '';
                setColor('');
                setCliente('');
                mostrarNuevoCliente(false);
                if (fecha) {
                    const f = new Date(fecha);
                    // Click en vista Mes llega a medianoche: poné una hora razonable.
                    if (f.getHours() === 0 && f.getMinutes() === 0) {
                        f.setHours(9, 0, 0, 0);
                    }
                    document.getElementById('iniciaEn').value = toLocalInput(f);
                }
                ocultarAlert();
                modal.show();
            }

            function abrirEdicion(event) {
                form.reset();
                const p = event.extendedProps;
                document.getElementById('turnoId').value = event.id;
                document.getElementById('modalTurnoTitulo').textContent = 'Editar turno';
                document.getElementById('btnEliminarTurno').classList.remove('d-none');
                mostrarNuevoCliente(false);
                setCliente(p.client_id, p.cliente, p.cliente_telefono);
                document.getElementById('servicioId').value = p.servicio_id || '';
                setColor(p.color_propio || '');
                document.getElementById('iniciaEn').value = toLocalInput(event.start);
                document.getElementById('estado').value = p.estado;
                document.getElementById('notas').value = p.notas || '';
                ocultarAlert();
                modal.show();
            }

            function mostrarAlert(msg) {
                alertBox.textContent = msg;
                alertBox.classList.remove('d-none');
            }
            function ocultarAlert() {
                alertBox.classList.add('d-none');
            }

            async function enviar(url, metodo, body) {
                const res = await fetch(url, {
                    method: metodo,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: body ? JSON.stringify(body) : null,
                });
                const data = await res.json().catch(() => ({}));
                return { ok: res.ok, data };
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                ocultarAlert();
                const id = document.getElementById('turnoId').value;
                const payload = {
                    client_id: document.getElementById('clientId').value,
                    servicio_id: document.getElementById('servicioId').value || null,
                    color: document.getElementById('turnoColor').value || null,
                    telefono: telefonoInput.value.trim() || null,
                    inicia_en: document.getElementById('iniciaEn').value,
                    estado: document.getElementById('estado').value,
                    notas: document.getElementById('notas').value,
                };
                const url = id ? `${urls.base}/${id}` : urls.store;
                const { ok, data } = await enviar(url, id ? 'PUT' : 'POST', payload);
                if (!ok) {
                    mostrarAlert(data.message || 'No se pudo guardar el turno.');
                    return;
                }
                modal.hide();
                calendar.refetchEvents();
            });

            document.getElementById('btnEliminarTurno').addEventListener('click', async function () {
                const id = document.getElementById('turnoId').value;
                if (!id || !confirm('¿Eliminar este turno?')) return;
                const { ok, data } = await enviar(`${urls.base}/${id}`, 'DELETE');
                if (!ok) { mostrarAlert(data.message || 'No se pudo eliminar.'); return; }
                modal.hide();
                calendar.refetchEvents();
            });

            document.getElementById('btnExportarPdf').addEventListener('click', function (e) {
                e.preventDefault();
                const fecha = calendar.getDate().toISOString().slice(0, 10);
                window.location = `${urls.exportar}?fecha=${fecha}`;
            });

            document.addEventListener('DOMContentLoaded', function () {
                calendar = new FullCalendar.Calendar(document.getElementById('calendario'), {
                    locale: 'es',
                    initialView: 'timeGridWeek',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay',
                    },
                    slotMinTime: '08:00:00',
                    slotMaxTime: '21:00:00',
                    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                    nowIndicator: true,
                    editable: true,
                    selectable: true,
                    buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día' },
                    events: function (info, success, failure) {
                        const params = new URLSearchParams({
                            start: info.startStr,
                            end: info.endStr,
                        });
                        fetch(`${urls.eventos}?${params}`, { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json()).then(success).catch(failure);
                    },
                    dateClick: (info) => abrirNuevo(info.date),
                    eventClick: (info) => abrirEdicion(info.event),
                    eventDrop: async function (info) {
                        const { ok, data } = await enviar(
                            `${urls.base}/${info.event.id}/reagendar`,
                            'PATCH',
                            { inicia_en: toLocalInput(info.event.start) }
                        );
                        if (!ok) { alert(data.message || 'No se pudo reagendar.'); info.revert(); }
                    },
                });
                calendar.render();
            });
        })();
    </script>
@endpush
