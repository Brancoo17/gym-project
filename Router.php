<?php

namespace MVC;

class Router
{
    public array $getRoutes = [];
    public array $postRoutes = [];

    public function get($url, $fn)
    {
        $this->getRoutes[$url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$url] = $fn;
    }

    public function comprobarRutas()
    {
        session_start();

        $currentUrl = $_SERVER['PATH_INFO'] ?? '/';
        if ($currentUrl === '/' && isset($_SERVER['REQUEST_URI'])) {
            $currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
            $currentUrl = preg_replace('#^/public#', '', $currentUrl) ?: '/';
        }
        $currentUrl = preg_replace('#^/index\.php#', '', $currentUrl) ?: '/';

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }

        if ($fn) {
            call_user_func($fn, $this);
        } else {
            echo "Página No Encontrada o Ruta no válida";
        }
    }

    public function render($view, $datos = [])
    {
        foreach ($datos as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include_once __DIR__ . "/views/$view.php";
        $contenido = ob_get_clean();
        include_once __DIR__ . '/views/layout.php';
    }
}
