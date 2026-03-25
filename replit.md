# Workspace

## Overview

pnpm workspace monorepo using TypeScript. Each package manages its own dependencies.

## Stack

- **Monorepo tool**: pnpm workspaces
- **Node.js version**: 24
- **Package manager**: pnpm
- **TypeScript version**: 5.9
- **API framework**: Express 5
- **Database**: PostgreSQL + Drizzle ORM
- **Validation**: Zod (`zod/v4`), `drizzle-zod`
- **API codegen**: Orval (from OpenAPI spec)
- **Build**: esbuild (CJS bundle)

## Structure

```text
artifacts-monorepo/
├── artifacts/              # Deployable applications
│   └── api-server/         # Express API server
├── lib/                    # Shared libraries
│   ├── api-spec/           # OpenAPI spec + Orval codegen config
│   ├── api-client-react/   # Generated React Query hooks
│   ├── api-zod/            # Generated Zod schemas from OpenAPI
│   └── db/                 # Drizzle ORM schema + DB connection
├── scripts/                # Utility scripts (single workspace package)
│   └── src/                # Individual .ts scripts, run via `pnpm --filter @workspace/scripts run <script>`
├── pnpm-workspace.yaml     # pnpm workspace (artifacts/*, lib/*, lib/integrations/*, scripts)
├── tsconfig.base.json      # Shared TS options (composite, bundler resolution, es2022)
├── tsconfig.json           # Root TS project references
└── package.json            # Root package with hoisted devDeps
```

## TypeScript & Composite Projects

Every package extends `tsconfig.base.json` which sets `composite: true`. The root `tsconfig.json` lists all packages as project references. This means:

- **Always typecheck from the root** — run `pnpm run typecheck` (which runs `tsc --build --emitDeclarationOnly`). This builds the full dependency graph so that cross-package imports resolve correctly. Running `tsc` inside a single package will fail if its dependencies haven't been built yet.
- **`emitDeclarationOnly`** — we only emit `.d.ts` files during typecheck; actual JS bundling is handled by esbuild/tsx/vite...etc, not `tsc`.
- **Project references** — when package A depends on package B, A's `tsconfig.json` must list B in its `references` array. `tsc --build` uses this to determine build order and skip up-to-date packages.

## Root Scripts

- `pnpm run build` — runs `typecheck` first, then recursively runs `build` in all packages that define it
- `pnpm run typecheck` — runs `tsc --build --emitDeclarationOnly` using project references

## Packages

### `artifacts/api-server` (`@workspace/api-server`)

Express 5 API server. Routes live in `src/routes/` and use `@workspace/api-zod` for request and response validation and `@workspace/db` for persistence.

- Entry: `src/index.ts` — reads `PORT`, starts Express
- App setup: `src/app.ts` — mounts CORS, JSON/urlencoded parsing, routes at `/api`
- Routes: `src/routes/index.ts` mounts sub-routers; `src/routes/health.ts` exposes `GET /health` (full path: `/api/health`)
- WordPress update server: `src/routes/wp-update.ts` — serves plugin version info and ZIP download
  - `GET /api/wp-update/info` — returns plugin metadata (name, version, download_url, changelog)
  - `GET /api/wp-update/version` — returns minimal version check
  - `GET /api/wp-update/download` — serves the latest `tablemaster-pro.zip` (streamed, not buffered)
- Security: Helmet headers, rate limiting (60 req/min general, 10 req/min downloads), explicit body size limits (100kb), base URL derived from REPLIT_DEV_DOMAIN (not request headers)
- Depends on: `@workspace/db`, `@workspace/api-zod`
- `pnpm --filter @workspace/api-server run dev` — run the dev server
- `pnpm --filter @workspace/api-server run build` — production esbuild bundle (`dist/index.cjs`)
- Build bundles an allowlist of deps (express, cors, pg, drizzle-orm, zod, etc.) and externalizes the rest

### TableMaster Pro (WordPress Plugin)

WordPress plugin in `tablemaster-pro/` folder. ZIP built at root as `tablemaster-pro.zip`.

