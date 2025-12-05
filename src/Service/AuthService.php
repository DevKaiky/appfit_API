<?php

namespace App\Service;

use App\DAO\UsuarioDAO;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * Classe AuthService
 * Responsável pela autenticação de usuários e geração/validação de tokens JWT
 */
class AuthService
{
    private UsuarioDAO $usuarioDAO;
    private string $chaveSecreta;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();

        // Verificar se JWT_SECRET está configurado
        if (empty($_ENV['JWT_SECRET'])) {
            throw new Exception("JWT_SECRET não configurado no arquivo .env");
        }

        $this->chaveSecreta = $_ENV['JWT_SECRET'];
    }

    /**
     * Realiza o login do usuário e retorna um token JWT
     *
     * @param string $email
     * @param string $senha
     * @return array
     * @throws Exception
     */
    public function login(string $email, string $senha): array
    {
        try {
            // Validar dados de entrada
            if (empty($email) || empty($senha)) {
                throw new Exception("E-mail e senha são obrigatórios");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("E-mail inválido");
            }

            // Buscar usuário no banco
            $usuario = $this->usuarioDAO->buscarPorEmail($email);

            if (!$usuario) {
                throw new Exception("E-mail ou senha inválidos");
            }

            // Verificar senha
            if (!password_verify($senha, $usuario['senha'])) {
                throw new Exception("E-mail ou senha inválidos");
            }

            // Gerar token JWT
            $token = $this->gerarToken($usuario);

            return [
                'usuario' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email']
                ],
                'token' => $token
            ];
        } catch (Exception $e) {
            error_log("Erro no service de autenticação: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gera um token JWT para o usuário
     *
     * @param array $usuario
     * @return string
     */
    private function gerarToken(array $usuario): string
    {
        $payload = [
            'iss' => 'localhost',                      // Emissor do token
            'aud' => 'localhost',                      // Destinatário
            'iat' => time(),                           // Data de emissão
            'exp' => time() + 3600,                    // Expiração (1 hora)
            'user_id' => $usuario['id'],               // ID do usuário
            'email' => $usuario['email']               // E-mail do usuário
        ];

        return JWT::encode($payload, $this->chaveSecreta, 'HS256');
    }

    /**
     * Valida um token JWT
     *
     * @param string $token
     * @return object
     * @throws Exception
     */
    public function validarToken(string $token): object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->chaveSecreta, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            error_log("Erro ao validar token: " . $e->getMessage());
            throw new Exception("Token inválido ou expirado");
        }
    }

    /**
     * Extrai o token do cabeçalho Authorization
     *
     * @return string|null
     */
    public static function extrairTokenDoHeader(): ?string
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authorization = $headers['Authorization'];

        // Formato esperado: "Bearer TOKEN_AQUI"
        if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
