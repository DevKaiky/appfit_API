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
        $this->chaveSecreta = $_ENV['JWT_SECRET'] ?? 'sua_chave_secreta_aqui_mude_em_producao';
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
            error_log("=== INÍCIO DO LOGIN ===");
            error_log("Email recebido: {$email}");
            error_log("Senha recebida (length): " . strlen($senha));

            // Validar dados de entrada
            if (empty($email) || empty($senha)) {
                error_log("ERRO: E-mail ou senha vazios");
                throw new Exception("E-mail e senha são obrigatórios");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log("ERRO: E-mail com formato inválido: {$email}");
                throw new Exception("E-mail inválido");
            }

            // Buscar usuário no banco
            error_log("Buscando usuário no banco de dados...");
            $usuario = $this->usuarioDAO->buscarPorEmail($email);

            if (!$usuario) {
                error_log("ERRO: Usuário não encontrado para o email: {$email}");
                throw new Exception("E-mail ou senha inválidos");
            }

            error_log("Usuário encontrado - ID: {$usuario['id']}, Nome: {$usuario['nome']}, Ativo: {$usuario['ativo']}");
            error_log("Hash no banco (30 primeiros chars): " . substr($usuario['senha'], 0, 30) . "...");

            // Verificar senha
            error_log("Verificando senha com password_verify...");
            $senhaCorreta = password_verify($senha, $usuario['senha']);
            error_log("Resultado password_verify: " . ($senhaCorreta ? 'TRUE (senha correta)' : 'FALSE (senha incorreta)'));

            if (!$senhaCorreta) {
                error_log("ERRO: Senha incorreta para o email: {$email}");

                // Log adicional para debug
                $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                error_log("Hash que seria gerado com a senha fornecida: {$novoHash}");
                error_log("Hash atual no banco: {$usuario['senha']}");

                throw new Exception("E-mail ou senha inválidos");
            }

            // Gerar token JWT
            error_log("Gerando token JWT...");
            $token = $this->gerarToken($usuario);
            error_log("Token gerado com sucesso");
            error_log("=== LOGIN BEM-SUCEDIDO ===");

            return [
                'usuario' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email']
                ],
                'token' => $token
            ];
        } catch (Exception $e) {
            error_log("ERRO no service de autenticação: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
