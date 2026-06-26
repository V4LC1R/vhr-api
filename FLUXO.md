# VHR — Fluxo Geral do Sistema

Visão de ponta a ponta: arquitetura modular, ciclo de uma requisição, multi-empresa/autorização, modelo de dados e os fluxos de cada módulo.

---

## 1. Módulos

```mermaid
flowchart TD
    Core[Core\npersons · companies · users · userCompanies] 
    Auth[Auth\nlogin / sanctum]
    Job[Job\nemployees · employments · workloads]
    Attendance[Attendance\ndaily_engagements · time_entries]

    Auth --> Core
    Job --> Core
    Attendance --> Job
    Attendance --> Core
```

- **Core** — base: pessoas, empresas, usuários e o vínculo usuário↔empresa (`userCompanies`) que carrega papéis/permissões.
- **Auth** — autenticação (Sanctum).
- **Job** — funcionários, vínculos (employment) e jornadas (workload).
- **Attendance** — lançamento de ponto (depende de Job e Core).

Cada módulo tem schema Postgres próprio (`core.*`, `job.*`, `attendance.*`).

---

## 2. Arquitetura em camadas (ciclo de uma requisição)

```mermaid
flowchart LR
    Req[HTTP Request] --> MW[Middlewares\nauth:sanctum + current.company]
    MW --> FR[FormRequest\nvalida + toDTO]
    FR --> C[Controller\nauthorizeResource]
    C --> P[Policy\ncurrentCompany: can / hasRole]
    P --> S[Service\nDB::transaction + scoping por empresa]
    S --> R[Repository\nBaseRepository]
    R --> M[(Model / Postgres)]
    S --> Res[Resource\nJSON camelCase]
    Res --> Out[Response\nshow plano · list paginado data+meta]
```

Convenções fixas em todos os módulos:
- **Request** valida e converte para um **DTO** (spatie/laravel-data).
- **Controller** fino: `authorizeResource` + delega ao **Service** + `response()->json()`.
- **Policy** decide acesso via `currentCompany()?->can(...)` (permissão) ou `?->hasRole(...)` (papel).
- **Service** concentra regra de negócio, roda em `DB::transaction`, escopa por empresa e retorna `->toResource()`.
- **Repository** (`BaseRepository`) padroniza acesso a dados; ligado por contrato no `AppServiceProvider`.
- **Listagem** sempre paginada: `->paginate()->through(fn => ->toResource())` → `{ data, current_page, per_page, total }`.

---

## 3. Multi-empresa & autorização

```mermaid
sequenceDiagram
    participant U as Cliente
    participant A as Auth (Sanctum)
    participant MW as Middleware current.company
    participant App as App

    U->>A: login (credenciais)
    A-->>U: token Sanctum
    U->>MW: request /api/v1/... (token + companyId na sessão)
    MW->>MW: session(companyId) -> UserCompany
    MW->>App: bind currentCompany() + setPermissionsTeamId(companyId)
    App->>App: Policies usam currentCompany()->can()/hasRole()
    App-->>U: resposta escopada à empresa
```

- Um usuário pode ter vários vínculos (`userCompanies`) — um por empresa.
- O middleware `current.company` resolve a **empresa ativa** (`currentCompany()`) e fixa o *team* de permissões (Spatie) naquela empresa.
- Toda leitura/escrita é **escopada por empresa**; `companyId` vem do contexto, nunca do payload do cliente.

### Papéis e permissões

| Permissão / Papel                         | owner | humanResource | accountant | employee |
|-------------------------------------------|:-----:|:-------------:|:----------:|:--------:|
| core.persons.*                            | ✓     | view/create/update | view  | view     |
| core.companies / users                    | ✓     | view (company)| view (company) | — |
| job.workloads.*                           | ✓     | ✓             | view       | —        |
| job.employees.* (+ dismiss)               | ✓     | ✓             | view       | view     |
| attendance.timeEntries.*                  | ✓     | view/create/update/delete | view | view |
| attendance.dailyEngagements.view          | ✓     | ✓             | ✓ (só aprovados) | ✓ (próprios) |
| attendance.dailyEngagements.create/update | ✓     | ✓             | —          | —        |
| attendance.dailyEngagements.approve       | ✓     | —             | —          | —        |

