# Tech Stack

> Every technology used in Project oblok, why it was chosen, what alternatives were considered, and what tradeoffs exist.
>
> For how these technologies fit together architecturally, see [architecture.md](architecture.md). For the engineering process around them, see [development.md](development.md).

---

## Backend Framework

### Laravel 13

**Purpose:** Application framework. Provides routing, ORM, queue management, event system, authentication scaffolding, and application structure.

**Why selected:**
- Mature ecosystem with extensive first-party packages (Horizon, Reverb, Sanctum, Breeze).
- Strong conventions that reduce decision fatigue.
- Excellent documentation and community support.
- Built-in support for queues, events, notifications, and scheduling — all core to oblok.
- First-class testing support with Pest integration.

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| Symfony | More flexible but requires more boilerplate. Laravel's conventions better suit a single-developer project. |
| Spring Boot | JVM ecosystem adds operational complexity. PHP deployment is simpler for self-hosted targets. |
| Go (stdlib/Echo) | Excellent performance but lacks the built-in ecosystem for queues, events, and ORM that oblok needs. |
| Node.js (NestJS) | Viable, but the Laravel ecosystem provides more out-of-the-box for operational tooling. |

**Tradeoffs:**
- PHP has lower raw throughput than Go or Rust, but oblok workloads are I/O-bound, not CPU-bound.
- Laravel's conventions can feel rigid for unconventional architectures, but oblok embraces those conventions.

**Future considerations:**
- Evaluate Laravel Octane for long-running process performance if health check throughput becomes a bottleneck.

---

## Language

### PHP 8.4

**Purpose:** Runtime language.

**Why selected:**
- Required by Laravel 13.
- PHP 8.4 provides property hooks, asymmetric visibility, and improved type system.
- Strong typing capabilities reduce runtime errors when used with PHPStan.

**Tradeoffs:**
- PHP's reputation is weaker than Go or Rust in some engineering communities, but its capabilities are sufficient for oblok.

---

## Database

### PostgreSQL

**Purpose:** Primary data store for all application data.

**Why selected:**
- Superior support for JSON columns, full-text search, and advanced indexing compared to MySQL.
- Better data integrity defaults (strict mode by default, proper transaction isolation).
- Native UUID support.
- Excellent performance for analytical queries that oblok will need for metrics and log aggregation.
- Strong ecosystem of extensions (pg_stat_statements, pg_trgm for fuzzy search).

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| MySQL / MariaDB | Weaker JSON support, less strict defaults, fewer advanced features. |
| SQLite | Not suitable for concurrent writes from multiple queue workers. |
| MongoDB | Schema flexibility not needed. Relational data model is a better fit. |

**Tradeoffs:**
- Slightly more complex to set up than SQLite for local development, mitigated by Docker.
- Heavier resource usage than SQLite, acceptable for a server-side application.

**Future considerations:**
- TimescaleDB extension for time-series metric storage if custom metric volume grows.
- Read replicas if query load requires it.

---

## Cache

### Redis

**Purpose:** Application cache, session store, and queue broker.

**Why selected:**
- Proven performance for key-value caching and message brokering.
- Native Laravel support with zero configuration.
- Supports both caching and queue workloads, reducing infrastructure components.
- Pub/sub capabilities useful for realtime event broadcasting.

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| Memcached | No persistence, no pub/sub, no queue support. |
| Database cache | Slower than in-memory cache, adds unnecessary database load. |
| RabbitMQ | More powerful queue semantics than needed. Adds operational complexity. |

**Tradeoffs:**
- Redis is single-threaded. Unlikely to be a bottleneck at oblok's target scale.
- Using Redis for both cache and queue means a Redis failure impacts both. Acceptable for self-hosted deployments where simplicity matters more than fault isolation.

**Future considerations:**
- Separate Redis instances for cache and queue if workload separation becomes necessary.
- Redis Sentinel or Cluster for high-availability deployments.

---

## Queue

### Redis (via Laravel Queue)

**Purpose:** Background job processing for health checks, notifications, log ingestion, and other async workloads.

**Why selected:**
- Same Redis instance used for caching, reducing infrastructure.
- Laravel's queue system provides job retries, delays, rate limiting, and middleware out of the box.
- Laravel Horizon provides a monitoring dashboard for Redis-backed queues.

