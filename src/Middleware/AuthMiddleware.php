<?php

namespace App\Middleware;

use App\Service\AuthService;
use Exception;

/**
 * Classe AuthMiddleware
 * Middleware para validar tokens JWT nas rotas protegidas
 */
class AuthMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Valida o token JWT da requisição
     * Se válido, adiciona o user_id ao $_SERVER para uso posterior
     * Se inválido, retorna erro e interrompe a execução
     */
    public function validar(): void
    {
        try {
            // Extrair token do header Authorization
            $token = AuthService::extrairTokenDoHeader();

            if (!$token) {
                $this->responderNaoAutorizado("Token não enviado");
            }

            // Validar token
            $decodificado = $this->authService->validarToken($token);

            // Adicionar dados do usuário ao $_SERVER para uso nos controllers
            $_SERVER['USER_ID'] = $decodificado->user_id;
            $_SERVER['USER_EMAIL'] = $decodificado->email;
        } catch (Exception $e) {
            $this->responderNaoAutorizado("Token inválido ou expirado");
        }
    }

    /**
     * Responde com erro de não autorizado e interrompe a execução
     *
     * @param string $mensagem
     */
    private function responderNaoAutorizado(string $mensagem): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'erro' => 'Acesso não autorizado',
            'dado' => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }
}
