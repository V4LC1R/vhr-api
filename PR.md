# Fundação do front-end (Inertia + React) e auth compartilhada por empresa

Branch: `feature/front-end` → `main`

## Objetivo

Montar a base do front unificado em `resources/js` (Inertia v3 + React 19 + TypeScript) e ligar o contexto de autenticação/permissões ao Inertia, já no modelo multi-tenant do produto (papéis por empresa).

## O que entra

### Stack / build
- **Inertia v3 + React 19 + TypeScript**, bundle via **Vite** (`vite.config.js` → `vite.config.ts`).
- **Tailwind v4** (`@tailwindcss/vite`) + tema em `resources/css/app.css`.
- **shadcn/ui** (`components.json`, `Button` inicial) e util `cn` em `resources/js/lib/utils.ts`.
- **pnpm** como gerenciador (`pnpm-lock.yaml`).
- Bootstrap do app Inertia em `resources/js/app.tsx` (resolve de páginas via `import.meta.glob`).
- View raiz `resources/views/app.blade.php` e rota inicial `routes/web.php` (`Inertia::render('Welcome')`).

### Layouts (persistent layout do Inertia)
- `resources/js/layouts/GuestLayout.tsx` — telas públicas (centraliza, full-height).
- `resources/js/layouts/AppLayout.tsx` — esqueleto da área autenticada.
- `resources/js/pages/Welcome.tsx` passa a usar o **persistent layout** via `Welcome.layout = (page) => <GuestLayout>{page}</GuestLayout>`, em vez de envolver no render — assim o layout não remonta entre navegações. O `min-h-screen` saiu da página (quem dá altura é o layout).

### Auth/permissões compartilhadas no Inertia — giradas em `UserCompany`
`app/Http/Middlewares/HandleInertiaRequests.php` passa a expor `auth`:

```
auth.user     → { id, email }              // identidade de login (sempre presente quando logado)
auth.current  → null                        // SEM empresa selecionada → front renderiza a tela de seleção
auth.current  → {                           // COM empresa ativa:
    companyId,
    company: { id, name },
    name,                                    // person.name (nome de exibição real)
    roles,                                   // papéis no escopo da empresa
    permissions,                             // permissões no escopo da empresa
}
```

Decisões de arquitetura:
- **O sujeito autorizável é o `UserCompany`, não o `User`.** É o `UserCompany` que carrega o trait `AdapterToPermission` (Spatie `HasRoles`, guard `web`) e implementa `AuthorizableContract`. O mesmo usuário tem papéis diferentes por empresa.
- Spatie em **modo teams** (`team_foreign_key = companyId`); o middleware `SetActiveCompany` (alias `current.company`) lê `session('companyId')`, carrega o `UserCompany` (com `company`/`person`, cacheado) e faz `setPermissionsTeamId()`, além de bindar `currentCompany` no container.
- O `share()` lê `currentCompany` do container e **só popula `auth.current` quando há empresa ativa** (`app()->bound('currentCompany')`). Com isso, `getAllPermissions()` (que bate no banco) **só roda quando faz sentido** — nas rotas que ativam o escopo da empresa. Nome e empresa saem das relations já carregadas/cacheadas, sem query extra.

### Outros
- Store de UI com **zustand** (`resources/js/stores/ui.ts`): tema + sidebar, persistido.
- Tipos compartilhados em `resources/js/types/index.d.ts`.
- Ajustes de infra: `bootstrap/app.php`, `composer.json/lock`, `docker/nginx/default.conf`, `package.json`.

## Validação

- `pnpm build` (vite) — ✅ passa.
- `php -l` em `HandleInertiaRequests.php` e `User.php` — ✅ sem erros de sintaxe.
- Typecheck (`tsc --noEmit`) — sem erros nas mudanças; resta apenas um aviso pré-existente de deprecação do `baseUrl` no `tsconfig.json`.

## Follow-ups / pontos de atenção

- ⚠️ **`resources/js/types/index.d.ts` está defasado** em relação ao novo `share()`: declara `auth: { user: User | null }` com `id: number` e `name`, mas o backend agora manda `auth.user = { id, email }` (id é **UUID/string**) e `auth.current`. Também declara `flash`, que o `share()` ainda não compartilha. Atualizar os tipos (`User.id: string`, adicionar `auth.current`, decidir sobre `flash`).
- `AppLayout` ainda é esqueleto: falta o shell real (sidebar/topbar) com nav filtrada por `auth.current.permissions`.
- Telas de `auth` e `employee` e seus layouts ainda não existem; quando surgirem, vale avaliar migrar a atribuição de layout para convenção de pasta no `resolve` do `app.tsx`.
- Garantir que as rotas do dashboard apliquem o middleware `current.company` (é o que ativa o escopo de permissões).
