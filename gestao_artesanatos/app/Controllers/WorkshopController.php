<?php
// app/Controllers/WorkshopController.php
class WorkshopController extends Controller
{
    private Workshop $workshops;
    private Production $productions;
    private User $users;

    public function __construct()
    {
        $this->workshops = new Workshop();
        $this->productions = new Production();
        $this->users = new User();
    }

    public function index(): void
    {
        $this->view('workshops/index', [
            'title' => 'Oficinas',
            'items' => $this->workshops->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('workshops/create', ['title' => 'Nova oficina']);
    }

    public function store(): void
    {
        $this->requireFields($_POST, ['name'], '/oficinas/criar');

        $this->attempt(function (): void {
            $this->workshops->create($_POST);
        }, 'Oficina criada com sucesso.', '/oficinas');
    }

    public function show($id): void
    {
        $workshop = $this->workshops->find((int) $id);
        if (!$workshop) {
            abort(404);
        }

        $this->view('workshops/show', [
            'title' => 'Detalhe da oficina',
            'workshop' => $workshop,
            'participants' => $this->workshops->participants((int) $id),
            'availableParticipants' => $this->workshops->availableParticipants((int) $id),
            'productions' => $this->productions->byWorkshop((int) $id),
        ]);
    }

    public function edit($id): void
    {
        $workshop = $this->workshops->find((int) $id);
        if (!$workshop) {
            abort(404);
        }

        $this->view('workshops/edit', [
            'title' => 'Editar oficina',
            'workshop' => $workshop,
        ]);
    }

    public function update($id): void
    {
        $this->requireFields($_POST, ['name'], '/oficinas/' . (int) $id . '/editar');

        $this->attempt(function () use ($id): void {
            $this->workshops->update((int) $id, $_POST);
        }, 'Oficina atualizada com sucesso.', '/oficinas');
    }

    public function delete($id): void
    {
        $this->attempt(function () use ($id): void {
            $this->workshops->delete((int) $id);
        }, 'Oficina excluída com sucesso.', '/oficinas');
    }

    public function addParticipant($id): void
    {
        $this->requireFields($_POST, ['user_id'], '/oficinas/' . (int) $id);

        $this->attempt(function () use ($id): void {
            $this->workshops->addParticipant((int) $id, (int) $_POST['user_id']);
        }, 'Participante vinculado com sucesso.', '/oficinas/' . (int) $id);
    }

    public function removeParticipant($id, $userId): void
    {
        $this->attempt(function () use ($id, $userId): void {
            $this->workshops->removeParticipant((int) $id, (int) $userId);
        }, 'Participante removido da oficina.', '/oficinas/' . (int) $id);
    }
}
