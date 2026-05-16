<?php

class View {

    public static function render(string $view, array $data = []): void {
        extract($data);

        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        echo $content;
    }

    public static function partial(string $view, array $data = []): void {
        extract($data);
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        }
    }

    public static function includeLayout(string $part, array $data = []): void {
        extract($data);
        $file = APP_PATH . '/Views/layouts/' . $part . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
}
