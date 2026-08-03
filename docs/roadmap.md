# Roadmap

> Development timeline and milestones for Project oblok.
>
> Each version has a theme, a focused set of deliverables, and clear goals.
>
> For detailed requirements, see [spec.md](spec.md). For architecture decisions, see [architecture.md](architecture.md).

---

## Versioning

oblok uses semantic versioning. Pre-1.0 releases indicate active development. Breaking changes may occur between minor versions before v1.0.

---

## v0.1 — Foundation

**Theme:** Core platform and first operational value.

**Goal:** A user can deploy oblok, create projects, and monitor services.

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
| Queue Monitoring | Job queue visibility, job inspection, retry failed jobs — metrics ingested from any queue (Laravel, Bull, RQ, Kafka) via the metrics API, decoupled from oblok's own Redis |
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
| Custom Metrics | Ingest and store application-defined metrics — ✅ delivered (Phase 16) agentless push endpoint (POST counters/gauges/histograms) plus Prometheus-compatible scrape source |
| Metric Dashboards | Configurable charts and time-range selectors — ✅ delivered (Phase 16) |
| Resource Monitoring | Host and container CPU, memory, disk, and network metrics via Prometheus scrape (node_exporter, cAdvisor, app `/metrics`) |
| Request Monitoring | Per-endpoint request counts, status codes, and latency derived from access logs (agent) or injected middleware |
| Advanced Check Types | TCP, TLS/certificate-expiry, DNS, and HTTP-with-expectations health checks beyond plain HTTP — ✅ delivered (Phase 18) |
| API Key Management | Issue, rotate, and revoke API keys per project — ✅ delivered (Phase 14) |
| API Usage Tracking | Request counts and rate limiting per key — ✅ delivered (Phase 14) |

---

## v0.4 — Integrations

**Theme:** Connect oblok with external services and scheduled operations.

**Goal:** A user can inspect webhooks, monitor cron jobs, and connect third-party services.

| Deliverable | Description |
|-------------|-------------|
| Webhook Inspector | Log incoming webhooks with full payload capture |
| Webhook Replay | Re-deliver captured webhooks for debugging |
| Scheduler Monitoring | Track cron job execution and detect missed runs |
| GitHub Integration | Link projects to repositories, surface commit and PR context |
| Slack Integration | Send notifications and alerts to Slack channels — ✅ delivered (Phase 15, driver framework ready for Discord/Telegram) |
| Integration Framework | Extensible interface for adding new integrations |
| oblok Agent (oblok-agent) | Optional single binary that runs beside any project — ✅ delivered (Phase 17) tails stdout/log files, reads access logs, collects resource stats, and pushes logs, metrics, and request data to oblok APIs with zero app-code injection |

---

## v0.5 — Intelligence

**Theme:** Apply AI to operational workflows.

**Goal:** A user can ask oblok questions about their systems and receive intelligent assistance.

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

**Goal:** oblok is reliable enough for teams to depend on in production.

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
| Multi-tenancy | Support multiple teams on a single oblok installation |
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

## Cross-Cutting: Stack-Independent Monitoring

oblok treats every monitored project as a black box, regardless of framework (Laravel, Node, Python, Go, static sites). This principle guides every monitoring deliverable.

- **Agentless-first**: oblok is push-based by design — apps (or a sidecar) POST to its REST APIs. Only health checks are outbound (oblok pings the target).
- **Zero-injection**: Health checks, deployments, incidents, and webhooks need no app changes. Logs, request, and resource monitoring work via the optional `oblok-agent`, so no project code or package is required.
- **Prometheus-compatible**: Resource and custom metrics reuse the Prometheus ecosystem (node_exporter, cAdvisor, app `/metrics`) rather than reinventing collection.
- **Mapped in this roadmap**: resource + request monitoring and advanced check types (v0.3), `oblok-agent` + integration framework (v0.4), queue decoupling (v0.2).

---

## Milestone Status

| Version | Status　　　　　　| Notes |
| ---------| -------------------| -------|
| v0.1    | 🔵 In Development | MVP   |
| v0.2    | 🔵 In Development | Log Aggregation, Queue Monitoring, Notifications, Alert Rules delivered (Phases 5–10); Realtime Updates pending |
| v0.3    | 🔵 In Development | Deployment Tracking + History delivered (Phase 5); API Key Management + Usage Tracking delivered (Phase 14); Custom Metrics + Metric Dashboards delivered (Phase 16); Resource + Request Monitoring planned |
| v0.4    | 🔵 In Development | Webhook Inspector + Replay delivered (Phase 11); Scheduler Monitoring delivered (Phase 12); GitHub Integration delivered (Phase 13); API Key Management + Usage Tracking delivered (Phase 14); Slack Integration delivered (Phase 15); oblok Agent delivered (Phase 17) |
| v0.5    | ⚪ Planned　　　　 |       |
| v1.0    | ⚪ Planned　　　　 |       |
