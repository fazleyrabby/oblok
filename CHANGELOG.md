# Changelog

All notable changes to Project oblok will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

#### Phase 22 — Frontend Performance & Brand Polish
- **ApexCharts bundled locally**: Removed the render-blocking `cdn.jsdelivr.net` ApexCharts script from the Metrics, Request Analytics, Server Resources, and Services pages. The library is now bundled via Vite and exposed as `window.ApexCharts` (`resources/js/app.js`), with guards so charts degrade gracefully if the library is ever unavailable. Fixes the "UI freezes and the chart never renders" behavior when the CDN is slow or unreachable.
- **Smooth analytics live-refresh**: Request Analytics and Server Resources no longer destroy and recreate their charts on every 5s live tick. They update in place via `chart.updateSeries()` (no full re-render blink), and the request-count chart pins `yaxis.min: 0` to avoid ApexCharts' `RangeError: Invalid array length` on flat/empty data.
- **Agent container memory fix**: `SystemMetricsCollector` no longer emits `container_memory_usage_percent` when the container has no real cgroup memory limit (`memory.max = 'max'`). It previously fell back to host RAM as the denominator and pushed a meaningless flat ~0.07% series.
- **Homepage redesign**: Rewrote `resources/views/welcome.blade.php` as a self-hosted positioning page — hero with a real console preview chart, capability bento grid, self-hosted trust section, real Docker install snippet, and corrected GitHub URL (`fazleyrabby/oblok`). No fabricated metrics or testimonials.
- **Render-inspired teal palette**: Remapped the `indigo` scale in `tailwind.config.js` to a teal accent (`#46e1d5` highlights, `#0f766e` button fills for AA contrast), re-theming the homepage and every dashboard page. Updated ApexCharts series colors and scrollbar hover accents accordingly.
- **Subtle corners & calmer motion**: Reduced homepage corner radii to `rounded-md`/`rounded`, and removed the animated pulse dots from the hero badge and console preview.

#### Phase 21 — Realtime Data Streaming & Auto-Refresh (v0.2)
- **Laravel Reverb WebSockets**: Installed and configured Reverb as the broadcast driver with authenticated private project channels (`projects.{id}`).
- **Broadcast Events**: Added `ServiceHealthChanged`, `AlertTriggered`, and `DeploymentStatusChanged` broadcast events dispatched from health checks, alert rules, and deployment webhooks; each carries a rich payload on a per-project channel.
- **Channel Authorization**: Registered `routes/channels.php` (owner or project member only) and wired it through `bootstrap/app.php`.
- **Live Stream Indicator**: Added reusable `<x-live-indicator>` component with a pulsing green `Live: ON/OFF` toggle.
- **Auto-Refresh Polling**: Server Resources and Request Analytics dashboards now refetch their data every 5s while Live is ON — no full page reload.
- **Frontend Echo Client**: Added `resources/js/realtime.js` with a Laravel Echo (Reverb) client and toast notifications for health/alert/deployment events, injected via `data-project-id` on the layout body.
- **Environment-Aware Agent** (from resource monitoring work): `SystemMetricsCollector` auto-detects container vs bare-metal, tags every sample, and only reports container RAM inside containers; the dashboard hides the Container RAM card and shows a Host/Container badge accordingly.
- **Bare-Metal Docs**: Added section 4c with systemd/supervisor setup for non-Dockerized projects.
- **Testing & Quality**: Added `tests/Feature/Realtime/RealtimeTest.php`; **273 Pest tests**, zero PHPStan / Pint errors.

#### Phase 20 — Resource & Server Monitoring (v0.3)
- **Server Resources Dashboard**: Built `ResourceMonitoringController` and web view (`resources/views/resources/index.blade.php`) under **Observability > Server Resources**.
- **Container & Host Metrics**: Updated `SystemMetricsCollector` to measure Docker cgroups memory limits (`/sys/fs/cgroup/memory.current` vs `memory.max`) alongside host system RAM, CPU load, and disk utilization.
- **Metrics Aggregation**: Created `QueryResourceMetrics` action class to query and calculate Host CPU %, Host RAM %, Container RAM %, and Disk utilization % across timeframe selectors (`1H`, `6H`, `24H`, `7D`).
- **Sidebar Integration**: Added persistent **Server Resources** navigation link in `sidebar.blade.php`.
- **Testing & Quality**: Passed 262 Pest tests with 0 PHPStan and Pint errors.

#### Phase 19 — Request Analytics & HTTP Traffic Dashboard (v0.3)
- **Dedicated Request Analytics View**: Built `RequestAnalyticsController` and web view (`resources/views/request-analytics/index.blade.php`) under **Observability > Request Analytics**.
- **Metrics & Log Enrichment**: Extracted Client IP Address and User Agent headers in `AccessLogParser` (`oblok-agent`) and surfaced enriched request log history in the web UI table.
- **Status & Method Breakdown**: Bucketed HTTP traffic into stacked 2xx success, 3xx redirect, 4xx client error, and 5xx server error series with real-time method counters (`GET`, `POST`, `PUT`, `DELETE`).
- **Sidebar Integration**: Added persistent **Request Analytics** link in `sidebar.blade.php`.
- **Testing & Quality**: Passed 260 Pest tests and zero PHPStan / Laravel Pint errors.

