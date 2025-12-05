<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurar headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Carregar e executar rotas
try {
    $router = require_once __DIR__ . '/../src/Routes/api.php';
    $router->resolve();
} catch (Exception $e) {
    // Erro genérico
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro interno do servidor',
        'dados' => null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    error_log("Erro no roteamento: " . $e->getMessage());
}
