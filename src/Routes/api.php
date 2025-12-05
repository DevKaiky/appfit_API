<?php

use App\Routes\Router;
use App\Controller\AuthController;
use App\Controller\DesafioController;
use App\Middleware\AuthMiddleware;

/**
 * Arquivo de definição de rotas
 * Todas as rotas da API devem ser registradas aqui
 */

$router = new Router();

// ==========================================
// ROTAS PÚBLICAS (sem autenticação)
// ==========================================

/**
 * POST /login
 * Autenticação de usuário
 * Body: { "email": "...", "senha": "..." }
 */
$router->post('/login', function() {
    $controller = new AuthController();
    $controller->login();
});

// ==========================================
// ROTAS PROTEGIDAS (com autenticação JWT)
// ==========================================

/**
 * GET /desafios
 * Lista todos os desafios
 * Requer: Authorization: Bearer {token}
 */
$router->get('/desafios', function() {
    $middleware = new AuthMiddleware();
    $middleware->validar();

    $controller = new DesafioController();
    $controller->listarTodos();
});

/**
 * GET /desafios/{id}
 * Busca desafio específico por ID
 * Requer: Authorization: Bearer {token}
 */
$router->get('/desafios/{id}', function($id) {
    $middleware = new AuthMiddleware();
    $middleware->validar();

    $controller = new DesafioController();
    $controller->buscarPorId((int)$id);
});

/**
 * POST /desafios
 * Cria novo desafio
 * Requer: Authorization: Bearer {token}
 * Body: { "titulo": "...", "descricao": "...", ... }
 */
$router->post('/desafios', function() {
    $middleware = new AuthMiddleware();
    $middleware->validar();

    $controller = new DesafioController();
    $controller->criar();
});

/**
 * PUT /desafios/{id}
 * Atualiza desafio existente
 * Requer: Authorization: Bearer {token}
 * Body: { "titulo": "...", "descricao": "...", ... }
 */
$router->put('/desafios/{id}', function($id) {
    $middleware = new AuthMiddleware();
    $middleware->validar();

    $controller = new DesafioController();
    $controller->atualizar((int)$id);
});

/**
 * DELETE /desafios/{id}
 * Remove desafio (soft delete)
 * Requer: Authorization: Bearer {token}
 */
$router->delete('/desafios/{id}', function($id) {
    $middleware = new AuthMiddleware();
    $middleware->validar();

    $controller = new DesafioController();
    $controller->excluir((int)$id);
});

return $router;
