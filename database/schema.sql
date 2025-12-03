-- ===========================================
-- Script SQL - Aplicativo de Desafios Fitness
-- ===========================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS appfit_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE appfit_db;

-- ===========================================
-- Tabela: usuarios
-- ===========================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo TINYINT(1) DEFAULT 1,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Tabela: desafios
-- ===========================================
CREATE TABLE IF NOT EXISTS desafios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    nivel_dificuldade ENUM('Iniciante', 'Intermediario', 'Avancado', 'Extremo') NOT NULL DEFAULT 'Intermediario',
    recompensa VARCHAR(100),
    criado_por INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_nivel (nivel_dificuldade),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_data_fim (data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Tabela: progresso
-- ===========================================
CREATE TABLE IF NOT EXISTS progresso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    desafio_id INT NOT NULL,
    data_participacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progresso_percentual DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('Em Andamento', 'Concluido', 'Abandonado') DEFAULT 'Em Andamento',
    observacoes TEXT,
    data_conclusao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (desafio_id) REFERENCES desafios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participacao (usuario_id, desafio_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_desafio (desafio_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Dados de exemplo para testes
-- ===========================================

-- Inserir usuário de teste (senha: 123456)
-- Hash gerado com: password_hash('123456', PASSWORD_DEFAULT)
INSERT INTO usuarios (nome, email, senha) VALUES
('Admin Teste', 'admin@appfit.com', '$2y$10$PPrUuBgZORh/RlYzTy5/R7KVU9nFvO2gN/dGnIHuN0LRbLqwkKeAW'),
('João Silva', 'joao@email.com', '$2y$10$PPrUuBgZORh/RlYzTy5/R7KVU9nFvO2gN/dGnIHuN0LRbLqwkKeAW'),
('Maria Santos', 'maria@email.com', '$2y$10$PPrUuBgZORh/RlYzTy5/R7KVU9nFvO2gN/dGnIHuN0LRbLqwkKeAW');

-- Inserir desafios de exemplo
INSERT INTO desafios (titulo, descricao, data_inicio, data_fim, nivel_dificuldade, recompensa, criado_por) VALUES
('30 Dias de Corrida', 'Correr pelo menos 5km todos os dias durante 30 dias consecutivos', '2025-12-01', '2025-12-30', 'Intermediario', 'Medalha Bronze + 100 pontos', 1),
('Desafio Flexibilidade', 'Alongamentos diários por 15 minutos durante 21 dias', '2025-12-01', '2025-12-21', 'Iniciante', 'Badge Flexível', 1),
('100 Flexões em 30 Dias', 'Progredir de 10 até 100 flexões em um único treino ao longo de 30 dias', '2025-12-01', '2025-12-30', 'Avancado', 'Troféu Força + 200 pontos', 1),
('Maratona de Yoga', 'Praticar yoga 5 vezes por semana durante 8 semanas', '2025-12-01', '2026-01-26', 'Intermediario', 'Certificado Digital', 2);

-- Inserir progresso de exemplo
INSERT INTO progresso (usuario_id, desafio_id, progresso_percentual, status) VALUES
(2, 1, 45.50, 'Em Andamento'),
(2, 2, 100.00, 'Concluido'),
(3, 1, 20.00, 'Em Andamento'),
(3, 3, 15.00, 'Em Andamento');
