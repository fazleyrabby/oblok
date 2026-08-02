# Changelog

All notable changes to Project Atlas will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

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
- **Testing**: Added Pest unit and feature tests (`tests/Unit/DeploymentTest.php`, `tests/Feature/Deployments/*`).

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
- **Demo Login**: Added demo user seeding (`admin@atlas.dev`) and one-click demo login button.
- **User Verification**: Implemented `MustVerifyEmail` contract on `User` model.
- **Testing**: Integrated 25 Pest auth feature tests (`tests/Feature/Auth/*`).

#### Phase 0 — Bootstrapping & Infrastructure
- **Framework**: Initialized Laravel 13 running on PHP 8.4 runtime.
- **Docker Topology**: Configured root `docker-compose.yml` for Nginx, PHP-FPM 8.4, Queue Worker, Task Scheduler, PostgreSQL 16, and Redis 7.
- **CI/CD**: Added GitHub Actions workflow (`.github/workflows/ci.yml`) automating Pint formatting, Larastan static analysis, and Pest testing.
- **Code Quality**: Configured Larastan (Level 5 static analysis) and Laravel Pint auto-formatting.