#### Phase 18 — Advanced Health Check Types (v0.3)
- **Check Drivers & Registry**: Implemented `HealthCheckerRegistry` to dynamically resolve probes by type (`http`, `tcp`, `tls`, `dns`).
- **TCP Port Probes**: Added `TcpHealthChecker` to verify raw socket connectivity and measure latency to any host/port.
- **TLS Certificate Expiry**: Added `TlsHealthChecker` to inspect SSL certificates and alert when remaining validity drops below configured thresholds (`min_cert_days`).
- **DNS Record Probes**: Added `DnsHealthChecker` to verify DNS resolution (`A`, `AAAA`, `CNAME`, `MX`, `TXT`) and expected target value matching.
- **HTTP Assertions**: Enhanced `HttpHealthChecker` to validate regex patterns or substrings in response bodies and verify custom response headers.
- **Database & Requests**: Added `config` JSON column to `services` table and updated `StoreServiceRequest`/`UpdateServiceRequest` validation.
- **Testing & Quality**: Passed 258 Pest tests and zero PHPStan / Laravel Pint errors.

#### Phase 17 — oblok Agent (v0.4)
- **Standalone Agent**: Built `oblok-agent/` — a dependency-free, stack-independent PHP CLI shipper that runs beside any project and pushes data to oblok. No changes to the monitored application are required.
- **Log Shipping**: `LogLineParser` handles JSON log lines, Laravel text lines (`[2026-08-02 12:00:00] production.ERROR: message`), and plain lines; `FileTailer` tails from the end, survives rotation/truncation, and forwards each line to `POST /api/v1/projects/{project}/logs`.
- **Request Metrics**: `AccessLogParser` parses nginx combined-format lines (optional `$request_time`); `RequestMetricsAggregator` aggregates per-minute `http_requests_total{method,status}` and `http_request_duration_seconds{method,status}` samples to the metrics API.
- **Config & Client**: `Config` reads env (`OBLOK_URL`, `OBLOK_API_KEY`, `OBLOK_PROJECT_ID`, `OBLOK_LOG_FILES`, `OBLOK_ACCESS_LOG`, poll/flush intervals); `ApiClient` authenticates with a Bearer API key. `bin/oblok-agent` supports `--log=` and `--access-log=` overrides.
- **Deployment**: `Dockerfile` (php:8.3-cli-alpine + curl) and `docker-compose.oblok-agent.yml` sidecar example with read-only log/nginx mounts.
- **Testing**: Added Pest unit tests (`tests/Unit/Agent/AgentTest.php`) covering the parsers, aggregator, config, and tailer rotation behavior (**252 total passing tests**, 782 assertions).

#### Phase 16 — Custom Metrics, Prometheus Scrape & Dashboards (v0.3)
- **Database & Models**: Created `metric_samples` and `metric_targets` migrations (`2026_08_02_000020_create_metric_tables.php`). Built `MetricSample` (labels, value, recorded_at) and `MetricTarget` (Prometheus scrape targets) Eloquent models with UUID keys and project relations.
- **Ingestion**: Built `IngestMetrics` action (batched, cross-project) exposed as `POST /api/v1/projects/{project}/metrics`. Accepts counters/gauges with optional labels and timestamps; works with session auth or Bearer API keys.
- **Prometheus Compatibility**: Built `PrometheusExpositionParser` (`app/Services/Metrics/`) for the text exposition format (HELP/TYPE comments, labels, optional timestamps). `ScrapeMetricTarget` action + `ScrapeMetricTargetJob` fetch targets; `ScrapeAllMetricTargetsJob` is scheduled every minute in `routes/console.php`.
- **Dashboards**: Built `QueryMetricSeries` action that down-samples raw samples into bucketed chart series (avg/min/max/sum/last, label filters) — cross-DB (no SQL date-trunc). Web dashboard (`resources/views/metrics/`) with ApexCharts line chart, metric-name picker, time-range selectors (1H/6H/24H/7D), push-metrics cURL example, and scrape-target management.
- **Validation & Authorization**: Added `ingestMetrics` (Owner/Admin/Operator) and `manageMetrics` (Owner/Admin) abilities. Added `IngestMetricsRequest`, `StoreMetricTargetRequest`, `MetricSamplePolicy`, and `MetricTargetPolicy`.
- **Controllers & Routes**: Built Web and REST API V1 `MetricController` (dashboard, chart data, ingestion, target CRUD). Added `projects/{project}/metrics` routes (web + `api/v1`).
- **API Resources**: Added `MetricTargetResource`.
- **Configuration**: Added `oblok.metrics` config block (`scrape_timeout`) and `.env.example` entry (`METRICS_SCRAPE_TIMEOUT`).
- **Testing**: Added Pest unit tests (`tests/Unit/MetricsTest.php`) and feature tests (`tests/Feature/Metrics/`) covering the parser, ingestion, chart bucketing, scrape jobs, and authorization (**241 total passing tests**, 749 assertions).

