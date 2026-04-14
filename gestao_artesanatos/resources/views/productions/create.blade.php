@extends('layouts.app')

@section('content')
<h1 class="mb-4">Nova Produção</h1>

<div class="card shadow">
    <div class="card-body">

        <form action="{{ route('productions.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Oficina</label>
                <select name="workshop_id" class="form-select" required>
                    @foreach($workshops as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Produto</label>
                <input type="text" name="product" class="form-control" placeholder="Ex: Pintura, Pote, Costura" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantidade</label>
                <input type="number" name="quantity" class="form-control" min="1" required>
            </div>

            <button class="btn btn-success">Salvar</button>
            <a href="{{ route('productions.index') }}" class="btn btn-secondary">Voltar</a>

        </form>

    </div>
</div>
@endsection