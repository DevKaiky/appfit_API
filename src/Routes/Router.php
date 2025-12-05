<?php

namespace App\Routes;

/**
 * Classe Router
 * Gerencia o roteamento central da aplicação
 */
class Router
{
    private array $routes = [];
    private string $metodo;
    private string $uri;

    public function __construct()
    {
        $this->metodo = $_SERVER['REQUEST_METHOD'];
        $this->uri = $this->parseUri();
    }

    /**
     * Limpa e retorna a URI da requisição
     */
    private function parseUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace('/index.php', '', $uri);
        return rtrim($uri, '/');
    }

    /**
     * Registra uma rota GET
     */
    public function get(string $path, callable $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Registra uma rota POST
     */
    public function post(string $path, callable $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }

    /**
     * Registra uma rota PUT
     */
    public function put(string $path, callable $callback): void
    {
        $this->addRoute('PUT', $path, $callback);
    }

    /**
     * Registra uma rota DELETE
     */
    public function delete(string $path, callable $callback): void
    {
        $this->addRoute('DELETE', $path, $callback);
    }

    /**
     * Adiciona rota ao array de rotas
     */
    private function addRoute(string $metodo, string $path, callable $callback): void
    {
        $this->routes[] = [
            'metodo' => $metodo,
            'path' => $path,
            'callback' => $callback
        ];
    }

    /**
     * Extrai parâmetros da URI (ex: /desafios/1 -> ['id' => 1])
     */
    private function extractParams(string $pattern, string $uri): array
    {
        $params = [];
        $patternParts = explode('/', trim($pattern, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        foreach ($patternParts as $index => $part) {
            if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $uriParts[$index] ?? null;
            }
        }

        return $params;
    }

    /**
     * Verifica se URI corresponde ao padrão da rota
     */
    private function matchRoute(string $pattern, string $uri): bool
    {
        // Converte {id} para regex \d+
        $regex = preg_replace('/\{[a-zA-Z]+\}/', '(\d+)', $pattern);
        $regex = '#^' . $regex . '$#';

        return preg_match($regex, $uri) === 1;
    }

    /**
     * Executa o roteamento
     */
    public function resolve(): void
    {
        foreach ($this->routes as $route) {
            if ($route['metodo'] === $this->metodo && $this->matchRoute($route['path'], $this->uri)) {
                $params = $this->extractParams($route['path'], $this->uri);
                call_user_func_array($route['callback'], $params);
                return;
            }
        }

        // Rota não encontrada
        $this->notFound();
    }

    /**
     * Resposta 404
     */
    private function notFound(): void
    {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Rota não encontrada',
            'dados' => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
