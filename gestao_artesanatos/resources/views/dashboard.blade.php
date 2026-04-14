@extends('layouts.app')

@section('content')

<h1 class="mb-4">Dashboard</h1>

<div class="row mb-4">

    <div class="col-md-6">
        <div class="card text-white bg-primary shadow">
            <div class="card-body">
                <h5>Total Produzido</h5>
                <h2>{{ $totalProductions }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card text-white bg-dark shadow">
            <div class="card-body">
                <h5>Total de Oficinas</h5>
                <h2>{{ $totalWorkshops }}</h2>
            </div>
        </div>
    </div>

</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <h4 class="mb-3">Ranking das Oficinas</h4>

        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Posição</th>
                    <th>Oficina</th>
                    <th>Produção</th>
                </tr>
            </thead>

            <tbody>
                @foreach($ranking as $index => $r)
                <tr>

                    <td>
                        @if($index == 0)
                            🥇
                        @elseif($index == 1)
                            🥈
                        @elseif($index == 2)
                            🥉
                        @else
                            {{ $index + 1 }}
                        @endif
                    </td>

                    <td>
                        <strong>{{ $r->name }}</strong>
                    </td>

                    <td>
                        <span class="badge bg-success fs-6">
                            {{ $r->productions_sum_quantity ?? 0 }}
                        </span>
                    </td>

                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <h4 class="mb-3">Gráfico de Produção</h4>

        <canvas id="chart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($labels) !!},
        datasets: [{
            label: 'Produção',
            data: {!! json_encode($data) !!}
        }]
    }
});
</script>

@endsection