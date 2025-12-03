<?php

namespace App\DAO;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Classe DesafioDAO
 * Responsável pelo acesso direto aos dados da entidade Desafios no banco de dados
 */
class DesafioDAO
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    /**
     * Cria um novo desafio no banco de dados
     *
     * @param array $dados
     * @return int ID do desafio criado
     * @throws PDOException
     */
    public function criar(array $dados): int
    {
        try {
            $sql = "INSERT INTO desafios (titulo, descricao, data_inicio, data_fim, nivel_dificuldade, recompensa, criado_por)
                    VALUES (:titulo, :descricao, :data_inicio, :data_fim, :nivel_dificuldade, :recompensa, :criado_por)";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':titulo', $dados['titulo']);
            $stmt->bindParam(':descricao', $dados['descricao']);
            $stmt->bindParam(':data_inicio', $dados['data_inicio']);
            $stmt->bindParam(':data_fim', $dados['data_fim']);
            $stmt->bindParam(':nivel_dificuldade', $dados['nivel_dificuldade']);
            $stmt->bindParam(':recompensa', $dados['recompensa']);
            $stmt->bindParam(':criado_por', $dados['criado_por']);

            $stmt->execute();

            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao criar desafio: " . $e->getMessage());
            throw new PDOException("Erro ao criar desafio no banco de dados");
        }
    }

    /**
     * Lista todos os desafios ativos
     *
     * @return array
     * @throws PDOException
     */
    public function listarTodos(): array
    {
        try {
            $sql = "SELECT d.*, u.nome as criador_nome
                    FROM desafios d
                    LEFT JOIN usuarios u ON d.criado_por = u.id
                    WHERE d.ativo = 1
                    ORDER BY d.data_criacao DESC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao listar desafios: " . $e->getMessage());
            throw new PDOException("Erro ao buscar desafios no banco de dados");
        }
    }

    /**
     * Busca um desafio específico por ID
     *
     * @param int $id
     * @return array|null
     * @throws PDOException
     */
    public function buscarPorId(int $id): ?array
    {
        try {
            $sql = "SELECT d.*, u.nome as criador_nome
                    FROM desafios d
                    LEFT JOIN usuarios u ON d.criado_por = u.id
                    WHERE d.id = :id AND d.ativo = 1";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch();
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar desafio por ID: " . $e->getMessage());
            throw new PDOException("Erro ao buscar desafio no banco de dados");
        }
    }

    /**
     * Atualiza um desafio existente
     *
     * @param int $id
     * @param array $dados
     * @return bool
     * @throws PDOException
     */
    public function atualizar(int $id, array $dados): bool
    {
        try {
            $sql = "UPDATE desafios
                    SET titulo = :titulo,
                        descricao = :descricao,
                        data_inicio = :data_inicio,
                        data_fim = :data_fim,
                        nivel_dificuldade = :nivel_dificuldade,
                        recompensa = :recompensa
                    WHERE id = :id AND ativo = 1";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':titulo', $dados['titulo']);
            $stmt->bindParam(':descricao', $dados['descricao']);
            $stmt->bindParam(':data_inicio', $dados['data_inicio']);
            $stmt->bindParam(':data_fim', $dados['data_fim']);
            $stmt->bindParam(':nivel_dificuldade', $dados['nivel_dificuldade']);
            $stmt->bindParam(':recompensa', $dados['recompensa']);

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar desafio: " . $e->getMessage());
            throw new PDOException("Erro ao atualizar desafio no banco de dados");
        }
    }

    /**
     * Exclui (soft delete) um desafio
     *
     * @param int $id
     * @return bool
     * @throws PDOException
     */
    public function excluir(int $id): bool
    {
        try {
            $sql = "UPDATE desafios SET ativo = 0 WHERE id = :id";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao excluir desafio: " . $e->getMessage());
            throw new PDOException("Erro ao excluir desafio no banco de dados");
        }
    }

    /**
     * Verifica se um desafio existe e está ativo
     *
     * @param int $id
     * @return bool
     * @throws PDOException
     */
    public function existe(int $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM desafios WHERE id = :id AND ativo = 1";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch();
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar existência de desafio: " . $e->getMessage());
            throw new PDOException("Erro ao verificar desafio no banco de dados");
        }
    }
}
