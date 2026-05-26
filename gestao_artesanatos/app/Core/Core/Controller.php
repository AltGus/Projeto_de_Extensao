<?php
// app/Core/Controller.php
abstract class Controller
{
    protected function view(string $page, array $data = [], string $layout = 'app'): void
    {
        View::render($page, $data, $layout);
    }

    protected function requireFields(array $input, array $fields, string $redirect): void
    {
        foreach ($fields as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                flash('error', 'Preencha todos os campos obrigatórios.');
                redirect_to($redirect);
            }
        }
    }

    protected function attempt(callable $callback, string $success, string $redirect, string $errorPrefix = 'Erro na operação'): void
    {
        try {
            $callback();
            flash('success', $success);
        } catch (Throwable $e) {
            flash('error', $errorPrefix . ': ' . $e->getMessage());
        }

        redirect_to($redirect);
    }
}
