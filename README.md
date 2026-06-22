# VHR API

API REST para gestão de recursos humanos. Digitaliza processos de RH como cadastro de funcionários, lançamento de pontos e contratos — substituindo planilhas por um sistema estruturado multi-empresa.

---

## Requisitos

| Dependência | Versão mínima |
|---|---|
| PHP | 8.3+ |
| Composer | 2.x |
| Docker | 24+ |
| Docker Compose | 2.x |

> Os testes sobem um container PostgreSQL automaticamente via **Testcontainers**. Docker precisa estar rodando durante a execução dos testes.

---

## Stack

- **Laravel** 13 — framework base
- **PostgreSQL** 17 — banco de dados principal
- **Redis** — cache e sessões
- **Laravel Sanctum** — autenticação via sessão/cookie
- **Spatie Laravel Permission** — controle de acesso baseado em roles/permissões por empresa
- **Spatie Laravel Data** — DTOs tipados
- **Spatie Laravel Query Builder** — filtros nas listagens
- **nwidart/laravel-modules** — arquitetura modular

---

## Instalação

### 1. Clonar e instalar dependências

```bash
git clone <repo-url> vhr-api
cd vhr-api
composer install
```

### 2. Configurar ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` com as credenciais do banco (ou use os valores padrão do Docker Compose):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=central
DB_USERNAME=admin
DB_PASSWORD=admin

SESSION_DRIVER=database
CACHE_STORE=database
```

### 3. Subir a infraestrutura

```bash
docker compose up -d
```

Isso sobe: **PostgreSQL** (porta 5432), **Redis** (porta 6379) e **Nginx + PHP-FPM** (porta 80).

### 4. Migrar e popular o banco

```bash
# Dentro do container
docker exec -it vhr-api php artisan migrate --force

# Ou localmente (com PHP instalado)
php artisan migrate --force
```

---

## Desenvolvimento local

```bash
composer run dev
```

Sobe em paralelo: servidor PHP, queue worker, log watcher e Vite.

---

## Testes

Os testes usam **Testcontainers** — um container PostgreSQL é criado automaticamente por suite, isolando completamente o banco de teste do banco de desenvolvimento. **Docker precisa estar rodando.**

```bash
# Rodar todos os testes
php artisan test

# Ou via composer
composer run test

# Rodar apenas um módulo
php artisan test --filter=EmployeeTest
php artisan test --filter=UserTest
php artisan test --filter=CompanyTest
php artisan test --filter=AuthTest
```

---

## Estrutura de Módulos

```
Modules/
├── Auth/       — login, logout, seleção de empresa
├── Core/       — usuários, empresas, pessoas
└── Job/        — funcionários, carga horária
```

Cada módulo é autônomo: controllers, services, repositories, models, migrations, factories e testes próprios.

---

## Autenticação

O sistema usa autenticação via **sessão** (cookie) com Sanctum.

```
POST /auth/login              — autenticação com email e senha
POST /auth/select-company     — seleciona a empresa ativa (multi-empresa)
GET  /auth/me                 — dados do usuário autenticado
POST /auth/logout             — encerrar sessão
```

Após o login, todas as requisições às rotas protegidas precisam da empresa ativa definida na sessão. Se o usuário pertence a apenas uma empresa, ela é selecionada automaticamente.

---

## Controle de Acesso

Permissões e roles são **isoladas por empresa** via Spatie Permission com teams. Cada usuário tem um vínculo `UserCompany` por empresa, e as permissões são atribuídas a esse vínculo — não diretamente ao usuário.

Roles disponíveis: `owner`, `humanResource`, `accountant`.

---

## Licença

Proprietário — todos os direitos reservados.
