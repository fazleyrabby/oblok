# Changelog

All notable changes to Project Atlas will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

#### Phase 3 — Dashboard Overview & UI Shell (v0.1)
- **UI Shell**: Built persistent collapsible left sidebar navigation component (`resources/views/layouts/sidebar.blade.php`) with dark mode default theme styling, group sections, user profile footer, and bracket key (`[`) toggle shortcut.
- **Dashboard Action**: Added `GetDashboardOverview` Action (`app/Actions/Dashboard/GetDashboardOverview.php`) to aggregate project counts, active status, uptime metrics, and recent activity timelines.
- **Web Controller**: Added `Web\DashboardController` (`app/Http/Controllers/Web/DashboardController.php`) to render the operational overview dashboard.
- **Dashboard Interface**: Redesigned `resources/views/dashboard.blade.php` with 4 top summary metric cards (Total Projects, Active Projects, System Uptime, Open Incidents), ApexChart visualization container placeholder, and recent active projects data table.
- **Testing**: Added Pest feature tests (`tests/Feature/DashboardTest.php`) verifying authenticated dashboard access, operational metrics data payload, and guest redirects (41 total passing tests, 139 assertions).

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
