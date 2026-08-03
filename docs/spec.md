# Specification

> Product Requirements Document for Project oblok.
>
> This document defines **what** oblok builds. It does not describe how it is built.
>
> For why oblok exists, see [vision.md](vision.md). For architecture, see [architecture.md](architecture.md). For timeline, see [roadmap.md](roadmap.md).

---

## Documentation Strategy

oblok maintains a small set of focused documents. Each file owns one responsibility.

| Document | Responsibility |
|----------|---------------|
| [vision.md](vision.md) | Why oblok exists |
| [spec.md](spec.md) | What to build (this document) |
| [architecture.md](architecture.md) | How it is built |
| [design.md](design.md) | UX patterns and user flows |
| [tech-stack.md](tech-stack.md) | Technology choices and rationale |
| [development.md](development.md) | Engineering process and standards |
| [roadmap.md](roadmap.md) | Timeline and milestones |
| [AGENTS.md](../AGENTS.md) | AI agent coding rules |
| [DESIGN.md](../DESIGN.md) | Design system (tokens, components) |
| [README.md](../README.md) | Public-facing project introduction |

Documents reference each other instead of duplicating content.

---

## Repository Structure

```
oblok/
├── app/                  # Application code (Actions, Controllers, Models, Events)
├── bootstrap/            # Application bootstrap configuration
├── config/               # Application configuration
├── database/             # Migrations, seeders, factories
├── docker/               # Docker configuration (Nginx, PHP Dockerfile)
├── docs/                 # Project documentation
├── resources/            # Views (Blade templates), CSS (Tailwind), JS (Alpine)
├── routes/               # Route definitions (web.php, api.php, auth.php)
├── storage/              # Application storage and logs
├── tests/                # Pest test suites (Feature and Unit)
├── .github/              # CI workflows (GitHub Actions)
├── docker-compose.yml    # Root container orchestration
├── AGENTS.md             # AI agent engineering guide
├── DESIGN.md             # Design system
├── README.md             # Project introduction
└── composer.json         # Composer dependencies
```

---

## Product Overview

oblok is a self-hosted Developer Operations Platform. It provides engineering teams with a unified workspace for operating backend applications.

The platform consolidates operational concerns — monitoring, deployments, logs, queues, webhooks, incidents, and notifications — into a single interface, eliminating the need to context-switch between multiple tools.

See [vision.md](vision.md) for the full mission and philosophy.

---

## MVP Scope

The MVP (v0.1) focuses on four modules that provide immediate operational value.

### Authentication

- User registration and login.
- Session management.
- Password reset.
- Account settings.

### Projects

- Create, read, update, and delete projects.
- Each project represents one application or service.
- Project-level settings and metadata.
- Project listing with search and filtering.

### Dashboard

- Per-project overview dashboard.
- Summary cards showing key operational metrics.
- Quick access to recent activity.
- Status indicators for connected services.

### Service Monitoring

- Register services (URLs or endpoints) to monitor.
- Periodic health checks with configurable intervals.
- Uptime tracking and history.
- Status indicators (healthy, degraded, down).
- Incident detection when health checks fail.

---

## Functional Requirements

### FR-01: Authentication

| ID | Requirement |
|----|-------------|
| FR-01.1 | Users can register with email and password |
| FR-01.2 | Users can log in and receive a session |
| FR-01.3 | Users can reset their password via email |
| FR-01.4 | Users can update their profile and account settings |
| FR-01.5 | Sessions expire after a configurable period of inactivity |
| FR-01.6 | API authentication uses token-based auth |

### FR-02: Projects

| ID | Requirement |
|----|-------------|
| FR-02.1 | Users can create projects with a name and optional description |
| FR-02.2 | Users can list all projects they have access to |
| FR-02.3 | Users can search and filter the project list |
| FR-02.4 | Users can update project details |
| FR-02.5 | Users can archive or delete projects |
| FR-02.6 | Each project has a unique slug for URL routing |
| FR-02.7 | Projects support metadata fields (repository URL, environment, tech stack) |

### FR-03: Dashboard

| ID | Requirement |
|----|-------------|
| FR-03.1 | Each project has a dedicated dashboard view |
| FR-03.2 | Dashboard displays summary cards for key metrics |
| FR-03.3 | Dashboard shows recent activity feed |
| FR-03.4 | Dashboard includes quick-action shortcuts |
| FR-03.5 | Dashboard data refreshes without full page reload |

### FR-04: Service Monitoring

| ID | Requirement |
|----|-------------|
| FR-04.1 | Users can register HTTP endpoints for monitoring |
| FR-04.2 | Health checks run at configurable intervals (minimum 30 seconds) |
| FR-04.3 | Check results are stored for historical analysis |
| FR-04.4 | Services display current status (healthy, degraded, down) |
| FR-04.5 | Status changes trigger events for downstream processing |
| FR-04.6 | Users can view uptime percentage over configurable time ranges |
| FR-04.7 | Users can pause and resume monitoring for individual services |
| FR-04.8 | Response time is recorded and graphed |

---

## Non-Functional Requirements

### Performance

