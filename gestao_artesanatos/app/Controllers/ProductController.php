<?php
// app/Controllers/ProductController.php
class ProductController extends Controller
{
    private Product $products;

    public function __construct()
    {
        $this->products = new Product();
    }

    public function index(): void
    {
        $this->view('products/index', [
            'title' => 'Produtos',
            'items' => $this->products->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('products/create', ['title' => 'Novo produto']);
    }

    public function store(): void
    {
        $this->requireFields($_POST, ['name', 'category'], '/produtos/criar');

        $this->attempt(function (): void {
            $this->products->create($_POST);
        }, 'Produto criado com sucesso.', '/produtos');
    }

    public function edit($id): void
    {
        $item = $this->products->find((int) $id);
        if (!$item) {
            abort(404);
        }

        $this->view('products/edit', [
            'title' => 'Editar produto',
            'product' => $item,
        ]);
    }

    public function update($id): void
    {
        $this->requireFields($_POST, ['name', 'category'], '/produtos/' . (int) $id . '/editar');

        $this->attempt(function () use ($id): void {
            $this->products->update((int) $id, $_POST);
        }, 'Produto atualizado com sucesso.', '/produtos');
    }

    public function delete($id): void
    {
        $this->attempt(function () use ($id): void {
            $this->products->delete((int) $id);
        }, 'Produto excluído com sucesso.', '/produtos');
    }
}