#### Phase 15 — Messaging Integrations (v0.4)
- **Driver Framework**: Defined a `ChatPlatform` driver interface (`app/Services/Messaging/ChatPlatform.php`) with `verify()`/`channels()`/`send()` plus a `MessagingDriverRegistry` that resolves a driver per platform. Adding a new platform (Discord, Telegram, …) is a new enum case + driver + registry entry.
- **Slack Driver**: Implemented `SlackDriver` (`app/Services/Messaging/Drivers/`) against the Slack Web API — `auth.test` (verify + workspace metadata), `conversations.list` (channel picker), and `chat.postMessage` (send). Transport and `ok:false` API failures surface as a domain `MessagingApiException`; the bot token is stored encrypted at rest.
- **Database & Models**: Created `messaging_integrations` migration (`2026_08_02_000019_create_messaging_integrations_table.php`) and `MessagingIntegration` Eloquent model (UUID keys, one integration per project+platform, encrypted `config` text column, platform/enabled scopes). Added `MessagingPlatform` enum.
- **Actions & Jobs**: Built `ConnectMessagingIntegration` (validates credentials via the driver, then `updateOrCreate`), `DisconnectMessagingIntegration`, and `SendMessagingMessage`. Added `SendMessagingMessageJob` (queued, retried) for background posting.
- **Controllers & Routes**: Built Web and REST API V1 `MessagingIntegrationController` (index/store/channels/send/destroy). Added nested `projects/{project}/messaging` routes (web + `api/v1`).
- **Validation & Authorization**: Added `StoreMessagingIntegrationRequest` (platform enum + bot token) and `SendMessagingMessageRequest`. Reused the `manageIntegrations` ability (Owner/Admin) with `MessagingIntegrationPolicy`.
- **API Resources**: Added `MessagingIntegrationResource` and `ChatChannelResource` with consistent JSON envelopes.
- **Views & UI**: Built the messaging Blade view (`resources/views/messaging/`) with a connect form, workspace summary, channel selector, and message composer. Added Messaging to the sidebar navigation.
- **Configuration**: Added `oblok.messaging.slack` config block (`api_url`, `timeout`) and matching `.env.example` entries (`SLACK_API_URL`, `SLACK_API_TIMEOUT`).
- **Testing**: Added Pest unit tests (`tests/Unit/MessagingIntegrationTest.php`) and feature tests (`tests/Feature/Integrations/`) covering the driver contract, Slack API calls, encryption, connect/send/disconnect, and authorization (**222 total passing tests**, 695 assertions).

#### Phase 14 — API Key Management (v0.4)
- **Database & Models**: Created `api_keys` migration (`2026_08_02_000018_create_api_keys_table.php`) and `ApiKey` Eloquent model (UUID keys, `user_id` + `project_id` foreign keys). Keys are scoped to a single project, tokens are stored as SHA-256 hashes with a displayable `key_prefix`, and each key tracks `requests_count` and `last_used_at` plus optional `expires_at`/`revoked_at`.
- **Actions**: Built `CreateApiKey` (generates a prefixed plaintext token and returns it exactly once) and `RevokeApiKey`.
- **Machine Auth**: Added a custom `api_key` auth guard (`app/Auth/ApiKeyGuard.php`) that resolves the owning user from an `Authorization: Bearer` token, rejects revoked/expired keys, records usage, and re-resolves per request so long-running workers never reuse a stale identity. The REST V1 API now accepts both session auth and API keys (`auth:web,api_key`), with a per-project scope middleware (`EnsureApiKeyProjectScope`) that forbids cross-project access.
- **Rate Limiting**: Added a named `api_key` rate limiter (default 120 requests/minute per key, env `OBLOK_API_RATE_LIMIT`) applied to the V1 API.
- **Controllers & Routes**: Built Web and REST API V1 `ApiKeyController` (index/store/destroy). Added nested `projects/{project}/api-keys` routes (web + `api/v1`).
- **Validation & Authorization**: Added `StoreApiKeyRequest` (name + optional future expiry). Added `manageApiKeys` ability for Owner/Admin roles and `ApiKeyPolicy`.
- **API Resource**: Added `ApiKeyResource` with prefix, usage counters, and lifecycle timestamps (never the raw token).
- **Views & UI**: Built the API Keys Blade view (`resources/views/api-keys/`) with a generate form, one-time token display with copy button, and a lifecycle table (requests, last used, expiry, revoke). Added API Keys to the sidebar navigation.
- **Configuration**: Added `oblok.api_keys.prefix` and `oblok.api.rate_limit` config plus `.env.example` entries (`OBLOK_API_KEY_PREFIX`, `OBLOK_API_RATE_LIMIT`).
- **Testing**: Added Pest unit tests (`tests/Unit/ApiKeyTest.php`) and feature tests (`tests/Feature/Integrations/`) covering hashing, expiry/revocation, Bearer authentication, 401/403 paths, project scope enforcement, session fallback, rate limiting, and web/API key management (**202 total passing tests**, 634 assertions).

