<?php

namespace App\Controller;

use App\Service\AuthService;
use Exception;

/**
 * Classe AuthController
 * Responsável por processar requisições de autenticação
 */
class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Realiza o login do usuário
     * POST /login
     */
    public function login(): void
    {
        try {
            $dados = $this->obterDadosRequisicao();

            if (!isset($dados['email']) || !isset($dados['senha'])) {
                throw new Exception("E-mail e senha são obrigatórios");
            }

            $resultado = $this->authService->login($dados['email'], $dados['senha']);

            $this->responderSucesso($resultado, "Login realizado com sucesso");
        } catch (Exception $e) {
            $this->responderErro($e->getMessage(), 401);
        }
    }

    /**
     * Obtém os dados da requisição JSON
     *
     * @return array
     */
    private function obterDadosRequisicao(): array
    {
        $dados = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON inválido na requisição");
        }

        return $dados ?? [];
    }

    /**
     * Responde com sucesso
     *
     * @param mixed $dados
     * @param string $mensagem
     * @param int $codigo
     */
    private function responderSucesso($dados, string $mensagem, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'status' => 'sucesso',
            'mensagem' => $mensagem,
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }

    /**
     * Responde com erro
     *
     * @param string $mensagem
     * @param int $codigo
     */
    private function responderErro(string $mensagem, int $codigo = 400): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'erro' => $mensagem,
            'dado' => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }
}
