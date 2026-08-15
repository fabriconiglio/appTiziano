@extends('layouts.app')

@section('title', 'Devolución ' . $devolucion->return_number)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            Devolución {{ $devolucion->return_number }}
            @if($devolucion->estaAnulada())
                <span class="badge bg-danger align-middle">Anulada</span>
            @endif
        </h1>
        <div>
            <a href="{{ route('distributor-returns.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('distributor-returns.comprobante', $devolucion) }}" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Comprobante
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($devolucion->estaAnulada())
        <div class="alert alert-danger">
            <strong>Devolución anulada</strong> el {{ $devolucion->anulada_en->format('d/m/Y H:i') }}
            por {{ $devolucion->anuladaPor?->name }}.
            <br><small>Motivo: {{ $devolucion->motivo_anulacion }}</small>
            <br><small class="text-muted">El stock y la cuenta corriente ya fueron revertidos.</small>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light"><strong>Datos</strong></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Cliente</dt>
                        <dd class="col-7">{{ $devolucion->nombreCliente() }}</dd>

                        <dt class="col-5">Fecha</dt>
                        <dd class="col-7">{{ $devolucion->return_date->format('d/m/Y') }}</dd>

                        <dt class="col-5">Compra de origen</dt>
                        <dd class="col-7">
                            {{ $devolucion->technicalRecord?->purchase_date?->format('d/m/Y') ?? '-' }}
                            <small class="text-muted">({{ $devolucion->dias_desde_compra }} días antes)</small>
                        </dd>

                        <dt class="col-5">Destino</dt>
                        <dd class="col-7">
                            @switch($devolucion->destino)
                                @case('cuenta_corriente')
                                    <span class="badge bg-info text-dark">Cuenta corriente</span> @break
                                @case('efectivo')
                                    <span class="badge bg-success">Efectivo</span> @break
                                @default
                                    <span class="badge bg-warning text-dark">Vale a favor</span>
                            @endswitch
                        </dd>

                        <dt class="col-5">Motivo</dt>
                        <dd class="col-7">{{ $devolucion->motivo ?: '-' }}</dd>

                        <dt class="col-5">Cargada por</dt>
                        <dd class="col-7">{{ $devolucion->user?->name }}</dd>

                        @if($devolucion->fuera_de_plazo)
                            <dt class="col-5">Fuera de plazo</dt>
                            <dd class="col-7">
                                <span class="badge bg-warning text-dark">Sí</span>
                                <small class="text-muted">autorizada por {{ $devolucion->autorizadaPor?->name }}</small>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light"><strong>Total devuelto</strong></div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div class="display-5 fw-bold text-success">
                        ${{ number_format($devolucion->total_amount, 2, ',', '.') }}
                    </div>
                    @if($devolucion->observations)
                        <p class="text-muted mt-3 mb-0">{{ $devolucion->observations }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light"><strong>Productos devueltos</strong></div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio unitario</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devolucion->products_returned as $item)
                        <tr>
                            <td>{{ $item['nombre'] }}</td>
                            <td class="text-center">{{ $item['cantidad'] }}</td>
                            <td class="text-end">${{ number_format($item['precio_unitario'], 2, ',', '.') }}</td>
                            <td class="text-end">${{ number_format($item['subtotal'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($puedeAnular && ! $devolucion->estaAnulada())
        <div class="card border-danger mb-5">
            <div class="card-header"><strong class="text-danger">Anular devolución</strong></div>
            <div class="card-body">
                <p class="text-muted">
                    Al anularla se resta del stock lo que se había reintegrado y se compensa la cuenta
                    corriente. La compra vuelve a quedar disponible para una nueva devolución.
                </p>
                <form method="POST" action="{{ route('distributor-returns.anular', $devolucion) }}"
                      onsubmit="return confirm('¿Seguro que querés anular esta devolución?')">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-9">
                            <input type="text" name="motivo_anulacion" class="form-control"
                                   placeholder="Motivo de la anulación (obligatorio)" required maxlength="500">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-danger w-100">
                                <i class="fas fa-ban"></i> Anular
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
