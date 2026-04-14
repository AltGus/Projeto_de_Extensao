@extends('layouts.app')

@section('content')
<h1 class="mb-4">Editar Produção</h1>

<div class="card shadow">
    <div class="card-body">

        <form action="{{ route('productions.update', $production->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Oficina</label>
                <select name="workshop_id" class="form-select">
                    @foreach($workshops as $w)
                        <option value="{{ $w->id }}"
                            {{ $production->workshop_id == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Produto</label>
                <input type="text" name="product" value="{{ $production->product }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Quantidade</label>
                <input type="number" name="quantity" value="{{ $production->quantity }}" class="form-control">
            </div>

            <button class="btn btn-success">Atualizar</button>
            <a href="{{ route('productions.index') }}" class="btn btn-secondary">Voltar</a>

        </form>

    </div>
</div>
@endsection