#### Phase 13 — GitHub Integration (v0.4)
- **Database & Models**: Created `github_integrations` (`2026_08_02_000015_create_github_integrations_table.php`), `github_commits` (`..._000016`), and `github_pull_requests` (`..._000017`) migrations. Built `GitHubIntegration`, `GitHubCommit`, and `GitHubPullRequest` Eloquent models (one integration per project) with access tokens encrypted at rest.
- **API Client**: Built `GitHubApiService` (`app/Services/GitHub/`) — a Laravel HTTP client for the GitHub REST API with typed `GitHubCommitData`/`GitHubPullRequestData` value objects and a domain `GitHubApiException`. The base URL is configurable for GitHub Enterprise (`GITHUB_API_URL`).
- **Actions & Jobs**: Built `ConnectGitHubIntegration` (validates the repository and detects the default branch), `SyncGitHubData` (upserts commit/PR snapshots), and `DisconnectGitHubIntegration`. Added `SyncGitHubDataJob` (queued, retried) and `SyncAllGitHubIntegrationsJob` scheduled every 15 minutes via `routes/console.php`.
- **Controllers & Routes**: Built Web and REST API V1 `GitHubIntegrationController` (index/store/sync/destroy plus commits and pull-requests listing endpoints). Added nested `projects/{project}/github` routes (web + `api/v1`).
- **Validation & Authorization**: Added `StoreGitHubIntegrationRequest` (validates `owner/name` repository format and token). Added `manageIntegrations` ability for Owner/Admin roles and `GitHubIntegrationPolicy`.
- **API Resources**: Added `GitHubIntegrationResource`, `GitHubCommitResource`, and `GitHubPullRequestResource` with consistent JSON envelopes.
- **Views & UI**: Built the GitHub integration Blade view (`resources/views/github/`) with a connect form, repository summary, recent-commit and open-PR context cards, and sync/disconnect actions. Added GitHub to the sidebar navigation.
- **Configuration**: Added `oblok.github` config block (`api_url`, `timeout`) and matching `.env.example` entries.
- **Testing**: Added Pest unit tests (`tests/Unit/GitHubIntegrationTest.php`) and feature tests (`tests/Feature/Integrations/`) covering the API client, encryption, connect/sync/disconnect, and authorization (**177 total passing tests**, 565 assertions).

#### Phase 12 — Scheduler Monitoring (v0.4)
- **Database & Models**: Created `scheduled_tasks` (`2026_08_02_000012_create_scheduled_tasks_table.php`) and `task_runs` (`2026_08_02_000013_create_task_runs_table.php`) migrations. Built `ScheduledTask` and `TaskRun` Eloquent models with UUID keys, plus `TaskRunStatus` enum (running/success/failed/missed/skipped) with labels and colors.
- **Cron Scheduling**: `ScheduledTask::calculateNextRun()` computes the next execution using `dragonmantank/cron-expression`, honoring the task timezone. `recordRun()` records a completed run and advances the schedule; `markMissed()` records a missed run and advances past the current time.
- **Missed-Run Detection**: Built `CheckScheduledTasksJob` (scheduled every minute in `routes/console.php`) that flags enabled tasks whose run window has passed beyond `oblok.scheduler.missed_grace_minutes` (env `OBLOK_SCHEDULER_MISSED_GRACE_MINUTES`, default 5) with a `missed` run.
- **Controllers & Routes**: Built Web and REST API V1 `ScheduledTaskController` (index/create/store/show/edit/update/destroy/recordRun). Added nested `projects/{project}/scheduled-tasks` routes (web + `api/v1`) with `{scheduledTask}` scoped binding and a `POST .../runs` recording endpoint.
- **Validation & Authorization**: Added `StoreScheduledTaskRequest` (validates cron expressions and timezones), `UpdateScheduledTaskRequest`, and `RecordTaskRunRequest`. Added `manageScheduler` ability for Owner/Admin/Operator roles and `ScheduledTaskPolicy`.
- **API Resources**: Added `ScheduledTaskResource` and `TaskRunResource` with consistent JSON envelopes.
- **Views & UI**: Built scheduler Blade views (`resources/views/scheduled-tasks/`) with task list, run-history table, record-run form for operators, and a cURL example for the runs endpoint. Added Scheduler to the sidebar navigation.
- **Testing**: Added Pest unit tests (`tests/Unit/ScheduledTaskTest.php`) and feature tests (`tests/Feature/Scheduler/`) covering cron math, run recording, missed-run detection, CRUD, and authorization (**160 total passing tests**, 493 assertions).

