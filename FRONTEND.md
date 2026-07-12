# Front-end — VHR API

Setup, arquitetura e fluxo de trabalho do front-end.

## Stack

| Camada | Tecnologia |
|---|---|
| Bridge servidor↔cliente | **Inertia v3** (`inertiajs/inertia-laravel`, `@inertiajs/react`) |
| UI | **React 19** + **TypeScript** |
| Build | **Vite** + `@vitejs/plugin-react` |
| Gerenciador de pacotes | **pnpm** |
| Estilo | **Tailwind CSS v4** (CSS-first) |
| Design system | **shadcn/ui** (sobre base-ui) |
| Estado de UI (cliente) | **zustand** |
| Rotas tipadas | **Ziggy** (`tightenco/ziggy` + `ziggy-js`) |

> Sem React Query: o Inertia v3 (`useHttp`, optimistic updates, deferred props, prefetch, polling) cobre os casos de dados.

## Arquitetura

O back-end é modular (`nwidart/laravel-modules`), mas o **front é unificado**. Diferente do back — onde a fronteira é aplicada por interface + container DI — o front não tem mecanismo de fechamento (`import` é só `import`) e a tela, por natureza, cruza vários contextos. Espelhar módulos no front seria parede de mentira na camada que mais compõe contexto.

**Modelo:** a modularidade vive 100% no back-end. O front é uma view layer fina.

- **Quem decide a exibição é o módulo (back-end):** o controller do módulo faz `Inertia::render('Timesheet/Index', $dados)` — dono da rota, da composição dos dados e da escolha da tela.
- O front só apresenta o que recebeu via props.

### Estrutura de pastas (`resources/js`)

```
resources/js/
├── app.tsx           # entrypoint: createInertiaApp (resolve ./pages/**)
├── pages/            # CASCA: captura props do Inertia e delega pra feature. Sem lógica.
├── feature/          # implementação de cada page: hooks, schemas, types, utils, UI da feature
├── components/       # componentes compartilhados
│   └── ui/           # shadcn (design system)
├── layouts/          # AppLayout, GuestLayout (shells)
├── stores/           # zustand (estado de UI global)
├── lib/              # utils (cn)
└── types/            # PageProps + tipos de domínio globais
```

**Fluxo de uma tela:** `Controller do módulo` → `Inertia::render('X/Index')` → `pages/X/Index.tsx` (casca) → `feature/x` (implementação).

Alias: `@/*` → `resources/js/*` (definido em `vite.config.ts` **e** `tsconfig.json` — manter os dois em sincronia).

## Rodar localmente

Ambiente: **PHP/DB/nginx/redis no Docker; Node/Vite no host.** O app é servido pelo nginx do Docker em **http://localhost** (porta 80).

### Dia a dia (com hot reload)

```bash
docker compose start      # sobe db, redis, app (php), nginx
pnpm dev                  # Vite + HMR no host (:5173) — deixa rodando em outro terminal
```
Abra **http://localhost**. Editar um `.tsx` e salvar atualiza a tela ao vivo (HMR + React Fast Refresh).

**Atalho** (comando único, rodar no host):
```bash
docker compose start && composer dev
```
Sobe serve + queue + logs + vite juntos. Roda **no host** — o container `app` não tem Node.

### Primeira vez (clone novo)

```bash
docker compose up -d
pnpm install
docker compose exec app composer install
```

### Ver sem HMR (build estático)

```bash
pnpm build                # gera public/build/ — não precisa deixar nada rodando
```

## Como funciona o serving

```
navegador (host) → nginx (Docker :80) → Laravel: Inertia::render('Page')
   ↓ devolve o HTML do blade com as tags @vite + os dados da page (data-page)
navegador carrega o JS do Vite (host :5173, em dev) → React monta a page no #app
```

- Com `pnpm dev` rodando, o Vite cria `public/hot`. O `@vite` no blade detecta esse arquivo e carrega os assets do dev server (:5173) em vez de `public/build`.
- Cruza a fronteira do Docker: **HTML** vem do nginx (:80), **JS/CSS** vem do Vite (host :5173). Funciona porque o navegador roda no host.

## Produção (resumo)

Sem SSR, **Node não roda como serviço em prod** — é só ferramenta de build:
- **build:** `pnpm build` gera `public/build/` (estáticos + manifest)
- **runtime:** nginx + php-fpm servem os estáticos. Sem Node, sem Vite.

Pra testar prod-like localmente: `pnpm build` + `rm -f public/hot` (força o blade a usar o build). O Dockerfile multi-stage de build será encaminhado no momento do deploy.

## Troubleshooting

| Sintoma | Causa / solução |
|---|---|
| `http://localhost` dá **500** com erro de Vite manifest | Sem assets nem dev server. Rode `pnpm dev` ou `pnpm build`. |
| `http://localhost` dá **502** (`Connection refused` no upstream nos logs do nginx) | O container `app` foi recriado e mudou de IP; o nginx ainda aponta pro IP antigo. O `resolver` em `docker/nginx/default.conf` corrige automático em ~10s. Se persistir: `docker compose restart nginx`. |
| Tela em branco / 404 nos assets após parar o Vite | `public/hot` ficou stale. Rode `pnpm build` ou `rm -f public/hot`. |
| `composer dev` falha no `pnpm` | Foi rodado dentro do Docker. Rode **no host** (container `app` não tem Node). |
| Classe Tailwind não aplica em arquivo novo | Confira o `@source` em `resources/css/app.css` (escaneia `../js/**/*.{ts,tsx}`). |
| Import `@/...` não resolve | Alias precisa estar em `vite.config.ts` **e** `tsconfig.json`. |