| ID | Requirement |
|----|-------------|
| NFR-01 | Dashboard pages load in under 500ms on standard hardware |
| NFR-02 | API responses return in under 200ms for non-aggregated queries |
| NFR-03 | Health check scheduling does not degrade under 100 monitored services |
| NFR-04 | Background jobs process within 5 seconds of dispatch under normal load |

### Security

| ID | Requirement |
|----|-------------|
| NFR-05 | All user input is validated server-side |
| NFR-06 | Passwords are hashed using bcrypt or argon2 |
| NFR-07 | CSRF protection on all state-changing requests |
| NFR-08 | Rate limiting on authentication endpoints |
| NFR-09 | No secrets stored in version control |
| NFR-10 | SQL injection prevented through parameterized queries |

### Reliability

| ID | Requirement |
|----|-------------|
| NFR-11 | Application recovers gracefully from database connection loss |
| NFR-12 | Failed background jobs retry with exponential backoff |
| NFR-13 | Monitoring continues operating if the dashboard is unreachable |
| NFR-14 | Data integrity maintained through foreign key constraints |

### Accessibility

| ID | Requirement |
|----|-------------|
| NFR-15 | WCAG AA compliance for all interactive elements |
| NFR-16 | Full keyboard navigation support |
| NFR-17 | Screen reader compatibility for core workflows |
| NFR-18 | Respects `prefers-reduced-motion` and `prefers-color-scheme` |

---

## User Stories

### Authentication

- As a new user, I can create an account so I can access the platform.
- As a returning user, I can log in so I can resume my work.
- As a user who forgot my password, I can reset it via email so I regain access.
- As a logged-in user, I can update my profile so my information stays current.

### Projects

- As a user, I can create a project so I can organize monitoring for one application.
- As a user, I can view all my projects so I can quickly navigate to the one I need.
- As a user, I can search my projects so I can find them quickly when I have many.
- As a user, I can archive a project I no longer maintain so it does not clutter my workspace.

### Dashboard

- As a user, I can view my project dashboard so I get an immediate overview of its status.
- As a user, I can see recent activity so I know what changed recently.
- As a user, I can access common actions from the dashboard so I can work efficiently.

### Service Monitoring

- As a user, I can register a URL for monitoring so oblok checks if my service is running.
- As a user, I can view the current status of all my services so I know what needs attention.
- As a user, I can view uptime history so I can identify patterns in outages.
- As a user, I can pause monitoring for a service during planned maintenance so I do not receive false alerts.

---

## Constraints

| Constraint | Detail |
|------------|--------|
| Team size | Single developer (initially) |
| Budget | Zero infrastructure cost beyond personal hardware |
| Framework | Laravel 13 (committed; see [tech-stack.md](tech-stack.md)) |
| Database | PostgreSQL (committed) |
| Deployment | Docker Compose (self-hosted only for v1) |
| Frontend | Server-rendered Blade with Alpine.js (no SPA framework) |
| Timeline | MVP (v0.1) targeted for initial development phase |

---

## Risks

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Scope creep beyond MVP | High | High | Strict MVP boundary. Future features tracked in [roadmap.md](roadmap.md), not in active development. |
| Over-engineering early | Medium | Medium | YAGNI principle enforced. Build only what is needed for the current milestone. |
| Performance bottlenecks with monitoring at scale | Low (MVP) | Medium | Design monitoring with background jobs from day one. Optimize after profiling. |
| Docker complexity for first-time users | Medium | Medium | Provide a single `docker compose up` command. Document prerequisites clearly in [README.md](../README.md). |
| Maintaining documentation alongside code | Medium | High | Documentation-first workflow enforced in [development.md](development.md) and [AGENTS.md](../AGENTS.md). |

---

## Future Modules

These modules are planned for post-MVP development. See [roadmap.md](roadmap.md) for timeline.

| Module | Description |
|--------|-------------|
| Logs | Centralized log aggregation, real-time log stream, and HTTP access log inspection (method, status, latency, user-agent filters) |
| Queue Monitoring | Job queue visibility with inspection and retry |
| Notifications | Multi-channel alerting (email, Slack, webhooks) |
| Deployments | Deployment tracking and history |
| Metrics | Custom metric collection and dashboards |
| API Management | API key management and usage tracking |
| Webhook Inspector | Incoming webhook logging and replay |
| Scheduler | Cron job monitoring and failure detection |
| Integrations | Third-party service connections (GitHub, Slack, PagerDuty) |
| AI Assistant | Natural language operational queries, anomaly detection, automated runbooks |
| Incident Management | Incident lifecycle tracking with timeline and postmortems |
| Team Collaboration | Multi-user access, roles, permissions, and activity feeds |
| Analytics | Operational analytics and trend reporting |

---

## Success Criteria

The MVP is considered successful when:

1. A user can register, log in, and manage their account.
2. A user can create and manage projects.
3. A user can register services for monitoring and view their status.
4. The dashboard provides a useful overview of project health.
5. The application deploys via Docker Compose with a single command.
6. All MVP features have test coverage.
7. Documentation is complete and accurate.
8. The codebase demonstrates production-grade architecture.