### Fixed

- **PostgreSQL compatibility**: Changed the `encrypted_config` column on `notification_channels` from `json` to `text` (`2026_08_02_000014_change_encrypted_config_to_text_on_notification_channels_table.php`). Laravel's `encrypted:array` cast stores an opaque base64 string that is not valid JSON, which PostgreSQL rejected at insert time (MySQL was lax). This resolves CI failures on the Phase 10/11 notification-channel tests.
- **Resource dashboard chart freeze** (`Server Resources`): `QueryResourceMetrics` no longer streams raw samples into ApexCharts. It down-samples each metric into a single collapsed series capped to 60 points, so a busy agent (container memory posted ~every 11s with high-cardinality `used_bytes`/`limit_bytes` labels) now yields a ~1 KB response instead of a ~156 KB / 1,418‑point payload that froze the browser.
- **Metric chart high-cardinality safeguard** (`Custom Metrics` page): `QueryMetricSeries` caps the number of returned label series (default `maxSeries = 20`, most data-dense first), preventing per-sample label combos from multiplying chart series.
- **Volatile container memory labels**: `SystemMetricsCollector` now emits only `type` and `environment` for container memory instead of `used_bytes`/`limit_bytes`, which changed on every sample and created unbounded label cardinality.
- **Dynamic Reverb WebSocket host**: The Echo client no longer bakes a fixed `127.0.0.1` host into the built bundle; it falls back to `window.location.hostname`, so realtime streams connect correctly over LAN, Tailscale, and local URLs without a rebuild.
- **Realtime server wiring**: Added an `oblok_reverb` service (`php artisan reverb:start`) to `docker-compose.yml` and wired the `broadcasts` queue into the worker, with `package:discover` on container start to handle freshly-installed packages.

#### Phase 11 — Webhook Inspector (v0.4)
- **Database & Models**: Created `webhook_calls` migration (`2026_08_02_000011_create_webhook_calls_table.php`) and `WebhookCall` Eloquent model with UUID keys, JSON casts, `ofEvent` scope, and a `webhookCalls()` relation on `Project`.
- **Capture**: Built `CaptureWebhook` action (`app/Actions/Webhooks/CaptureWebhook.php`). The deployment webhook receiver (`DeploymentWebhookController`) now records every incoming request — method, URL, headers, full payload, source IP, user agent, response status, and processing time.
- **Replay**: Built `ReplayWebhook` action (`app/Actions/Webhooks/ReplayWebhook.php`) that re-processes a captured deployment payload into a new deployment, incrementing `replay_count` and stamping `replayed_at`. Unsupported events are rejected with a clear error.
- **Controllers & Routes**: Built Web and REST API V1 `WebhookCallController` (index/show/replay). Added nested `projects/{project}/webhooks` routes (web + `api/v1`) with `{webhookCall}` scoped binding.
- **Authorization**: Added `manageWebhooks` ability for Owner/Admin/Operator roles and `WebhookCallPolicy` (view for any member, replay for operators and above).
- **API Resource**: Added `WebhookCallResource` with full payload/header details included on show/replay responses.
- **Views & UI**: Built webhook inspector Blade views (`resources/views/webhooks/`) with event badges, delivery metadata, pretty-printed payload/header JSON, and a replay action. Added Webhooks to the sidebar navigation.
- **Testing**: Added Pest unit tests (`tests/Unit/WebhookCallTest.php`) and feature tests (`tests/Feature/Webhooks/WebhookInspectorTest.php`) covering capture, listing, authorization, replay, and unsupported events (**142 total passing tests**, 433 assertions).

