@extends('layouts.app')

@section('content')
<h1>Participantes</h1>

<a href="{{ route('participants.create') }}">Novo Participante</a>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Idade</th>
        <th>Ações</th>
    </tr>

    @foreach($participants as $p)
    <tr>
        <td>{{ $p->id }}</td>
        <td>{{ $p->name }}</td>
        <td>{{ $p->age }}</td>
        <td>
            <a href="{{ route('participants.edit', $p->id) }}">Editar</a>

            <form action="{{ route('participants.destroy', $p->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Deletar</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
@endsection