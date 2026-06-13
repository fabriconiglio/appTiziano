<!-- resources/views/servicios/create.blade.php -->
@extends('layouts.app')

@section('title', 'Nuevo Servicio')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Nuevo Servicio</h5>
                        <a href="{{ route('servicios.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('servicios.store') }}" method="POST">
                            @include('servicios._form')
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