#### Phase 10 — Alerting & Notifications (v0.4)
- **Database & Models**: Created migrations for `alert_rules`, `notification_channels`, `alert_rule_channel`, `alert_events`, and `notification_deliveries`. Built `AlertRule`, `NotificationChannel`, `AlertEvent`, `NotificationDelivery`, and `AlertRuleChannel` (typed pivot with `recipient_filter`) Eloquent models, plus `AlertMetric`, `AlertComparison`, `AlertSeverity`, `DeliveryStatus`, and `NotificationChannelType` enums.
- **Metric Sources**: Implemented `MetricSourceRegistry` and pluggable metric sources for `ServiceHealth`, `QueueBacklog`, `DeploymentStatus`, and `IncidentOpened`, producing `MetricReading` values with context.
- **Actions & Jobs**: Built `DispatchAlertRule` (creates alert events and mail/webhook deliveries, respects cooldown and recipient filters) and `DeliverNotification` queued job. Added `EvaluateAlertRulesJob` scheduled to run every minute via `routes/console.php`.
- **Controllers & Routes**: Built Web and REST API V1 controllers for alert rules, notification channels, alert events, and delivery acknowledge/snooze. Added nested `projects/{project}/alerts/*`, `alert-rules`, and `notification-channels` routes (web + `api/v1`), with delivery actions scoped to `{project}/{delivery}`.
- **Validation & Authorization**: Added `StoreAlertRuleRequest`, `UpdateAlertRuleRequest`, `StoreNotificationChannelRequest`, `UpdateNotificationChannelRequest`, and `SnoozeNotificationDeliveryRequest`. Added `manageAlerts` ability for Owner/Admin roles and `AlertRulePolicy`, `NotificationChannelPolicy`, `AlertEventPolicy`.
- **API Resources**: Added `AlertRuleResource`, `NotificationChannelResource`, `AlertEventResource`, and `NotificationDeliveryResource` with consistent JSON envelopes.
- **Views & UI**: Built Blade views for alert rules (index/create/edit), notification channels (index/create/edit), and alerts center (index/show) with acknowledge/snooze actions. Added Alerts, Alert Rules, and Notification Channels to the sidebar and mobile navigation.
- **Security**: Notification channel credentials stored encrypted at rest via `encrypted_config`; `recipient_filter` restricted to valid project roles; deliveries scoped to project.
- **Testing**: Added Pest unit tests (`tests/Unit/AlertRuleTest.php`) and feature tests (`tests/Feature/Alerts/`) covering evaluation, cooldowns, dispatch, CRUD authorization, scheduler trigger, encryption, and delivery acknowledge/snooze (**131 total passing tests**, 393 assertions).

#### Phase 9 — Team Members & Role Authorization (v0.3)
- **Database & Relationships**: Created `project_members` pivot table migration (`2026_08_02_000005_create_project_members_table.php`). Added `members(): BelongsToMany` on `Project` and `memberProjects(): BelongsToMany` on `User` models with `role` attributes.
- **Actions**: Built `AddProjectMember` (`app/Actions/Teams/AddProjectMember.php`) and `RemoveProjectMember` (`app/Actions/Teams/RemoveProjectMember.php`) actions.
- **Controllers & Views**: Built Web (`app/Http/Controllers/Web/ProjectMemberController.php`) and REST API V1 (`app/Http/Controllers/Api/V1/ProjectMemberController.php`) controllers. Built team management Blade view (`resources/views/projects/members.blade.php`).
- **Testing**: Added Pest unit tests (`tests/Unit/ProjectMemberTest.php`) and feature tests (`tests/Feature/Teams/TeamManagementTest.php`) (**75 total passing tests**, 244 assertions).

---

## [v0.4.0] — Milestone v0.4 Integrations

### Added

#### Phase 17 — oblok Agent (v0.4)
- **Standalone log/metric shipper**: `oblok-agent` — a dependency-free PHP CLI that tails log files (JSON, Laravel text, plain) and nginx access logs, collects host/container resource stats, and pushes to oblok's REST APIs. No application code changes required.
- **Deployment & Docs**: `Dockerfile`, `docker-compose.oblok-agent.yml`, and a systemd/supervisor walkthrough for bare-metal hosts. Auto-detects container vs host and tags every sample accordingly.

#### Phase 15 — Slack Integration (v0.4)
- **Messaging driver framework**: `MessagingIntegration` with a Slack driver; Discord/Telegram ready via the same interface. Supports notification channels and alert delivery.

#### Phase 13 — GitHub Integration (v0.4)
- Link a project to a repository (`owner/name`) with a GitHub token; commits and pull requests sync automatically every 15 minutes and surface in the project context.

#### Phase 12 — Scheduler Monitoring (v0.4)
- Track scheduled/cron tasks and their recent runs (pass/fail), with missed-run detection.

#### Phase 11 — Webhook Inspector & Replay (v0.4)
- Capture inbound webhooks with full payload and headers; inspect and replay them for debugging.

---

## [v0.3.0] — Milestone v0.3 Control Plane

### Added

#### Phase 20 — Resource Monitoring (v0.3)
- Host and container CPU, memory, disk, and network metrics via Prometheus-compatible scrape (node_exporter, cAdvisor, app `/metrics`) or the agent. Dashboard shows a `Host`/`Container` badge and hides the container card when no cgroup limit exists.

#### Phase 19 — Request Monitoring (v0.3)
- Per-endpoint request counts, status codes, and latency derived from access logs (via the agent) or injected middleware.

#### Phase 18 — Advanced Check Types (v0.3)
- Beyond plain HTTP: TCP, TLS/certificate-expiry, DNS, and HTTP-with-expectations health checks.

#### Phase 16 — Custom Metrics & Metric Dashboards (v0.3)
- Agentless push endpoint for counters/gauges/histograms and Prometheus-compatible scrape targets. Configurable charts with 1H/6H/24H/7D time ranges.

