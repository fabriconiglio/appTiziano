<!-- resources/views/clients/show.blade.php -->
@extends('layouts.app')

@section('title', 'Detalle de Cliente Distribuidor')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Información del Cliente Distribuidor: {{ $distributorClient->name }} {{ $distributorClient->surname }}</h5>
                <div>
                    <a href="{{ route('distributor-clients.edit', $distributorClient) }}" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('distributor-clients.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Datos Personales</h6>
                        <dl class="row">
                            <dt class="col-sm-4">Nombre Completo:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->name }} {{ $distributorClient->surname }}</dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->email ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">DNI:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->dni ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->phone ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">Fecha de Nacimiento:</dt>
                            <dd class="col-sm-8">
                                @if($distributorClient->birth_date && !is_string($distributorClient->birth_date))
                                    {{ $distributorClient->birth_date->format('d/m/Y') }}
                                @else
                                    {{ $distributorClient->birth_date ?? 'No registrada' }}
                                @endif
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Información Adicional</h6>
                        <dl class="row">
                            <dt class="col-sm-4">Observaciones:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->observations ?? 'Sin observaciones' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Auto-cerrar alertas después de 5 segundos
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    var alerts = document.querySelectorAll('.alert');
                    alerts.forEach(function(alert) {
                        var bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });
        </script>
    @endpush
@endsection
