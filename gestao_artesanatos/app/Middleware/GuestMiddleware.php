<?php

class GuestMiddleware
{
    public function handle(): void
    {
        if (Auth::check()) {
            redirect_to('/dashboard');
        }
    }
}