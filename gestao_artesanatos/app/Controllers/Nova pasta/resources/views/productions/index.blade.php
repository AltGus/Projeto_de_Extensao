@extends('layouts.app')

@section('content')
<h1 class="mb-4">Produções</h1>

<a href="{{ route('productions.create') }}" class="btn btn-primary mb-3">
    + Nova Produção
</a>

<div class="card shadow">
    <div class="card-body">

        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Oficina</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                @foreach($productions as $p)
                <tr>
                    <td>
                        <span class="badge bg-secondary">
                            {{ $p->workshop->name }}
                        </span>
                    </td>

                    <td>
                        <strong>{{ $p->product }}</strong>
                    </td>

                    <td>
                        <span class="badge bg-success fs-6">
                            {{ $p->quantity }}
                        </span>
                    </td>

                    <td>
                        <a href="{{ route('productions.edit', $p->id) }}" class="btn btn-warning btn-sm">
                            Editar
                        </a>

                        <form action="{{ route('productions.destroy', $p->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>
@endsection