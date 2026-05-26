<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\Production;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProductions = Production::sum('quantity');
        $totalWorkshops = Workshop::count();

        $ranking = Workshop::withSum('productions', 'quantity')
            ->orderByDesc('productions_sum_quantity')
            ->get();

        $labels = $ranking->pluck('name');
        $data = $ranking->pluck('productions_sum_quantity');

        return view('dashboard', compact(
            'totalProductions',
            'totalWorkshops',
            'ranking',
            'labels',
            'data'
        ));
    }
}