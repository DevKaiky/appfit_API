<?php
/**
 * Script de Diagnóstico de Login
 * Execute: php diagnostico_login.php
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
echo "DIAGNÓSTICO DE LOGIN\n";
echo "====================================\n\n";

// 1. Testar conexão com banco
echo "1. Testando conexão com banco de dados...\n";
try {
    $conn = Database::getConnection();
    echo "   ✓ Conexão estabelecida com sucesso!\n\n";
} catch (Exception $e) {
    echo "   ✗ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Verificar usuários no banco
echo "2. Verificando usuários cadastrados...\n";
try {
    $stmt = $conn->query("SELECT id, nome, email, ativo, SUBSTRING(senha, 1, 20) as senha_preview FROM usuarios");
    $usuarios = $stmt->fetchAll();

    if (empty($usuarios)) {
        echo "   ✗ NENHUM usuário encontrado no banco!\n";
        echo "   Execute: mysql -u root < database/schema.sql\n\n";
        exit(1);
    }

    echo "   ✓ Encontrados " . count($usuarios) . " usuário(s):\n";
    foreach ($usuarios as $user) {
        echo "     - ID: {$user['id']}, Email: {$user['email']}, Nome: {$user['nome']}, Ativo: {$user['ativo']}\n";
        echo "       Senha (preview): {$user['senha_preview']}...\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ ERRO ao buscar usuários: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. Testar hash de senha
echo "3. Testando verificação de senha...\n";
$senhaTexto = '123456';
$senhaHashEsperada = '$2y$10$PPrUuBgZORh/RlYzTy5/R7KVU9nFvO2gN/dGnIHuN0LRbLqwkKeAW';

echo "   Senha texto: {$senhaTexto}\n";
echo "   Hash esperado: {$senhaHashEsperada}\n";

if (password_verify($senhaTexto, $senhaHashEsperada)) {
    echo "   ✓ password_verify() funcionando corretamente!\n\n";
} else {
    echo "   ✗ ERRO: password_verify() falhou!\n";
    echo "   Pode ser problema de versão do PHP ou algoritmo de hash.\n\n";
}

// 4. Testar login completo
echo "4. Testando login completo com email: admin@appfit.com\n";
try {
    $email = 'admin@appfit.com';
    $senha = '123456';

    $stmt = $conn->prepare("SELECT id, nome, email, senha, ativo FROM usuarios WHERE email = :email AND ativo = 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch();

    if (!$usuario) {
        echo "   ✗ ERRO: Usuário não encontrado ou inativo!\n";
        echo "   Email buscado: {$email}\n\n";
    } else {
        echo "   ✓ Usuário encontrado: {$usuario['nome']}\n";
        echo "   Hash no banco: " . substr($usuario['senha'], 0, 30) . "...\n";

        if (password_verify($senha, $usuario['senha'])) {
            echo "   ✓ SENHA CORRETA! Login deveria funcionar.\n\n";
        } else {
            echo "   ✗ SENHA INCORRETA!\n";
            echo "   Possíveis causas:\n";
            echo "   - A senha no banco não é '123456'\n";
            echo "   - O hash no banco está corrompido\n";
            echo "   - Problema com encoding de caracteres\n\n";

            // Tentar gerar novo hash
            echo "   Gerando novo hash para senha '123456':\n";
            $novoHash = password_hash($senha, PASSWORD_DEFAULT);
            echo "   Novo hash: {$novoHash}\n";
            echo "   Execute este SQL para corrigir:\n";
            echo "   UPDATE usuarios SET senha = '{$novoHash}' WHERE email = '{$email}';\n\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ ERRO: " . $e->getMessage() . "\n\n";
}

// 5. Verificar variáveis de ambiente
echo "5. Verificando variáveis de ambiente...\n";
echo "   DB_HOST: " . ($_ENV['DB_HOST'] ?? 'não definido') . "\n";
echo "   DB_NAME: " . ($_ENV['DB_NAME'] ?? 'não definido') . "\n";
echo "   DB_USER: " . ($_ENV['DB_USER'] ?? 'não definido') . "\n";
echo "   DB_PASS: " . (isset($_ENV['DB_PASS']) ? '***' : 'não definido') . "\n";
echo "   JWT_SECRET: " . (isset($_ENV['JWT_SECRET']) ? substr($_ENV['JWT_SECRET'], 0, 10) . '...' : 'não definido') . "\n\n";

echo "====================================\n";
echo "DIAGNÓSTICO CONCLUÍDO\n";
echo "====================================\n";
