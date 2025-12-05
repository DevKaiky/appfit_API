<?php

/**
 * Script de inicialização do banco de dados
 * Execute este arquivo após importar o schema.sql
 *
 * Como usar:
 * php database/seed.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Conectado ao banco de dados...\n";

    // Limpar dados anteriores
    $pdo->exec("DELETE FROM progresso");
    $pdo->exec("DELETE FROM desafios");
    $pdo->exec("DELETE FROM usuarios");
    echo "Tabelas limpas.\n";

    // Gerar hash correto na hora da execução
    $senhaPadrao = '123456';
    $hashSenha = password_hash($senhaPadrao, PASSWORD_DEFAULT);

    echo "Hash gerado: " . substr($hashSenha, 0, 30) . "...\n";

    // Inserir usuários
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, senha) VALUES
        ('Admin Teste', 'admin@appfit.com', ?),
        ('João Silva', 'joao@email.com', ?),
        ('Maria Santos', 'maria@email.com', ?)
    ");
    $stmt->execute([$hashSenha, $hashSenha, $hashSenha]);
    echo "Usuários criados: 3\n";

    // Inserir desafios
    $pdo->exec("
        INSERT INTO desafios (titulo, descricao, data_inicio, data_fim, nivel_dificuldade, recompensa, criado_por) VALUES
        ('30 Dias de Corrida', 'Correr pelo menos 5km todos os dias durante 30 dias consecutivos', '2025-12-01', '2025-12-30', 'Intermediario', 'Medalha Bronze + 100 pontos', 1),
        ('Desafio Flexibilidade', 'Alongamentos diários por 15 minutos durante 21 dias', '2025-12-01', '2025-12-21', 'Iniciante', 'Badge Flexível', 1),
        ('100 Flexões em 30 Dias', 'Progredir de 10 até 100 flexões em um único treino ao longo de 30 dias', '2025-12-01', '2025-12-30', 'Avancado', 'Troféu Força + 200 pontos', 1),
        ('Maratona de Yoga', 'Praticar yoga 5 vezes por semana durante 8 semanas', '2025-12-01', '2026-01-26', 'Intermediario', 'Certificado Digital', 2)
    ");
    echo "Desafios criados: 4\n";

    // Inserir progresso
    $pdo->exec("
        INSERT INTO progresso (usuario_id, desafio_id, progresso_percentual, status) VALUES
        (2, 1, 45.50, 'Em Andamento'),
        (2, 2, 100.00, 'Concluido'),
        (3, 1, 20.00, 'Em Andamento'),
        (3, 3, 15.00, 'Em Andamento')
    ");
    echo "Progressos criados: 4\n";

    echo "\n✓ Banco de dados inicializado com sucesso!\n";
    echo "\nCredenciais de teste:\n";
    echo "- admin@appfit.com / 123456\n";
    echo "- joao@email.com / 123456\n";
    echo "- maria@email.com / 123456\n";

} catch (PDOException $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
