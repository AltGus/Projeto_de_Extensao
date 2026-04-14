@extends('layouts.app')

@section('content')
<h1 class="mb-4">Oficinas</h1>

<a href="{{ route('workshops.create') }}" class="btn btn-primary mb-3">
    + Nova Oficina
</a>

<div class="row">
@foreach($workshops as $w)
    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">

                <h5 class="card-title">{{ $w->name }}</h5>

                <p class="text-muted">
                    {{ $w->description }}
                </p>

                <hr>

                <p><strong>Total Produzido:</strong></p>

                <h3 class="text-success">
                    {{ $w->productions->sum('quantity') }}
                </h3>

                <hr>

                <strong>Produções:</strong>
                <ul>
                    @forelse($w->productions as $p)
                        <li>{{ $p->product }} ({{ $p->quantity }})</li>
                    @empty
                        <li class="text-muted">Nenhuma produção</li>
                    @endforelse
                </ul>

            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('workshops.edit', $w->id) }}" class="btn btn-warning btn-sm">
                    Editar
                </a>

                <form action="{{ route('workshops.destroy', $w->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">Excluir</button>
                </form>
            </div>
        </div>
    </div>
@endforeach
</div>

@endsection