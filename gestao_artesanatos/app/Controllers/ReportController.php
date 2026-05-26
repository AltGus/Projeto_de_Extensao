<?php
// app/Controllers/ReportController.php
class ReportController extends Controller
{
    public function index(): void
    {
        $workshops = new Workshop();
        $productions = new Production();
        $materials = new Material();
        $stock = new StockMovement();

        $this->view('reports/index', [
            'title' => 'Relatórios',
            'ranking' => $workshops->ranking(),
            'monthlySummary' => $productions->monthlySummary(),
            'lowStock' => $materials->lowStock(),
            'stockTotals' => $stock->totals(),
        ]);
    }
}