---

## 4. Modelo de dados

```mermaid
erDiagram
    persons ||--o{ userCompanies : ""
    companies ||--o{ userCompanies : ""
    users ||--o{ userCompanies : ""

    companies ||--o{ employees : ""
    persons   ||--o{ employees : ""
    employees ||--o{ employments : ""
    workloads ||--o{ employments : ""
    companies ||--o{ workloads : ""

    employees ||--o{ daily_engagements : ""
    workloads ||--o{ daily_engagements : "snapshot"
    daily_engagements ||--o{ time_entries : ""

    employees {
        uuid id
        uuid companyId
        uuid personId
        int  registerNumber "sequencial por empresa"
    }
    employments {
        uuid id
        uuid employeeId
        uuid workloadId
        enum status "hired|experience|left"
        enum kind "clt|dayli|temporary|freelancer"
        datetime register_at
        datetime left_at
    }
    daily_engagements {
        date date
        enum type "work|day_off|holiday|medical|absence"
        enum status "draft|pending|approved|rejected"
        int worked_minutes
        int balance_minutes
        decimal diaria_value "só dayli (parcial)"
    }
    time_entries {
        datetime punched_at "UTC"
        enum type "entry|exit"
        enum source "manual|device"
    }
```

---

## 5. Fluxo: Job (contratação)

```mermaid
flowchart TD
    A[POST /employees\nperson + workload] --> B{permissão create?}
    B -- não --> X[403]
    B -- sim --> C{já existe vínculo\nCLT ativo p/ a pessoa?}
    C -- sim --> Y[bloqueia: vínculo duplicado]
    C -- não --> D[resolve/cria Employee\nmatrícula sequencial por empresa]
    D --> E[cria Employment\nstatus=experience]
    E --> F[funcionário ativo]
    F -. dismiss .-> G[Employment status=left, left_at]
```

- **Employee** = identidade do funcionário na empresa (matrícula única por empresa).
- **Employment** = vínculo contratual (histórico; recontratação cria novo).
- **Workload** = jornada (horários + carga horária), base do cálculo de ponto.
- A jornada **ativa** de um funcionário: `employee.activeEmployment.workload`.

---

## 6. Fluxo: Attendance (ponto)

Resumo abaixo; detalhes em [`Modules/Attendance/FLOW.md`](Modules/Attendance/FLOW.md).

```mermaid
stateDiagram-v2
    [*] --> draft: lançar marcação (cria o dia)
    draft --> draft: editar / marcar exceção
    draft --> pending: submit (owner/RH)
    pending --> approved: approve (owner)
    pending --> rejected: reject (owner)
    approved --> draft: editar marcação
```

- **TimeEntry** = marcação (1 linha por ação, entrada/saída); `punched_at` em **UTC**.
- **DailyEngagement** = o dia (1 por funcionário/data); lançar marcação cria o dia em `draft`.
- **Cálculo** (`AttendanceCalculator`): soma só pares entrada→saída; marcação em aberto não conta. `expected`/`balance` variam por tipo (folga/feriado/atestado/falta).
- **Visibilidade**: rascunho só p/ quem criou; **contador só vê aprovados**; funcionário só os próprios.
- **Diária** (`dayli`): estrutura pronta, regra de pagamento **provisória** isolada em `DiariaRule`.

---

## 7. Onde está cada coisa

| Camada            | Caminho                                             |
|-------------------|-----------------------------------------------------|
| Contratos/repos   | `app/Contracts/*` + binding em `app/Providers/AppServiceProvider.php` |
| Base de repositório | `app/Supports/Abstracts/BaseRepository.php`       |
| Helpers / empresa | `app/Helpers/functions.php` (`currentCompany()`, `*Repo()`) |
| Empresa ativa     | `app/Http/Middlewares/SetActiveCompany.php`         |
| Papéis/permissões | `Modules/Core/database/seeders/RolesAndPermissionsSeeder.php` |
| Cada domínio      | `Modules/<Modulo>/app/{Models,Services,Policies,Http,...}` |
| Modelo visual     | `DER.dbml`                                           |
