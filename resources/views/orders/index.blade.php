@extends('layouts.app')

@section('title', 'Pedidos E-Commerce')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="fs-4 fw-bold mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>Pedidos del E-Commerce
                </h2>
                <span class="badge bg-primary fs-6">{{ $orders->total() }} pedidos</span>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('orders.index') }}" class="mb-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por N° pedido, nombre o email..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">Estado</option>
                                @foreach(['pending' => 'Pendiente', 'confirmed' => 'Confirmado', 'processing' => 'En proceso', 'shipped' => 'Enviado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado'] as $val => $label)
                                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_method" class="form-select">
                                <option value="">Método de pago</option>
                                <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transferencia</option>
                                <option value="taca_taca" {{ request('payment_method') === 'taca_taca' ? 'selected' : '' }}>Taca Taca</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_status" class="form-select">
                                <option value="">Estado pago</option>
                                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Pagado</option>
                                <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Fallido</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            @if(request()->hasAny(['search', 'status', 'payment_method', 'payment_status']))
                                <a href="{{ route('orders.index') }}" class="btn btn-outline-danger" title="Limpiar">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>N° Pedido</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Pago</th>
                                <th>Envío</th>
                                <th>Estado</th>
                                <th>Est. Pago</th>
                                <th>Fecha</th>
                                <th style="width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                    </td>
                                    <td>
                                        {{ $order->user->name ?? '—' }}
                                        <br>
                                        <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($order->total, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        @if($order->payment_method === 'transfer')
                                            <span class="badge bg-info text-dark">Transferencia</span>
                                        @else
                                            <span class="badge bg-dark">Taca Taca</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $shippingLabels = [
                                                'local_pickup' => 'Retiro en local',
                                                'cordoba' => 'Córdoba',
                                                'national' => 'Interior',
                                            ];
                                        @endphp
                                        <span class="badge bg-secondary">{{ $shippingLabels[$order->shipping_method] ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'processing' => 'primary',
                                                'shipped' => 'secondary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Pendiente',
                                                'confirmed' => 'Confirmado',
                                                'processing' => 'En proceso',
                                                'shipped' => 'Enviado',
                                                'delivered' => 'Entregado',
                                                'cancelled' => 'Cancelado',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $paymentColors = ['pending' => 'warning', 'paid' => 'success', 'failed' => 'danger'];
                                            $paymentLabels = ['pending' => 'Pendiente', 'paid' => 'Pagado', 'failed' => 'Fallido'];
                                        @endphp
                                        <span class="badge bg-{{ $paymentColors[$order->payment_status] ?? 'secondary' }}">
                                            {{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-shopping-cart fa-2x text-muted mb-2 d-block"></i>
                                        @if(request()->hasAny(['search', 'status', 'payment_method', 'payment_status']))
                                            No se encontraron pedidos con los filtros aplicados.
                                        @else
                                            Todavía no hay pedidos en el e-commerce.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Mostrando {{ $orders->firstItem() ?? 0 }} a {{ $orders->lastItem() ?? 0 }} de {{ $orders->total() }} resultados
                        </div>
                        <div>
                            {{ $orders->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
