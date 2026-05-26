@extends('layouts.app')

@section('content')
<h1>Novo Participante</h1>

<form action="{{ route('participants.store') }}" method="POST">
    @csrf

    <label>Nome:</label>
    <input type="text" name="name" required>

    <br><br>

    <label>Idade:</label>
    <input type="number" name="age">

    <br><br>

    <button type="submit">Salvar</button>
</form>

<a href="{{ route('participants.index') }}">Voltar</a>
@endsection