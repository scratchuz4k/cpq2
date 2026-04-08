# CPQ App — Workspace Instructions

Configure, Price, Quote (CPQ) application built on Laravel 12 + Vue 3 + Inertia.js, serving multiple tenants with full quote lifecycle management.

## Tech Stack

| Layer    | Technology                                         |
| -------- | -------------------------------------------------- |
| Backend  | Laravel 12, PHP 8.4                                |
| Frontend | Vue 3 (Composition API), Inertia.js v2, PrimeVue 4 |
| Bundler  | Vite 6, TailwindCSS 3 + Bootstrap 5 + SCSS         |
| Database | MariaDB 11 (primary), SQLite (tests)               |
| Queue    | Laravel Horizon (Redis)                            |
| Testing  | Pest 3 (unit/feature), Cypress 13 (E2E)            |
| Auth     | Sanctum, Socialite (Google + SAML2)                |

## Build & Test

```bash
# Dev environment (Sail)
./vendor/bin/sail up -d

# Frontend
npm run dev        # dev server (Vite on port 5173)
npm run build      # production build

# Backend
php artisan test          # Pest unit + feature tests
npm run cy:run            # Cypress E2E tests
composer run format       # Pint PHP formatter
```

Pest feature tests use `RefreshDatabase` with an in-memory SQLite DB (see [phpunit.xml](../phpunit.xml)).

## Architecture

### Backend (`app/`)

Controller → Service → Repository/Model. Business logic lives in Services; controllers handle HTTP concerns only.

- `Http/Controllers/` — thin, inject services via constructor
- `Services/` — business logic (QuoteService, ProductsService, PriceService, …)
- `Repositories/` — complex query encapsulation (AccountRepository, TenantRepository, …)
- `Models/` — ~40 Eloquent models, compose traits for cross-cutting concerns
- `Enums/` — status/type constants (QuoteStatusesEnum, CalculatorLayoutsEnum, …)
- `Rules/`, `Http/Requests/` — validation at the boundary

See [app/Http/Controllers/Api/QuoteController.php](../app/Http/Controllers/Api/QuoteController.php) and [app/Services/QuoteService.php](../app/Services/QuoteService.php) as canonical examples.

### Frontend (`resources/js/`)

- `Pages/` — Inertia page components (one per route, injected by controllers)
- `Components/` — reusable Vue components
- `Composables/` — Vue composables (tooltip, API helpers)
- `Layouts/` — layout shells

All API calls use Axios with auto-CSRF injection and 401 → login redirect.

## Multi-Tenancy (Critical)

**Every model query is automatically tenant-scoped.** All models use the `SetTenantId` trait + `TenantScope` global scope (Spatie Multitenancy). Do not assume global DB access.

- Queued jobs are tenant-aware by default (`queues_are_tenant_aware_by_default: true`)
- Config: [config/multitenancy.php](../config/multitenancy.php)

## Model Conventions

Models in this project:

1. **Compose traits** — `HasConfigurations`, `RegisterActivity`, `SetTenantId`, `SoftDeletes`
2. **Use `booted()` hooks** — for UUID generation, status auto-calculation
3. **Dispatch events** on significant state changes (e.g., `QuoteStatusEvent`)
4. **JSON cast** complex fields (configurations, metadata)
5. **Soft delete** widely — always check for deleted records when debugging missing data

See [app/Models/Quote.php](../app/Models/Quote.php) as the reference model.

## Code Style

- PHP: Pint (`laravel` preset) — run `composer run format` before committing
- PHP: Rector (PHP 8.3+ target) — see [rector.php](../rector.php)
- JS/Vue: Prettier

Helpers: [app/Helpers/Helpers.php](../app/Helpers/Helpers.php) and [app/Helpers/QuoteHelpers.php](../app/Helpers/QuoteHelpers.php) are auto-loaded.

## Testing Conventions

- **Pest** for all PHP tests — use `it()` / `test()` style, group with `describe()`
- Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`
- **Cypress** E2E tests in `cypress/e2e/` organised by domain (products/, quotes/, accounts/, …)
- Factories in `database/factories/` follow the domain model
- Use `DB::transaction()` for tests that modify multiple models

## Key Pitfalls

- **Tenant scope is always on** — adding `withoutGlobalScope(TenantScope::class)` is rarely correct; prefer proper tenant context setup in tests
- **Quote status is auto-calculated** on save via `Quote::resolveStatus()` — don't set it directly
- **Quote UUID** is auto-generated in `booted()` — never pass a UUID on creation
- **MongoDB** is installed but optional — verify connection config before using it
- **Vite port** is 5173 (mapped in docker-compose); set `VITE_PORT` if you change it
