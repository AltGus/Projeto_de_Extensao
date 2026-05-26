<?php
// app/Middleware/AuthMiddleware.php
class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            flash('error', 'Faça login para acessar o sistema.');
            redirect_to('/login');
        }
    }
}

<?php
// app/Middleware/AdminMiddleware.php
class AdminMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            flash('error', 'Faça login para continuar.');
            redirect_to('/login');
        }

        if (!Auth::isAdmin()) {
            abort(403);
        }
    }
}

<?php
// app/Middleware/GuestMiddleware.php
class GuestMiddleware
{
    public function handle(): void
    {
        if (Auth::check()) {
            redirect_to('/dashboard');
        }
    }
}