- ~65KB ZIP, current version 1.3.27
- Features: unlimited tables, 3-level grouping, multi-level column headers (3-level), footer/closing rows (full-width spanning banner with configurable colors), color theming (red/blue/grey/custom), search/sort/pagination, responsive (horizontal scroll), shortcode `[tablemaster id="X"]`, Gutenberg block, WPML with built-in translation editor, auto-updates, CSV import (auto-detect delimiter: comma/semicolon/tab, quoted fields, BOM support), Excel-like toolbar (bold/italic/link/bullets/cell alignment left/center/right/delete row/delete col), per-cell text alignment (stored in cells table `align` column), per-table max-width setting (renders in `<style>` block, overridable by Elementor)
- WPML integration: uses **modern WPML filter/action API only** (`wpml_register_single_string`, `wpml_translate_single_string`, `wpml_active_languages`, `wpml_current_language`, `wpml_default_language`). No deprecated `icl_*` functions. All DB queries to WPML tables include table existence checks. Translation completeness (100% required) enforced via `get_translation_progress()`.
- WPML Translation Editor: custom admin page (`tablemaster-translate`) with side-by-side layout (original left, translation right), covers table name, caption, column labels, and all cell content; saves directly to WPML string translations via `icl_string_translations` table
- Admin UX: clickable row-type badge (Data→G1→G2→G3 cycle), row duplicate button, auto-merge hints in group row placeholders
- Group row auto-merge: on frontend, empty cells following a filled cell in group rows are automatically merged with colspan
- Auto-update: plugin checks API server's `/api/wp-update/info` endpoint; update URL configured in WP admin under TableMaster > Instellingen
- Elementor integration: custom widget with defensive guards (`class_exists`, `method_exists` for both `register` and legacy `register_widget_type`), CSS overrides for editor/preview, table selector dropdown, style controls (max-width, font-size, alignment). Widget auto-activates when Elementor is present, gracefully skips when absent.
- Key files: `tablemaster-pro.php` (main), `includes/class-updater.php` (auto-update checker), `includes/class-wpml.php` (WPML integration — centralized helpers), `templates/table-frontend.php` (frontend rendering), `assets/css/frontend.css` (styling), `assets/js/admin.js` (admin editor), `assets/css/admin.css` (admin styling), `includes/class-elementor.php` (Elementor integration), `includes/class-elementor-widget.php` (Elementor widget)

### `lib/db` (`@workspace/db`)

Database layer using Drizzle ORM with PostgreSQL. Exports a Drizzle client instance and schema models.

- `src/index.ts` — creates a `Pool` + Drizzle instance, exports schema
- `src/schema/index.ts` — barrel re-export of all models
- `src/schema/<modelname>.ts` — table definitions with `drizzle-zod` insert schemas (no models definitions exist right now)
- `drizzle.config.ts` — Drizzle Kit config (requires `DATABASE_URL`, automatically provided by Replit)
- Exports: `.` (pool, db, schema), `./schema` (schema only)

Production migrations are handled by Replit when publishing. In development, we just use `pnpm --filter @workspace/db run push`, and we fallback to `pnpm --filter @workspace/db run push-force`.

### `lib/api-spec` (`@workspace/api-spec`)

Owns the OpenAPI 3.1 spec (`openapi.yaml`) and the Orval config (`orval.config.ts`). Running codegen produces output into two sibling packages:

1. `lib/api-client-react/src/generated/` — React Query hooks + fetch client
2. `lib/api-zod/src/generated/` — Zod schemas

Run codegen: `pnpm --filter @workspace/api-spec run codegen`

### `lib/api-zod` (`@workspace/api-zod`)

Generated Zod schemas from the OpenAPI spec (e.g. `HealthCheckResponse`). Used by `api-server` for response validation.

### `lib/api-client-react` (`@workspace/api-client-react`)

Generated React Query hooks and fetch client from the OpenAPI spec (e.g. `useHealthCheck`, `healthCheck`).

### `scripts` (`@workspace/scripts`)

Utility scripts package. Each script is a `.ts` file in `src/` with a corresponding npm script in `package.json`. Run scripts via `pnpm --filter @workspace/scripts run <script>`. Scripts can import any workspace package (e.g., `@workspace/db`) by adding it as a dependency in `scripts/package.json`.
