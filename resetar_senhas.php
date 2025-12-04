<?php
/**
 * Script para Resetar Senhas dos Usuários
 * Execute: php resetar_senhas.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (Exception $e) {
    echo "Erro ao carregar .env: " . $e->getMessage() . "\n";
}

use App\Config\Database;

echo "====================================\n";
echo "RESETAR SENHAS DOS USUÁRIOS\n";
echo "====================================\n\n";

try {
    $conn = Database::getConnection();
    echo "✓ Conectado ao banco de dados\n\n";

    // Senha padrão que será configurada
    $senhaTexto = '123456';
    $senhaHash = password_hash($senhaTexto, PASSWORD_DEFAULT);

    echo "Senha que será configurada: {$senhaTexto}\n";
    echo "Hash gerado: {$senhaHash}\n\n";

    // Verificar se o hash funciona
    if (password_verify($senhaTexto, $senhaHash)) {
        echo "✓ Hash verificado com sucesso\n\n";
    } else {
        echo "✗ ERRO: Hash não pode ser verificado!\n";
        exit(1);
    }

    // Listar usuários atuais
    $stmt = $conn->query("SELECT id, nome, email FROM usuarios");
    $usuarios = $stmt->fetchAll();

    if (empty($usuarios)) {
        echo "Nenhum usuário encontrado no banco!\n";
        exit(0);
    }

    echo "Usuários que terão a senha resetada para '{$senhaTexto}':\n";
    foreach ($usuarios as $user) {
        echo "  - ID: {$user['id']}, Nome: {$user['nome']}, Email: {$user['email']}\n";
    }

    echo "\n";
    echo "Deseja continuar? (s/n): ";
    $resposta = trim(fgets(STDIN));

    if (strtolower($resposta) !== 's') {
        echo "Operação cancelada.\n";
        exit(0);
    }

    // Atualizar senhas
    $stmt = $conn->prepare("UPDATE usuarios SET senha = :senha");
    $stmt->bindParam(':senha', $senhaHash);
    $stmt->execute();

    $affected = $stmt->rowCount();
    echo "\n✓ {$affected} senha(s) atualizada(s) com sucesso!\n\n";

    // Verificar se as senhas foram atualizadas corretamente
    echo "Verificando senhas atualizadas...\n";
    foreach ($usuarios as $user) {
        $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();
        $resultado = $stmt->fetch();

        if ($resultado && password_verify($senhaTexto, $resultado['senha'])) {
            echo "  ✓ {$user['email']}: Senha configurada corretamente\n";
        } else {
            echo "  ✗ {$user['email']}: ERRO ao configurar senha!\n";
        }
    }

    echo "\n====================================\n";
    echo "CONCLUÍDO!\n";
    echo "Todos os usuários agora têm a senha: {$senhaTexto}\n";
    echo "====================================\n";

} catch (Exception $e) {
    echo "✗ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
