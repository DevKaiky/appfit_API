# Configuração do Banco de Dados

## Instruções para nova instalação

### Passo 1: Importar estrutura
```bash
# Importe o schema.sql no phpMyAdmin ou via linha de comando:
mysql -u root -p < database/schema.sql
```

### Passo 2: Popular com dados de teste (RECOMENDADO)
```bash
# Execute o seed.php para gerar senhas com hash correto:
php database/seed.php
```

**Este método garante que os hashes sejam gerados na versão do PHP que você está usando.**

## Credenciais de teste

Após executar o seed.php, você terá:

| Email | Senha | Perfil |
|-------|-------|--------|
| admin@appfit.com | 123456 | Admin |
| joao@email.com | 123456 | Usuário |
| maria@email.com | 123456 | Usuário |

## Por que usar seed.php?

- ✅ Gera hash compatível com sua versão do PHP
- ✅ Evita problemas de hash corrompido
- ✅ Garante funcionamento em qualquer máquina
- ✅ Pode ser executado várias vezes (limpa e recria)

## Troubleshooting

### Erro 401 ao fazer login?

Provavelmente o hash da senha está incorreto. Execute:

```bash
php database/seed.php
```

Isso vai regenerar todos os usuários com hash correto.
