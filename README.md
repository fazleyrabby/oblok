# oblok

A self-hosted Developer Operations Platform for engineering teams.

oblok provides a unified workspace for monitoring services, tracking deployments, inspecting queues, managing incidents, and operating backend applications — without depending on third-party SaaS.

---

## Why oblok

Engineering teams typically rely on a fragmented set of tools to operate their applications: one for uptime monitoring, another for log aggregation, another for deployment tracking, and yet another for queue inspection. Each tool requires its own account, configuration, and mental model.

oblok consolidates these operational concerns into a single, self-hosted platform. One deployment. One interface. Full ownership of your data.

See [docs/vision.md](docs/vision.md) for the full project philosophy.

---

## Features

### Available (MVP)

- **Authentication** — Registration, login, password reset, session management.
- **Project Management** — Organize services by project with metadata and search.
- **Dashboard** — Per-project operational overview with summary metrics and activity feed.
- **Service Monitoring** — HTTP health checks with configurable intervals, uptime tracking, and status history.

### Planned

- Log Aggregation
- Queue Monitoring
- Notifications and Alerting
- Deployment Tracking
- Custom Metrics
- API Key Management
- Webhook Inspector
- Scheduler Monitoring
- Third-Party Integrations
- AI-Assisted Operations

See [docs/roadmap.md](docs/roadmap.md) for the full development timeline.

---

## Screenshots

> Screenshots will be added once the MVP interface is complete.

---

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | Laravel 13 / PHP 8.4 |
| Database | PostgreSQL |
| Cache / Queue | Redis |
| Realtime | Laravel Reverb |
| Frontend | Blade, Alpine.js, Tailwind CSS |
| Charts | ApexCharts |
| Testing | Pest |
| Infrastructure | Docker Compose, Nginx |
| CI | GitHub Actions |

See [docs/tech-stack.md](docs/tech-stack.md) for detailed rationale and tradeoffs.

---

## Prerequisites

- Docker and Docker Compose
- Git

---

## Quick Start

```bash
# Clone the repository
git clone https://github.com/fazleyrabby/project-oblok.git
cd project-oblok

# Install PHP & JS dependencies
composer install
npm install && npm run build

# Copy environment configuration
cp .env.example .env
php artisan key:generate

# Start Docker containers (optional)
docker compose up -d

# Run database migrations
php artisan migrate

# Run Pest tests
./vendor/bin/pest
```

---

## Documentation

| Document | Description |
|----------|-------------|
| [Vision](docs/vision.md) | Why oblok exists — mission, philosophy, principles |
| [Specification](docs/spec.md) | Product requirements and MVP scope |
| [Architecture](docs/architecture.md) | System design, Clean Architecture, module boundaries |
| [UX Design](docs/design.md) | Navigation, layouts, user flows, interaction patterns |
| [Design System](DESIGN.md) | Visual language — colors, typography, components |
| [Tech Stack](docs/tech-stack.md) | Technology choices with rationale and tradeoffs |
| [Development](docs/development.md) | Engineering handbook — standards, Git workflow, testing |
| [Roadmap](docs/roadmap.md) | Development timeline from v0.1 to v1.0+ |
| [AI Agents](AGENTS.md) | Guidelines for AI coding agents |

---

## Roadmap

| Version | Theme | Status |
|---------|-------|--------|
| v0.1 | Foundation — Auth, Projects, Dashboard, Monitoring | 🔵 In Development |
| v0.2 | Observability — Logs, Queues, Notifications | ⚪ Planned |
| v0.3 | Control Plane — Deployments, Metrics, API Keys | ⚪ Planned |
| v0.4 | Integrations — Webhooks, Scheduler, Third-Party | 🔵 In Development |
| v0.5 | Intelligence — AI Assistant | ⚪ Planned |
| v1.0 | Public Release — Stability, Docs, Hardening | ⚪ Planned |

See [docs/roadmap.md](docs/roadmap.md) for detailed milestone contents.

---

## Contributing

Contributions are welcome. Before contributing:

1. Read [docs/vision.md](docs/vision.md) to understand the project direction.
2. Read [docs/development.md](docs/development.md) for coding standards and workflow.
3. Read [AGENTS.md](AGENTS.md) if using AI coding assistance.

### Process

1. Fork the repository.
2. Create a feature branch from `main`.
3. Make focused, well-tested changes.
4. Submit a pull request following the [PR checklist](docs/development.md#pull-request-checklist).

---

## License

oblok is open-source software. See [LICENSE](LICENSE) for details.
