<?php

// Desabilitar exibição de erros no output (apenas log)
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\DesafioController;
use App\Controller\AuthController;
use App\Middleware\AuthMiddleware;

// Carregar variáveis de ambiente
try {
    $envPath = __DIR__ . '/..';

    // Verificar se o arquivo .env existe
    if (!file_exists($envPath . '/.env')) {
        error_log("AVISO: Arquivo .env não encontrado em: " . $envPath);
        error_log("Usando valores padrão de configuração. Copie .env.example para .env e configure.");
    }

    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->safeLoad(); // Usa safeLoad ao invés de load para não dar erro se .env não existir
} catch (Exception $e) {
    error_log("Erro ao carregar .env: " . $e->getMessage());
    // Continua a execução com valores padrão
}

// Configurar headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obter método HTTP e URI
$metodo = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/index.php', '', $uri);
$uri = rtrim($uri, '/');

// Função auxiliar para extrair ID da URI
function obterIdDaUri(string $uri, string $prefixo): ?int
{
    $partes = explode('/', $uri);
    $indice = array_search($prefixo, $partes);

    if ($indice !== false && isset($partes[$indice + 1])) {
        return (int) $partes[$indice + 1];
    }

    return null;
}

// Roteamento
try {
    // ==========================================
    // ROTAS PÚBLICAS (sem autenticação)
    // ==========================================

    // POST /login - Autenticação
    if ($metodo === 'POST' && $uri === '/login') {
        $controller = new AuthController();
        $controller->login();
        exit;
    }

    // ==========================================
    // ROTAS PROTEGIDAS (com autenticação JWT)
    // ==========================================

    // Validar token JWT para todas as rotas abaixo
    $middleware = new AuthMiddleware();
    $middleware->validar();

    // POST /desafios - Criar desafio
    if ($metodo === 'POST' && $uri === '/desafios') {
        $controller = new DesafioController();
        $controller->criar();
        exit;
    }

    // GET /desafios - Listar todos os desafios
    if ($metodo === 'GET' && $uri === '/desafios') {
        $controller = new DesafioController();
        $controller->listarTodos();
        exit;
    }

    // GET /desafios/{id} - Buscar desafio por ID
    if ($metodo === 'GET' && preg_match('#^/desafios/\d+$#', $uri)) {
        $id = obterIdDaUri($uri, 'desafios');
        $controller = new DesafioController();
        $controller->buscarPorId($id);
        exit;
    }

    // PUT /desafios/{id} - Atualizar desafio
    if ($metodo === 'PUT' && preg_match('#^/desafios/\d+$#', $uri)) {
        $id = obterIdDaUri($uri, 'desafios');
        $controller = new DesafioController();
        $controller->atualizar($id);
        exit;
    }

    // DELETE /desafios/{id} - Excluir desafio
    if ($metodo === 'DELETE' && preg_match('#^/desafios/\d+$#', $uri)) {
        $id = obterIdDaUri($uri, 'desafios');
        $controller = new DesafioController();
        $controller->excluir($id);
        exit;
    }

    // Rota não encontrada
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Rota não encontrada',
        'dados' => null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
