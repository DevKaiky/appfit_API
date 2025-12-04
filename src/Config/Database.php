<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Classe Database
 * Responsável pela conexão com o banco de dados MySQL usando PDO
 */
class Database
{
    private static ?PDO $connection = null;

    /**
     * Retorna uma conexão PDO ativa com o banco de dados
     * Implementa o padrão Singleton para garantir uma única conexão
     *
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $dbname = $_ENV['DB_NAME'] ?? 'appfit_db';
                $username = $_ENV['DB_USER'] ?? 'root';
                $password = $_ENV['DB_PASS'] ?? '';
                $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

                $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

                error_log("Tentando conectar ao banco: host={$host}, dbname={$dbname}, user={$username}");

                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                error_log("Conexão com banco de dados estabelecida com sucesso");
            } catch (PDOException $e) {
                $errorMsg = "Erro de conexão com o banco de dados: " . $e->getMessage();
                $errorMsg .= " | Host: {$host}, Database: {$dbname}, User: {$username}";
                error_log($errorMsg);

                // Mensagens de erro mais específicas
                if (strpos($e->getMessage(), 'Access denied') !== false) {
                    throw new PDOException("Acesso negado ao banco de dados. Verifique as credenciais no arquivo .env");
                } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                    throw new PDOException("Banco de dados '{$dbname}' não encontrado. Execute o script database/schema.sql");
                } elseif (strpos($e->getMessage(), "Can't connect") !== false || strpos($e->getMessage(), 'Connection refused') !== false) {
                    throw new PDOException("Não foi possível conectar ao servidor MySQL. Verifique se o MySQL está em execução");
                } else {
                    throw new PDOException("Não foi possível conectar ao banco de dados: " . $e->getMessage());
                }
            }
        }

        return self::$connection;
    }

    /**
     * Fecha a conexão com o banco de dados
     */
    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}