#### Phase 14 — API Key Management & Usage Tracking (v0.3)
- Issue, rotate, and revoke per-project Bearer API keys; request counts and rate limiting per key.

---

## [v0.2.0] - 2026-08-02 — Milestone v0.2 Queue & Deployment Operations Release

### Added

#### Phase 8 — Incident Management & Alerting (v0.2)
- **Database & Models**: Created `incidents` migration (`2026_08_02_000004_create_incidents_table.php`) and `Incident` Eloquent model with UUID keys, soft deletes, and `scopeOpen`/`scopeResolved` scopes.
- **Actions & Event Trigger**: Built `CreateIncident` and `ResolveIncident` actions. Created `TriggerIncidentOnServiceFailure` listener automatically opening `high` severity incidents upon service failure events.
- **Controllers & Views**: Built Web (`app/Http/Controllers/Web/IncidentController.php`) and API V1 (`app/Http/Controllers/Api/V1/IncidentController.php`) controllers. Designed incident timeline Blade views (`resources/views/incidents/`) with severity badges and resolution triggers.
- **Testing**: Added Pest unit tests (`tests/Unit/IncidentTest.php`) and feature tests (`tests/Feature/Incidents/`) verifying manual incident logging, resolution, and automated service failure triggers (**72 total passing tests**, 236 assertions).

#### Phase 7 — Log Aggregation & Inspection (v0.2)
- **Database & Models**: Created `logs` migration (`2026_08_02_000003_create_logs_table.php`) and `LogEntry` Eloquent model (`app/Models/LogEntry.php`) with UUID keys and level/search scopes.
- **Action & Ingestion**: Built `IngestLogEntry` action (`app/Actions/Logs/IngestLogEntry.php`) and `IngestLogRequest` validation for REST log ingestion (`POST /api/v1/projects/{project}/logs`).
- **Controllers & Stream UI**: Built Web (`app/Http/Controllers/Web/LogController.php`) and API V1 (`app/Http/Controllers/Api/V1/LogController.php`) controllers. Designed real-time log inspector view (`resources/views/logs/index.blade.php`) with severity color badges, search input, and level filters.
- **Sidebar & Alignment**: Resolved global layout alignment to 100% full-width container (`w-full px-4 sm:px-6 lg:px-8`) and added dynamic project route resolution for Services, Deployments, and Logs Stream in sidebar.
- **Testing**: Added Pest unit tests (`tests/Unit/LogEntryTest.php`) and feature tests (`tests/Feature/Logs/LogAggregationTest.php`).

#### Phase 6 — Queue Monitoring & Horizon Integration (v0.2)
- **Laravel Horizon**: Installed and configured `laravel/horizon` package (`config/horizon.php`). Registered `viewHorizon` gate in `app/Providers/HorizonServiceProvider.php`.
- **Metrics Action**: Created `GetQueueMetricsAction` (`app/Actions/Queues/GetQueueMetricsAction.php`) aggregating pending jobs, failed jobs, Redis master supervisor status, and recent exception logs.
- **Controllers & API**: Built Web (`app/Http/Controllers/Web/QueueController.php`) and REST API V1 (`app/Http/Controllers/Api/V1/QueueController.php`) controllers.
- **Blade & Sidebar**: Built queue monitoring dashboard (`resources/views/queues/index.blade.php`) and enabled "Queues & Workers" navigation item in dark sidebar layout.
- **Testing**: Added Pest feature tests (`tests/Feature/Queues/QueueMonitoringTest.php`) verifying queue metrics, Horizon gate authorization, and API payloads.

#### Phase 5 — Deployment Tracking & Webhook Processing (v0.2)
- **Database & Models**: Created `deployments` migration (`2026_08_02_000002_create_deployments_table.php`) and `Deployment` Eloquent model with UUID keys, soft deletes, and environment scopes.
- **Webhook Receiver**: Implemented `ProcessDeploymentWebhook` action (`app/Actions/Deployments/ProcessDeploymentWebhook.php`) parsing GitHub, Vercel, and Railway CI/CD payload metadata.
- **Controllers & Views**: Built `DeploymentWebhookController` (`POST /api/v1/webhooks/deployments/{project:slug}`), Web controller, and deployment history timeline Blade views (`resources/views/deployments/`).
- **Testing**: Added Pest unit and feature tests (`tests/Unit/DeploymentTest.php`, `tests/Feature/Deploys/*`).

#### Phase 10 — Notification Channels & Delivery (v0.2)
- **Channels**: Email, webhook, and Slack notification channels configured per project.
- **Delivery & History**: `NotificationDelivery` records every send with status; consumers built for each channel driver.

#### Phase 9 — Alert Rules & Threshold Alerts (v0.2)
- **Rules engine**: Threshold-based `AlertRule` tied to monitoring and queue metrics; triggers `AlertEvent` and dispatches through notification channels.
- **UI & API**: Web and API V1 controllers for managing rules and viewing alert events; reject noisy alerts via configurable thresholds.

