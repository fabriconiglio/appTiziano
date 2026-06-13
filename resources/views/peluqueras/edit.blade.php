<!-- resources/views/peluqueras/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Editar Peluquera')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Editar Peluquera</h5>
                        <a href="{{ route('peluqueras.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('peluqueras.update', $peluquera) }}" method="POST">
                            @method('PUT')
                            @include('peluqueras._form')
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
