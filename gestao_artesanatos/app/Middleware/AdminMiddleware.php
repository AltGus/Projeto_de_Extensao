<?php

class AdminMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            flash('error', 'Você precisa fazer login para acessar o sistema.');
            redirect_to('/login');
        }

        if (!Auth::isAdmin()) {
            http_response_code(403);

            View::render('errors/403', [
                'title' => 'Acesso negado',
            ]);

            exit;
        }
    }
}