---

## [v0.1.0] - 2026-08-02 — Milestone v0.1 Foundation Release

### Added

#### Phase 4 — Service Health Monitoring (v0.1)
- **Database & Models**: Created `services` and `health_check_results` migrations (`2026_08_02_000001_create_services_table.php`). Built `Service` and `HealthCheckResult` Eloquent models with UUID keys, soft deletes, and status scopes.
- **Monitoring Driver**: Implemented `HttpHealthChecker` (`app/Services/Monitoring/HttpHealthChecker.php`) measuring precise millisecond response duration and expected HTTP status codes.
- **Action & Events**: Built `PingServiceHealth` action (`app/Actions/Services/PingServiceHealth.php`) and `ServiceStatusChanged` event.
- **Queued Monitoring**: Added `CheckServiceHealthJob` and `DispatchScheduledHealthChecksJob` queued background jobs. Scheduled automated checks every minute in `routes/console.php`.
- **Controllers & API**: Built Web (`app/Http/Controllers/Web/ServiceController.php`) and REST API V1 (`app/Http/Controllers/Api/V1/ServiceController.php`) controllers with `StoreServiceRequest` and `UpdateServiceRequest` validation.
- **Charts & Views**: Integrated **ApexCharts** response latency graph visualization in `resources/views/services/show.blade.php`, with healthy/failing status badges and manual probe trigger buttons.
- **Testing**: Added Pest unit tests (`tests/Unit/ServiceTest.php`) and feature tests (`tests/Feature/Services/`) verifying HTTP probe checks, scheduled queue jobs, and CRUD authorization (**50 total passing tests**, 160 assertions).

#### Phase 3 — Dashboard Overview & UI Shell (v0.1)
- **UI Shell**: Built persistent collapsible left sidebar navigation component (`resources/views/layouts/sidebar.blade.php`) with dark mode default theme styling, group sections, user profile footer, and bracket key (`[`) toggle shortcut.
- **Dashboard Action**: Added `GetDashboardOverview` Action (`app/Actions/Dashboard/GetDashboardOverview.php`) to aggregate project counts, active status, uptime metrics, and recent activity timelines.
- **Web Controller**: Added `Web\DashboardController` (`app/Http/Controllers/Web/DashboardController.php`) to render the operational overview dashboard.
- **Dashboard Interface**: Redesigned `resources/views/dashboard.blade.php` with 4 top summary metric cards (Total Projects, Active Projects, System Uptime, Open Incidents), ApexChart visualization container placeholder, and recent active projects data table.
- **Testing**: Added Pest feature tests (`tests/Feature/DashboardTest.php`) verifying authenticated dashboard access, operational metrics data payload, and guest redirects.

#### Phase 2 — Project Management (v0.1)
- **Database & Models**: Created `projects` migration with UUID primary keys, user foreign keys, unique slugs, metadata JSONB column, and soft deletes (`app/Models/Project.php`).
- **Actions**: Added `CreateProject`, `UpdateProject`, and `ArchiveProject` use-case actions (`app/Actions/Projects/`).
- **Authorization**: Added `ProjectPolicy` to enforce strict per-user project ownership.
- **Controllers & Requests**: Built Web (`app/Http/Controllers/Web/ProjectController.php`) and API V1 (`app/Http/Controllers/Api/V1/ProjectController.php`) controllers with `StoreProjectRequest` and `UpdateProjectRequest` validation.
- **Blade Views**: Built project listing (`resources/views/projects/index.blade.php`), creation form, edit form, and project detail overview Blade templates.
- **Testing**: Added Pest unit tests (`tests/Unit/ProjectTest.php`) and feature tests (`tests/Feature/Projects/`) covering CRUD operations, authorization, search filtering, and API envelopes.

#### Phase 1 — Authentication (v0.1)
- **Session Authentication**: Installed Laravel Breeze Blade stack with Alpine.js and Tailwind CSS (`routes/auth.php`).
- **Demo Login**: Added demo user seeding (`admin@oblok.dev`) and one-click demo login button.
- **User Verification**: Implemented `MustVerifyEmail` contract on `User` model.
- **Testing**: Integrated 25 Pest auth feature tests (`tests/Feature/Auth/*`).

#### Phase 0 — Bootstrapping & Infrastructure
- **Framework**: Initialized Laravel 13 running on PHP 8.4 runtime.
- **Docker Topology**: Configured root `docker-compose.yml` for Nginx, PHP-FPM 8.4, Queue Worker, Task Scheduler, PostgreSQL 16, and Redis 7.
- **CI/CD**: Added GitHub Actions workflow (`.github/workflows/ci.yml`) automating Pint formatting, Larastan static analysis, and Pest testing.
- **Code Quality**: Configured Larastan (Level 5 static analysis) and Laravel Pint auto-formatting.
