@extends('layouts.app')

@section('content')
<h1 class="mb-4">Editar Oficina</h1>

<div class="card shadow">
    <div class="card-body">

        <form action="{{ route('workshops.update', $workshop->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="{{ $workshop->name }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <input type="text" name="description" value="{{ $workshop->description }}" class="form-control">
            </div>

            <button class="btn btn-success">Atualizar</button>
            <a href="{{ route('workshops.index') }}" class="btn btn-secondary">Voltar</a>

        </form>

    </div>
</div>
@endsection