@extends('layouts.app')

@section('title', 'Pedido ' . $order->order_number)

@section('content')
    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fs-4 fw-bold mb-0">
                <i class="fas fa-receipt me-2"></i>Pedido {{ $order->order_number }}
            </h2>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>

        <div class="row g-4">
            {{-- Order info --}}
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Productos del pedido</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio unitario</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                            <br>
                                            <small class="text-muted">ID producto: {{ $item->product_id }}</small>
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="text-end"><strong>${{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                                    <td class="text-end"><strong class="fs-5">${{ number_format($order->total, 0, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                @if($order->notes)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Notas del cliente</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $order->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="col-md-4">
                {{-- Client info --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Cliente</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $order->user->name ?? '—' }}</strong></p>
                        <p class="mb-0">
                            <a href="mailto:{{ $order->user->email ?? '' }}">{{ $order->user->email ?? '—' }}</a>
                        </p>
                    </div>
                </div>

                {{-- Shipping info --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Datos de envío</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <small class="text-muted d-block">Método de envío</small>
                            @php
                                $shippingLabels = [
                                    'local_pickup' => 'Retiro en local',
                                    'cordoba' => 'Uber Motos — Córdoba Capital',
                                    'national' => 'Andreani — Interior',
                                ];
                            @endphp
                            <strong>{{ $shippingLabels[$order->shipping_method] ?? $order->shipping_method ?? '—' }}</strong>
                            @if($order->shipping_cost)
                                <span class="badge bg-info ms-2">Envío: ${{ number_format($order->shipping_cost, 2, ',', '.') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item">
                            <small class="text-muted d-block">Destinatario</small>
                            <strong>{{ $order->shipping_name ?? '—' }}</strong>
                        </li>
                        <li class="list-group-item">
                            <small class="text-muted d-block">Teléfono</small>
                            {{ $order->shipping_phone ?? '—' }}
                        </li>
                        <li class="list-group-item">
                            <small class="text-muted d-block">Dirección</small>
                            {{ $order->shipping_address ?? '—' }}
                            @if($order->shipping_address_2)
                                <br>{{ $order->shipping_address_2 }}
                            @endif
                        </li>
                        <li class="list-group-item">
                            <small class="text-muted d-block">Localidad</small>
                            {{ $order->shipping_city ?? '—' }}, {{ $order->shipping_province ?? '—' }} ({{ $order->shipping_zip ?? '—' }})
                        </li>
                    </ul>
                </div>

                {{-- Status management --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Gestionar estado</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('orders.update-status', $order->id) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado del pedido</label>
                                <select name="status" class="form-select">
                                    @foreach(['pending' => 'Pendiente', 'confirmed' => 'Confirmado', 'processing' => 'En proceso', 'shipped' => 'Enviado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado'] as $val => $label)
                                        <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado del pago</label>
                                <select name="payment_status" class="form-select">
                                    @foreach(['pending' => 'Pendiente', 'paid' => 'Pagado', 'failed' => 'Fallido'] as $val => $label)
                                        <option value="{{ $val }}" {{ $order->payment_status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i>Actualizar estado
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Order meta --}}
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Información</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Método de pago</span>
                            @if($order->payment_method === 'transfer')
                                <span class="badge bg-info text-dark">Transferencia</span>
                            @elseif($order->payment_method === 'mercadopago')
                                <span class="badge bg-primary">Mercado Pago</span>
                            @endif
                        </li>
                        @if($order->mercadopago_preference_id)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Preferencia MP</span>
                                <code class="small">{{ $order->mercadopago_preference_id }}</code>
                            </li>
                        @endif
                        @if($order->mercadopago_payment_id)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Pago MP</span>
                                <code class="small">{{ $order->mercadopago_payment_id }}</code>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Creado</span>
                            <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Actualizado</span>
                            <span>{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
