# API RESTful - Aplicativo de Desafios Fitness

**Trabalho de Aplicações para Internet - Parte II**

API desenvolvida em PHP para gerenciar desafios de exercícios físicos.

---

## Sobre o Projeto

Sistema de gerenciamento de desafios fitness onde usuários podem criar, visualizar, editar e excluir desafios de exercícios.

**Entidade Principal:** Desafios
- Título, descrição, datas de início/fim
- Nível de dificuldade
- Recompensas
- Usuário criador

---

## Tecnologias

- PHP 8+
- MySQL
- PDO
- JWT (autenticação)
- Composer

---

## Instalação

### 1. Clonar o repositório

```bash
git clone <url-do-repositorio>
cd appFit_API
```

### 2. Instalar dependências

```bash
composer install
```

### 3. Configurar banco de dados

Copie `.env.example` para `.env` e configure:

```env
DB_HOST=localhost
DB_NAME=appfit_db
DB_USER=root
DB_PASS=
```

Execute o script SQL:

```bash
mysql -u root -p < database/schema.sql
```

### 4. Configurar chave JWT

Edite o arquivo `.env` e defina uma chave secreta:

```env
JWT_SECRET=sua_chave_secreta_aqui
```

### 5. Rodar o servidor

```bash
cd public
php -S localhost:8000
```

Acesse: http://localhost:8000

---

## Estrutura do Banco de Dados

### Tabela: usuarios
- id, nome, email, senha, data_cadastro, ativo

### Tabela: desafios
- id, titulo, descricao, data_inicio, data_fim
- nivel_dificuldade, recompensa, criado_por
- data_criacao, ativo

### Tabela: progresso
- id, usuario_id, desafio_id
- progresso_percentual, status
- data_participacao

---

## Endpoints da API

### Autenticação

**POST** `/login`
- Retorna token JWT para acesso às rotas protegidas

```json
{
  "email": "admin@appfit.com",
  "senha": "123456"
}
```

### Desafios (requerem autenticação)

**GET** `/desafios` - Lista todos os desafios

**GET** `/desafios/{id}` - Busca desafio específico

**POST** `/desafios` - Cria novo desafio

**PUT** `/desafios/{id}` - Atualiza desafio

**DELETE** `/desafios/{id}` - Remove desafio

---

## Autenticação JWT

Todas as rotas de desafios requerem token JWT no header:

```
Authorization: Bearer SEU_TOKEN
```

O token é obtido através do endpoint `/login` e expira em 1 hora.

---

## Testando

### Página de Testes

Acesse: http://localhost:8000/teste.html

### Exemplo com cURL

**Login:**
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@appfit.com","senha":"123456"}'
```

**Listar desafios:**
```bash
curl -X GET http://localhost:8000/desafios \
  -H "Authorization: Bearer SEU_TOKEN"
```

---

## Estrutura de Pastas

```
appFit_API/
├── database/
│   └── schema.sql
├── public/
│   ├── index.php
│   └── teste.html
├── src/
│   ├── Config/
│   ├── Controller/
│   ├── DAO/
│   ├── Middleware/
│   └── Service/
├── .env
├── composer.json
└── README.md
```

---

## Tratamento de Erros

Todas as respostas são em JSON padronizado:

**Sucesso:**
```json
{
  "status": "sucesso",
  "mensagem": "Operação realizada",
  "dados": { ... }
}
```

**Erro:**
```json
{
  "status": "erro",
  "mensagem": "Descrição do erro",
  "dados": null
}
```

Todos os métodos DAO e Service possuem try/catch para tratamento de exceções.

---

## Credenciais de Teste

| Email | Senha |
|-------|-------|
| admin@appfit.com | 123456 |
| joao@email.com | 123456 |
| maria@email.com | 123456 |

---

## Arquitetura MVC

**Controller:** Recebe requisições HTTP e retorna JSON

**Service:** Regras de negócio e validações

**DAO:** Acesso ao banco de dados via PDO

**Middleware:** Validação de autenticação JWT

---

## Requisitos Atendidos

Padrão MVC com separação em camadas

CRUD completo para entidade Desafios

Autenticação JWT em rotas protegidas

Tratamento de erros com try/catch

Validações de dados no Service

Mensagens de erro padronizadas

Banco de dados MySQL com 3 tabelas relacionadas

---

## Autor

Trabalho desenvolvido para a disciplina de Aplicações para Internet.
