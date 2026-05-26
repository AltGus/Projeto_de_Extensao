<?php
// app/Controllers/MaterialController.php
class MaterialController extends Controller
{
    private Material $materials;

    public function __construct()
    {
        $this->materials = new Material();
    }

    public function index(): void
    {
        $this->view('materials/index', [
            'title' => 'Materiais',
            'items' => $this->materials->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('materials/create', ['title' => 'Novo material']);
    }

    public function store(): void
    {
        $this->requireFields($_POST, ['name', 'category', 'unit'], '/materiais/criar');

        $this->attempt(function (): void {
            $this->materials->create($_POST);
        }, 'Material criado com sucesso.', '/materiais');
    }

    public function edit($id): void
    {
        $item = $this->materials->find((int) $id);
        if (!$item) {
            abort(404);
        }

        $this->view('materials/edit', [
            'title' => 'Editar material',
            'material' => $item,
        ]);
    }

    public function update($id): void
    {
        $this->requireFields($_POST, ['name', 'category', 'unit'], '/materiais/' . (int) $id . '/editar');

        $this->attempt(function () use ($id): void {
            $this->materials->update((int) $id, $_POST);
        }, 'Material atualizado com sucesso.', '/materiais');
    }

    public function delete($id): void
    {
        $this->attempt(function () use ($id): void {
            $this->materials->delete((int) $id);
        }, 'Material excluído com sucesso.', '/materiais');
    }
}
