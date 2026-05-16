<?php

class Controller {

    protected function view(string $view, array $data = []): void {
        View::render($view, $data);
    }

    protected function redirect(string $path): void {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    protected function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireAuth(): void {
        if (!Auth::check()) {
            $this->redirect('login');
        }
    }

    protected function requireAdmin(): void {
        if (!Auth::check()) {
            $this->redirect('login');
        }
        if (!Auth::isAdminAuthorized()) {
            http_response_code(403);
            View::render('errors/403', ['title' => '403 - Access Denied']);
            exit;
        }
    }

    protected function validateCsrf(): bool {
        return Csrf::validate($_POST['_token'] ?? '');
    }
}
