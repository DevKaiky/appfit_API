<?php

namespace App\DAO;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Classe UsuarioDAO
 * Responsável pelo acesso direto aos dados da entidade Usuários no banco de dados
 */
class UsuarioDAO
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    /**
     * Busca um usuário por e-mail
     *
     * @param string $email
     * @return array|null
     * @throws PDOException
     */
    public function buscarPorEmail(string $email): ?array
    {
        try {
            $sql = "SELECT id, nome, email, senha, ativo
                    FROM usuarios
                    WHERE email = :email AND ativo = 1";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $resultado = $stmt->fetch();
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário por email: " . $e->getMessage());
            throw new PDOException("Erro ao buscar usuário no banco de dados");
        }
    }

    /**
     * Busca um usuário por ID
     *
     * @param int $id
     * @return array|null
     * @throws PDOException
     */
    public function buscarPorId(int $id): ?array
    {
        try {
            $sql = "SELECT id, nome, email, data_cadastro, ativo
                    FROM usuarios
                    WHERE id = :id AND ativo = 1";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch();
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário por ID: " . $e->getMessage());
            throw new PDOException("Erro ao buscar usuário no banco de dados");
        }
    }

    /**
     * Cria um novo usuário
     *
     * @param array $dados
     * @return int ID do usuário criado
     * @throws PDOException
     */
    public function criar(array $dados): int
    {
        try {
            $sql = "INSERT INTO usuarios (nome, email, senha)
                    VALUES (:nome, :email, :senha)";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':email', $dados['email']);
            $stmt->bindParam(':senha', $dados['senha']);

            $stmt->execute();

            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            throw new PDOException("Erro ao criar usuário no banco de dados");
        }
    }
}
