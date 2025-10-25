<?php
namespace App;

class Router {
  private array $routes = ['GET'=>[], 'POST'=>[]];

  public function get(string $path, callable $handler) {
    $this->routes['GET'][$path] = $handler;
  }

  public function post(string $path, callable $handler) {
    $this->routes['POST'][$path] = $handler;
  }

  public function dispatch() {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // fallback for shared hosts: ?r=/path
    if (isset($_GET['r'])) $uri = $_GET['r'];

    $handler = $this->routes[$method][$uri] ?? null;
    if (!$handler) {
      http_response_code(404);
      echo "404 Not Found";
      return;
    }
    $handler();
  }
}
