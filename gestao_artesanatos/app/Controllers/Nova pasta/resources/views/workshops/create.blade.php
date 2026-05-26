@extends('layouts.app')

@section('content')
<h1 class="mb-4">Nova Oficina</h1>

<div class="card shadow">
    <div class="card-body">

        <form action="{{ route('workshops.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nome da Oficina</label>
                <input type="text" name="name" class="form-control" placeholder="Ex: Arte, Costura..." required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <input type="text" name="description" class="form-control"
                    placeholder="Ex: Pintura, potes, artesanato, costura">
            </div>

            <button class="btn btn-success">Salvar</button>
            <a href="{{ route('workshops.index') }}" class="btn btn-secondary">Voltar</a>

        </form>

    </div>
</div>
@endsection