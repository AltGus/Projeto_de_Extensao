@extends('layouts.app')

@section('content')
<h1>Dashboard</h1>

<p>Bem-vindo ao sistema.</p>

<ul>
    <li><a href="/participants">Gerenciar Participantes</a></li>
    <li><a href="/workshops">Gerenciar Oficinas</a></li>
    <li><a href="/productions">Gerenciar Produções</a></li>
</ul>
@endsection