**Future considerations:**
- Laravel Horizon for queue monitoring and metrics in production.
- Named queues for workload isolation (e.g., `monitoring`, `notifications`, `default`).

---

## Realtime

### Laravel Reverb

**Purpose:** WebSocket server for realtime updates (dashboard refresh, log streaming, notification delivery).

**Why selected:**
- First-party Laravel package. No external WebSocket service required.
- Self-hosted, consistent with oblok's philosophy of zero external dependencies.
- Integrates directly with Laravel's event broadcasting system.

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| Pusher | Third-party SaaS dependency. Violates self-hosted philosophy. |
| Socket.io (Node.js) | Requires a separate Node.js process. Adds operational complexity. |
| Soketi | Viable self-hosted option but less integrated with Laravel than Reverb. |
| Server-Sent Events | Unidirectional only. WebSockets provide bidirectional communication for future features. |

**Tradeoffs:**
- Reverb is newer than alternatives. Smaller community, fewer production war stories.
- Requires a separate long-running process alongside the main application.

---

## Frontend

### Blade

**Purpose:** Server-side templating engine.

**Why selected:**
- Native Laravel integration. No build step required for basic views.
- Server-rendered pages provide better initial load performance and SEO.
- Simpler mental model than a full SPA — no client-side routing, no state management library.

### Alpine.js

**Purpose:** Client-side interactivity for dynamic UI components (dropdowns, modals, filters, search).

**Why selected:**
- Minimal footprint (~15KB). Declared directly in HTML attributes.
- No build step. No virtual DOM. No component tree.
- Sufficient for the interactivity oblok needs without the complexity of React or Vue.

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| React | Requires a full SPA architecture, build pipeline, and API layer. Overkill for oblok. |
| Vue.js | Better than React for progressive enhancement, but still heavier than Alpine.js. |
| htmx | Viable, but Alpine.js is more mature and has better Laravel ecosystem support (via Livewire patterns). |
| Livewire | Adds server-side state management complexity. Alpine.js is simpler for oblok's needs. |

### Tailwind CSS

**Purpose:** Utility-first CSS framework.

**Why selected:**
- Rapid UI development with consistent spacing, colors, and typography.
- Works well with Blade templates.
- Built-in dark mode support.
- PurgeCSS integration keeps production bundles small.

**Tradeoffs:**
- HTML can become verbose with many utility classes.
- Requires a build step (PostCSS) for production optimization.

---

## Charts

### ApexCharts

**Purpose:** Interactive charts for metrics, uptime history, response times, and dashboards.

**Why selected:**
- Rich chart types (line, area, bar, donut) covering all oblok use cases.
- Built-in dark mode support.
- Interactive tooltips, zooming, and time-range selection.
- No framework dependency — works with vanilla JavaScript and Blade.

**Distribution:**
- Bundled locally via Vite (`apexcharts` npm dependency, exposed as `window.ApexCharts`). Charts are not loaded from a third-party CDN, so the dashboards work on networks where external CDNs are blocked or slow.

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| Chart.js | Fewer built-in features. Requires more plugins for equivalent functionality. |
| D3.js | Too low-level. Requires significant custom code for standard charts. |
| Recharts | React-only. |
| ECharts | Viable, but ApexCharts has a simpler API for common use cases. |

**Tradeoffs:**
- Larger bundle size than Chart.js (~125KB vs ~65KB). Acceptable for a dashboard application.

---

## AI Assistant

**Purpose:** Power natural-language queries against a project's operational data.

**Approach:** oblok does **not** depend on a vendor AI SDK. The assistant talks to any
OpenAI-compatible `/chat/completions` endpoint over Laravel's HTTP client, behind an
`AiProvider` interface resolved by `AiProviderManager`. This keeps the provider swappable
(Groq, OpenAI, OpenRouter, Ollama, LM Studio, vLLM) and adds zero composer dependencies.

**Provider:** Groq is the default tested provider (`OBLOK_AI_ENDPOINT=https://api.groq.com/openai/v1`).
It offers strong models on a free tier, keeping the self-hosted platform free to operate.
Default model: `openai/gpt-oss-120b`; alternatives on the same free tier include
`llama-3.3-70b-versatile` and `llama-3.1-8b-instant`.

