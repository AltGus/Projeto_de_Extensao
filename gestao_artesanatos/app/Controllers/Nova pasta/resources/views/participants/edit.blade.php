@extends('layouts.app')

@section('content')
<h1>Editar Participante</h1>

<form action="{{ route('participants.update', $participant->id) }}" method="POST">
    @csrf
    @method('PUT')

    <label>Nome:</label>
    <input type="text" name="name" value="{{ $participant->name }}" required>

    <br><br>

    <label>Idade:</label>
    <input type="number" name="age" value="{{ $participant->age }}">

    <br><br>

    <button type="submit">Atualizar</button>
</form>

<a href="{{ route('participants.index') }}">Voltar</a>
@endsection