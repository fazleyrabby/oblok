# Roadmap

> Development timeline and milestones for Project Atlas.
>
> Each version has a theme, a focused set of deliverables, and clear goals.
>
> For detailed requirements, see [spec.md](spec.md). For architecture decisions, see [architecture.md](architecture.md).

---

## Versioning

Atlas uses semantic versioning. Pre-1.0 releases indicate active development. Breaking changes may occur between minor versions before v1.0.

---

## v0.1 — Foundation

**Theme:** Core platform and first operational value.

**Goal:** A user can deploy Atlas, create projects, and monitor services.

| Deliverable | Description |
|-------------|-------------|
| Authentication | Registration, login, password reset, session management |
| Project Management | CRUD operations, search, filtering, project metadata |
| Dashboard | Per-project overview with summary cards and activity feed |
| Service Monitoring | HTTP health checks, uptime tracking, status history |
| API v1 (partial) | RESTful endpoints for all MVP resources |
| Docker Compose | Single-command deployment for development and production |
| Documentation | All foundational docs complete and accurate |
| Test Suite | Feature and unit tests for all MVP modules |

---

## v0.2 — Observability

**Theme:** Give engineers visibility into what their applications are doing.

**Goal:** A user can inspect logs, monitor queues, and receive notifications when something goes wrong.

| Deliverable | Description |
|-------------|-------------|
| Log Aggregation | Ingest, store, search, and filter application logs |
| Queue Monitoring | Job queue visibility, job inspection, retry failed jobs |
| Notifications | Multi-channel alerting (email, Slack, webhook) with configurable rules |
| Alert Rules | Threshold-based alerts tied to monitoring and queue metrics |
| Realtime Updates | WebSocket-powered live updates for dashboards and logs |

---

## v0.3 — Control Plane

**Theme:** Track deployments, collect metrics, and manage API access.

**Goal:** A user can track what is deployed, visualize custom metrics, and issue API keys.

| Deliverable | Description |
|-------------|-------------|
| Deployment Tracking | Record deployments with metadata (commit, environment, status) |
| Deployment History | Timeline view with rollback indicators |
| Custom Metrics | Ingest and store application-defined metrics |
| Metric Dashboards | Configurable charts and time-range selectors |
| API Key Management | Issue, rotate, and revoke API keys per project |
| API Usage Tracking | Request counts and rate limiting per key |

---

## v0.4 — Integrations

**Theme:** Connect Atlas with external services and scheduled operations.

**Goal:** A user can inspect webhooks, monitor cron jobs, and connect third-party services.

| Deliverable | Description |
|-------------|-------------|
| Webhook Inspector | Log incoming webhooks with full payload capture |
| Webhook Replay | Re-deliver captured webhooks for debugging |
| Scheduler Monitoring | Track cron job execution and detect missed runs |
| GitHub Integration | Link projects to repositories, surface commit and PR context |
| Slack Integration | Send notifications and alerts to Slack channels |
| Integration Framework | Extensible interface for adding new integrations |

---

## v0.5 — Intelligence

**Theme:** Apply AI to operational workflows.

**Goal:** A user can ask Atlas questions about their systems and receive intelligent assistance.

| Deliverable | Description |
|-------------|-------------|
| AI Assistant | Natural language queries against operational data |
| Anomaly Detection | Automatic detection of unusual metric patterns |
| Incident Suggestions | AI-generated root cause hypotheses based on correlated data |
| Runbook Automation | Execute predefined operational procedures from natural language commands |
| Smart Alerts | Reduce alert noise through pattern recognition and deduplication |

---

## v1.0 — Public Release

**Theme:** Stability, polish, and production readiness.

**Goal:** Atlas is reliable enough for teams to depend on in production.

| Deliverable | Description |
|-------------|-------------|
| Stability | All known critical bugs resolved |
| Performance | Profiled and optimized for target workloads (see [spec.md](spec.md)) |
| Security Audit | OWASP compliance review, dependency audit |
| Documentation | Complete user guide, API reference, deployment guide |
| Migration Tooling | Safe upgrade path between versions |
| Accessibility | Full WCAG AA compliance audit |
| API v1 (complete) | Stable, versioned API covering all features |
| Onboarding | First-run setup wizard and guided tour |

---

## Beyond v1.0

These are exploratory goals. They are not committed and will be scoped based on community feedback and usage patterns.

### v1.x — Ecosystem

| Idea | Description |
|------|-------------|
| Plugin System | Allow third-party extensions to add modules, integrations, and dashboard widgets |
| Theme Customization | User-configurable color schemes and branding |
| Multi-tenancy | Support multiple teams on a single Atlas installation |
| Role-Based Access Control | Fine-grained permissions beyond basic authentication |
| Audit Log | Track all user actions for compliance and debugging |
| Command Palette | Keyboard-first navigation and actions (⌘K) |

### v2.x — Scale

| Idea | Description |
|------|-------------|
| Horizontal Scaling | Support for multi-node deployments behind a load balancer |
| Distributed Monitoring | Run health checks from multiple geographic locations |
| Data Retention Policies | Configurable retention periods for logs, metrics, and check results |
| Marketplace | Community-contributed plugins and integrations |
| Mobile Companion | Lightweight mobile interface for on-call engineers |
| SSO / SAML | Enterprise authentication integration |

---

## Milestone Status

| Version | Status　　　　　　| Notes |
| ---------| -------------------| -------|
| v0.1    | 🔵 In Development | MVP   |
| v0.2    | ⚪ Planned　　　　 |       |
| v0.3    | ⚪ Planned　　　　 |       |
| v0.4    | ⚪ Planned　　　　 |       |
| v0.5    | ⚪ Planned　　　　 |       |
| v1.0    | ⚪ Planned　　　　 |       |
