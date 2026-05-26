<?php
// app/Controllers/ProductionController.php
class ProductionController extends Controller
{
    private Production $productions;
    private Workshop $workshops;
    private Product $products;
    private User $users;

    public function __construct()
    {
        $this->productions = new Production();
        $this->workshops = new Workshop();
        $this->products = new Product();
        $this->users = new User();
    }

    public function index(): void
    {
        $this->view('productions/index', [
            'title' => 'Produções',
            'items' => $this->productions->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('productions/create', [
            'title' => 'Nova produção',
            'workshops' => $this->workshops->all(),
            'products' => $this->products->all(),
            'users' => $this->users->all(),
        ]);
    }

    public function store(): void
    {
        $this->requireFields($_POST, ['workshop_id', 'product_id', 'quantity', 'produced_at', 'purpose'], '/producoes/criar');

        $this->attempt(function (): void {
            $payload = $_POST;
            $payload['responsible_id'] = Auth::isAdmin() && !empty($_POST['responsible_id'])
                ? (int) $_POST['responsible_id']
                : (int) Auth::id();

            $this->productions->create($payload);
        }, 'Produção registrada com sucesso.', '/producoes');
    }

    public function edit($id): void
    {
        $item = $this->productions->find((int) $id);
        if (!$item) {
            abort(404);
        }

        $this->view('productions/edit', [
            'title' => 'Editar produção',
            'production' => $item,
            'workshops' => $this->workshops->all(),
            'products' => $this->products->all(),
            'users' => $this->users->all(),
        ]);
    }

    public function update($id): void
    {
        $this->requireFields($_POST, ['workshop_id', 'product_id', 'quantity', 'produced_at', 'purpose', 'responsible_id'], '/producoes/' . (int) $id . '/editar');

        $this->attempt(function () use ($id): void {
            $this->productions->update((int) $id, $_POST);
        }, 'Produção atualizada com sucesso.', '/producoes');
    }

    public function delete($id): void
    {
        $this->attempt(function () use ($id): void {
            $this->productions->delete((int) $id);
        }, 'Produção excluída com sucesso.', '/producoes');
    }
}
