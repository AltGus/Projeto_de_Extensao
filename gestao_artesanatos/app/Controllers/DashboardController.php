<?php
// app/Controllers/DashboardController.php
class DashboardController extends Controller
{
    public function index(): void
    {
        $users = new User();
        $workshops = new Workshop();
        $products = new Product();
        $materials = new Material();
        $productions = new Production();
        $stock = new StockMovement();

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => [
                'oficinas' => count($workshops->all()),
                'participantes' => count($users->participants()),
                'produtos' => count($products->all()),
                'materiais' => count($materials->all()),
                'producao_total' => $productions->totalQuantity(),
            ],
            'ranking' => $workshops->ranking(),
            'recentProductions' => $productions->recent(),
            'monthlySummary' => $productions->monthlySummary(),
            'lowStock' => $materials->lowStock(),
            'stockTotals' => $stock->totals(),
        ]);
    }
}
