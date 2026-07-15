# VHR

Sistema de gestão de recursos humanos multi-empresa. Digitaliza processos de RH — cadastro de pessoas e funcionários, jornadas de trabalho e lançamento de ponto — substituindo planilhas por um sistema estruturado, com controle de acesso isolado por empresa.

---

## Documentação

| Documento | Conteúdo |
|---|---|
| [FLUXO.md](FLUXO.md) | Visão de ponta a ponta: módulos, ciclo de requisição, multi-empresa/autorização, modelo de dados e fluxos |
| [FRONTEND.md](FRONTEND.md) | Stack, arquitetura e workflow do front-end |
| [DER.dbml](DER.dbml) | Modelo de dados (DBML) |
| [Modules/Attendance/FLOW.md](Modules/Attendance/FLOW.md) | Detalhe do fluxo de ponto |

---

## Stack

### Back-end
- **Laravel 13** (PHP 8.3+) — framework base
- **PostgreSQL 17** — banco principal; um schema por módulo (`core.*`, `job.*`, `attendance.*`)
- **Redis** — cache, sessões e fila
- **Laravel Sanctum** — autenticação via sessão/cookie
- **Spatie Laravel Permission** — roles/permissões por empresa (teams)
- **Spatie Laravel Data** — DTOs tipados
- **Spatie Laravel Query Builder** — filtros nas listagens
- **nwidart/laravel-modules** — arquitetura modular
- **Resend** — envio de e-mail (recuperação de senha)

### Front-end
- **Inertia v3** — ponte servidor ↔ cliente
- **React 19** + **TypeScript**
- **Vite** + **Tailwind CSS v4**
- **shadcn/ui** (sobre base-ui) — design system
- **zustand** (estado de UI) · **Ziggy** (rotas tipadas)

> Front unificado (não modular): a modularidade vive no back-end; o front é uma view layer fina que só apresenta as props do Inertia. Detalhes em [FRONTEND.md](FRONTEND.md).

---

## Requisitos

| Dependência | Versão mínima |
|---|---|
| Docker + Docker Compose | 24+ / 2.x |
| PHP | 8.3+ |
| Composer | 2.x |
| Node | 20+ |
| pnpm | 9+ |

> **Ambiente:** PHP, PostgreSQL, Nginx e Redis rodam no **Docker**; Node/Vite roda no **host** (o container `app` não tem Node).
> Os testes sobem um PostgreSQL efêmero via **Testcontainers** — o Docker precisa estar rodando durante a execução.

---

## Instalação

### 1. Clonar e subir a infraestrutura

```bash
git clone <repo-url> vhr-api
cd vhr-api
docker compose up -d          # PostgreSQL (15432), Redis (16379), PHP-FPM + Nginx (18080)
```

### 2. Instalar dependências

```bash
docker compose exec -u root app composer install   # back-end (dentro do container)
pnpm install                                        # front-end (no host)
```

### 3. Configurar ambiente e migrar

```bash
cp .env.example .env
docker compose exec -u root app php artisan key:generate
docker compose exec -u root app php artisan migrate --force
```

Valores padrão do Docker Compose:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=central
DB_USERNAME=admin
DB_PASSWORD=admin

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
```

> Sessão, cache e fila usam **Redis** (serviço `redis` do Compose). Para enviar e-mail de verdade: `MAIL_MAILER=resend` + `RESEND_API_KEY=...` no `.env`.

---

## Desenvolvimento local

O app é servido pelo Nginx do Docker em **http://localhost:18080** (porta 18080). Para hot reload do front, rode o Vite no host:

```bash
pnpm dev                      # Vite + HMR (deixe rodando em outro terminal)
```

Editar um `.tsx` atualiza a tela ao vivo. Sem o Vite rodando, gere o build estático com `pnpm build`.

---

## Massa de demonstração

```bash
# Popula uma empresa Demo com usuários (owner/rh/contador), jornadas,
# funcionários e alguns dias de ponto (idempotente):
docker compose exec -u root app php artisan dev:seed

# Recriar o banco do zero antes de semear (APAGA tudo):
docker compose exec -u root app php artisan dev:seed --fresh --force

# Criar uma empresa e definir o owner de forma interativa:
docker compose exec -u root app php artisan company:create
```

---

## Testes

Testes de integração usam **Testcontainers** — um PostgreSQL efêmero é criado por suite, isolando o banco de teste do de desenvolvimento. **Docker precisa estar rodando.**

```bash
# Todos os testes
docker compose exec -u root app php artisan test

