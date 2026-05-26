<?php
// app/Controllers/StockController.php
class StockController extends Controller
{
    private StockMovement $movements;
    private Material $materials;

    public function __construct()
    {
        $this->movements = new StockMovement();
        $this->materials = new Material();
    }

    public function index(): void
    {
        $this->view('stock/index', [
            'title' => 'Estoque',
            'materials' => $this->materials->all(),
            'movements' => $this->movements->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('stock/create', [
            'title' => 'Nova movimentação de estoque',
            'materials' => $this->materials->all(),
        ]);
    }

    public function store(): void
    {
        $this->requireFields($_POST, ['material_id', 'movement_type', 'quantity', 'happened_at'], '/estoque/criar');

        $this->attempt(function (): void {
            $payload = $_POST;
            $payload['responsible_id'] = (int) Auth::id();
            $this->movements->create($payload);
        }, 'Movimentação registrada com sucesso.', '/estoque');
    }

    public function edit($id): void
    {
        $item = $this->movements->find((int) $id);
        if (!$item) {
            abort(404);
        }

        $this->view('stock/edit', [
            'title' => 'Editar movimentação',
            'movement' => $item,
            'materials' => $this->materials->all(),
        ]);
    }

    public function update($id): void
    {
        $this->requireFields($_POST, ['material_id', 'movement_type', 'quantity', 'happened_at'], '/estoque/' . (int) $id . '/editar');

        $this->attempt(function () use ($id): void {
            $payload = $_POST;
            $payload['responsible_id'] = (int) Auth::id();
            $this->movements->update((int) $id, $payload);
        }, 'Movimentação atualizada com sucesso.', '/estoque');
    }

    public function delete($id): void
    {
        $this->attempt(function () use ($id): void {
            $this->movements->delete((int) $id);
        }, 'Movimentação excluída com sucesso.', '/estoque');
    }
}