**Why selected:**
- No SDK dependency — one thin HTTP driver serves every compatible provider.
- Groq's free tier provides fast, reliable inference at no cost.
- The action layer (prompt construction, context building) is provider-agnostic and unit-testable with `Http::fake()`.

---

## Authentication

### Laravel Breeze

**Purpose:** Authentication scaffolding (login, registration, password reset, email verification).

**Why selected:**
- Minimal, un-opinionated scaffolding. Generates clean Blade templates.
- Provides a working authentication system in minutes.
- Easy to customize — all generated code is in the application, not hidden in a package.

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| Laravel Jetstream | Includes team management and API tokens out of the box, but is more opinionated and heavier. oblok will build these features incrementally. |
| Laravel Fortify | Headless — no UI scaffolding. Breeze provides starter templates. |
| Custom auth | Unnecessary when Breeze provides a solid, auditable starting point. |

**Future considerations:**
- Add Sanctum for API token authentication when API management is implemented (v0.3).
- SSO/SAML support planned for post-v1.0.

---

## Testing

### Pest

**Purpose:** Testing framework for feature and unit tests.

**Why selected:**
- Expressive, minimal syntax reduces test boilerplate.
- First-class Laravel integration via `pestphp/pest-plugin-laravel`.
- Built on PHPUnit — compatible with existing tooling and CI.
- Architectural testing support for enforcing layer dependencies.

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| PHPUnit | Pest is built on PHPUnit and provides a better developer experience with less syntax. |
| Codeception | More complex setup. Pest covers oblok's testing needs with less configuration. |

---

## Code Quality

### Laravel Pint

**Purpose:** Code formatting (PHP-CS-Fixer wrapper with Laravel presets).

**Why selected:**
- Zero-configuration for Laravel projects.
- Enforces consistent code style automatically.
- Runs in CI to prevent style drift.

### PHPStan

**Purpose:** Static analysis to catch bugs before runtime.

**Why selected:**
- Detects type errors, undefined variables, unreachable code, and incorrect method calls.
- Larastan extension provides Laravel-specific rules.
- Progressive — can increase strictness level over time.

**Configuration:**
- Start at level 5. Increase to level 8+ as the codebase matures.

---

## Infrastructure

### Docker Compose

**Purpose:** Container orchestration for local development and self-hosted production deployments.

**Why selected:**
- Single `docker compose up` command to start the entire stack.
- Reproducible environments across development and production.
- Eliminates "works on my machine" issues.
- Standard tooling — most engineers are already familiar with Docker.

**Tradeoffs:**
- Docker adds overhead compared to running PHP natively. Acceptable for operational tooling.
- Container networking can complicate debugging. Mitigated with proper logging and health checks.

**Future considerations:**
- Kubernetes Helm chart for teams that run on Kubernetes.
- One-click deployment scripts for common VPS providers (DigitalOcean, Hetzner).

### Nginx

**Purpose:** Reverse proxy and web server.

**Why selected:**
- Industry standard for PHP applications.
- Handles static file serving, SSL termination, and request buffering.
- Low resource usage compared to Apache.

**Alternatives considered:**

| Alternative | Reason Not Selected |
|------------|-------------------|
| Apache | Higher resource usage. Less performant for high-concurrency workloads. |
| Caddy | Automatic HTTPS is appealing, but Nginx is more widely understood and documented. |
| FrankenPHP | Promising but newer. Nginx is a safer choice for a project targeting production stability. |

---

## CI/CD

### GitHub Actions

**Purpose:** Continuous integration (test, lint, static analysis on every push).

**Why selected:**
- Native GitHub integration. No additional service to configure.
- Free for public repositories.
- Large marketplace of pre-built actions.
- YAML-based configuration versioned alongside code.

**Pipeline stages:**

| Stage | Tools |
|-------|-------|
| Lint | Laravel Pint |
| Static Analysis | PHPStan (Larastan) |
| Test | Pest |
| Build | Docker Compose |

**Future considerations:**
- Add deployment automation when self-hosted deployment pipeline is defined.
- Cache Composer dependencies between runs for faster CI.