# Um módulo / arquivo específico
docker compose exec -u root app php artisan test --filter=EmployeeTest
docker compose exec -u root app php artisan test --filter=DailyEngagementTest
docker compose exec -u root app php artisan test --filter=AuthTest
```

---

## Módulos

```
Modules/
├── Core/            — pessoas, empresas, usuários e vínculo usuário↔empresa (userCompanies)
├── Auth/            — login, seleção de empresa, logout, recuperação de senha
├── Job/             — funcionários (employees), vínculos (employments) e jornadas (workloads)
├── Attendance/      — lançamento de ponto (daily_engagements, time_entries)
└── Communication/   — envio de e-mail (contrato de mailer)
```

Cada módulo é autônomo — controllers, services, repositories, models, migrations, factories, policies e testes próprios — e tem schema Postgres próprio.

**Camadas** (fixas em todos os módulos): `Request` (valida → DTO) → `Controller` (fino, `authorizeResource`) → `Policy` (`currentCompany()->can/hasRole`) → `Service` (regra de negócio, `DB::transaction`, escopo por empresa) → `Repository` → `Model`. Listagens sempre paginadas. Detalhes em [FLUXO.md](FLUXO.md).

---

## Autenticação

Autenticação via **sessão** (cookie) com Sanctum.

```
POST /api/auth/login              — login com email e senha
POST /api/auth/forgot-password    — solicita recuperação de senha (throttle)
POST /api/auth/reset-password     — redefine a senha via token
POST /api/auth/select-company     — seleciona a empresa ativa (multi-empresa)
GET  /api/auth/me                 — dados do usuário autenticado
POST /api/auth/logout             — encerra a sessão
```

Após o login, as rotas protegidas exigem uma empresa ativa na sessão. Se o usuário pertence a uma única empresa, ela é selecionada automaticamente.

---

## Controle de acesso

Roles e permissões são **isoladas por empresa** via Spatie Permission com *teams*. O sujeito autorizável é o vínculo **`UserCompany`** (não o `User`): as permissões são atribuídas ao vínculo, e o middleware `current.company` fixa o team da empresa ativa a cada requisição. Todo acesso é escopado por empresa — `companyId` vem do contexto, nunca do payload.

Roles: `owner` · `humanResource` · `accountant` · `employee`. Matriz completa de permissões em [FLUXO.md](FLUXO.md#3-multi-empresa--autorização).

---

## API (recursos)

Rotas protegidas por `auth:sanctum` + `current.company`, sob o prefixo `/api/v1`:

```
persons              CRUD de pessoas
users                CRUD de usuários
companies            CRUD de empresas
employees            CRUD de funcionários  (+ PATCH employees/{id}/dismiss)
workloads            CRUD de jornadas
time-entries         CRUD de marcações de ponto
daily-engagements    index/show + submit · approve · reject · exception
```

---

## Roadmap

### ✅ Fundação
- Estrutura modular com autenticação via sessão (Sanctum)
- Multi-empresa: vínculo `UserCompany` com isolamento de permissões por empresa
- Controle de acesso com roles/permissões por empresa (Spatie Permission + teams)
- Módulos **Auth** (login, seleção de empresa, recuperação de senha) e **Core** (pessoas, empresas, usuários)
- **Communication** — envio de e-mail (Resend)
- Testes de integração com PostgreSQL via Testcontainers

### ✅ Módulo Job
- Funcionários com matrícula sequencial por empresa
- Vínculo funcionário ↔ pessoa ↔ empresa, admissão e desligamento
- Jornada de trabalho (Workload)

### 🔄 Módulo Attendance (em andamento)
- Lançamento manual de pontos diários por funcionário (substituição de planilhas)
- Fluxo de aprovação: draft → pending → approved/rejected
- Exceções: faltas, folgas, feriados, atestados
- Consolidação mensal por funcionário

### 🔄 Front-end (em andamento)
- Inertia + React sobre os módulos Core, Job e Attendance
- Colaboradores: listagem (filtros, paginação, responsivo) e cadastro (busca de pessoa por CPF com auto-preenchimento, jornada via modal)
- Jornadas: listagem com criação, edição e exclusão via modal (soft delete; bloqueada no back quando há vínculo ativo)
- Lançamento de ponto: grade mensal por colaborador (busca por nome) com edição inline (sem modais), tipo do dia direto na grade, "dia completo" pela jornada, exceções folga/feriado/atestado/falta (observação opcional) e envio p/ aprovação
- Lançamento de temporários (diarista/freelancer/temporário) na mesma tela, em aba própria (CLTs × Temporários, como em Aprovações): presença com 1 clique, coluna/total de diárias pra vínculo dayli (regra provisória — ver DiariaRule) e contratação rápida em tela (CPF busca quem já passou pela empresa ou cadastra pessoa nova)
- Aprovações: fila agrupada por colaborador com pares entrada→saída, alerta de sequência inconsistente, aprovar/rejeitar por dia ou em lote (dias marcados), histórico de aprovados/rejeitados com motivo e busca por nome

### 🔮 Futuro
- **Módulo Contract** — admissões, demissões, período de experiência, estágios, terceiros

---

## Licença

Proprietário — todos os direitos reservados.
