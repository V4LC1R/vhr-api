# Módulo Attendance — Fluxo

Digitalização da planilha de ponto: lançamento manual (retroativo), cálculo de horas/saldo, exceções e aprovação. Sem bate-ponto automático.

## Entidades

```mermaid
erDiagram
    DAILY_ENGAGEMENT ||--o{ TIME_ENTRY : "tem (0..N)"
    EMPLOYEE ||--o{ DAILY_ENGAGEMENT : "tem (1 por dia)"
    WORKLOAD ||--o{ DAILY_ENGAGEMENT : "snapshot da jornada"

    DAILY_ENGAGEMENT {
        uuid id
        uuid employeeId
        uuid workloadId "snapshot"
        date date
        enum type "work|day_off|holiday|medical|absence"
        enum status "draft|pending|approved|rejected"
        int worked_minutes "cache"
        int expected_minutes "cache"
        int balance_minutes "cache"
        decimal diaria_value "só dayli (parcial)"
        uuid draftedBy "autor do rascunho"
        uuid approvedBy
        datetime approvedAt
    }
    TIME_ENTRY {
        uuid id
        uuid dailyEngagementId
        datetime punched_at "UTC"
        enum type "entry|exit"
        enum source "manual|device"
    }
```

- **DailyEngagement** = o dia consolidado por funcionário (1 por funcionário/data).
- **TimeEntry** = cada marcação (1 linha por ação: entrada/saída).

## Máquina de estados do dia

```mermaid
stateDiagram-v2
    [*] --> draft: lançar 1ª marcação\n(cria o dia)
    draft --> draft: editar marcação / marcar exceção
    draft --> pending: submit (owner/RH)
    pending --> approved: approve (owner)
    pending --> rejected: reject (owner)
    approved --> draft: editar marcação\n(reabre p/ revisão)
    rejected --> draft: editar marcação
    pending --> draft: editar marcação
```

- O dia **nasce `draft`** ao lançar a primeira marcação.
- Qualquer **edição** de marcação (criar/alterar/excluir) ou **marcar exceção** devolve o dia para `draft` e limpa a aprovação.
- **`submit`** (owner/RH) envia `draft → pending`.
- **`approve`/`reject`** (só owner) agem apenas sobre `pending`.

## Fluxo de lançamento de marcação

```mermaid
flowchart TD
    A[POST /time-entries\nemployeeId, punched_at+fuso, type] --> B{permissão\nattendance.timeEntries.create?}
    B -- não --> X[403]
    B -- sim --> C[normaliza punched_at -> UTC]
    C --> D[deriva a data do dia]
    D --> E{já existe\nDailyEngagement\ndesse dia?}
    E -- sim --> F[usa o dia existente]
    E -- não --> G[cria o dia\nstatus=draft, draftedBy=autor\nworkloadId=jornada ativa]
    F --> H[cria TimeEntry source=manual]
    G --> H
    H --> I[se dia != draft -> volta p/ draft]
    I --> J[AttendanceCalculator.recalculate]
    J --> K[grava worked/expected/balance/diaria]
```

## Cálculo (AttendanceCalculator)

**worked_minutes** — soma só pares **entrada → saída** completos, em ordem de horário:

```mermaid
flowchart LR
    E1[entry 08:00] --> S1[exit 12:00]
    S1 -->|+240| E2[entry 13:00]
    E2 --> S2[exit 18:00]
    S2 -->|+300| T[worked = 540]
```

- Mantém a **primeira entrada aberta**; entradas repetidas são ignoradas.
- Saída sem entrada aberta é ignorada.
- **Marcação em aberto (entrada sem saída) NÃO é calculada.**
- Funciona cruzando meia-noite (diferença entre dois `datetime`).

**expected_minutes / balance_minutes** dependem do `type` do dia:

| type      | expected             | worked                 | balance         |
|-----------|----------------------|------------------------|-----------------|
| `work`    | jornada (ex.: 540)   | das marcações          | worked − expected |
| `day_off` | 0                    | das marcações (0)      | 0               |
| `holiday` | 0                    | das marcações (0)      | 0               |
| `medical` | jornada              | **abonado = expected** | 0               |
| `absence` | jornada              | 0                      | − expected      |

> `expected` = `(left_time − entry_time) − (interval_end − interval_start)` da jornada (workload) snapshot. Sem workload no dia → `expected = 0`.

## Diária (`dayli`) — parcial

```mermaid
flowchart TD
    A[recalculate] --> B{vínculo ativo\né dayli?}
    B -- não --> N[diaria_value = null]
    B -- sim --> R[DiariaRule.provisional\nPROVISÓRIO: por presença]
    R --> V[worked>0 e type=work ? 1.0 : 0.0]
```

A regra de pagamento real (presença × horas × meia-diária) ainda depende do cliente — vive só em `DiariaRule`. O dado cru (worked + presença) já é persistido, então trocar a regra não exige migration.

## Visibilidade (quem vê o quê)

| Papel            | O que enxerga na listagem/relatório                     |
|------------------|---------------------------------------------------------|
| owner / RH       | Todos os dias da empresa; rascunho **só o que ele criou** |
| **contador**     | **Somente dias `approved`** (relatório de horas)        |
| funcionário comum| Somente os **próprios** dias (via `personId`)           |
| qualquer um      | Rascunho (`draft`) só é visível para **quem o criou** (`draftedBy`) |

## Timezone

- `punched_at` é **convertido para UTC** no back (`08:00-03:00` → grava `11:00`).
- Lançamento é **retroativo**: a hora vem sempre do dado enviado, nunca do servidor.
- O front converte de volta para o fuso local na exibição.
- Cálculo de horas é diferença entre marcações → indiferente ao fuso.

## Permissões

`attendance.timeEntries.{view,create,update,delete}` · `attendance.dailyEngagements.{view,create,update,approve}`
- `approve` → só owner. `update/delete` de marcação → owner/RH (role).
