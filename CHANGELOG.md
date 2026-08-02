# Changelog

All notable changes to Project Atlas will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

#### Phase 2 — Project Management (v0.1)
- **Database & Models**: Created `projects` migration with UUID primary keys, user foreign keys, unique slugs, metadata JSONB column, and soft deletes (`app/Models/Project.php`).
- **Actions**: Added `CreateProject`, `UpdateProject`, and `ArchiveProject` use-case actions (`app/Actions/Projects/`).
- **Authorization**: Added `ProjectPolicy` to enforce strict per-user project ownership.
- **Controllers & Requests**: Built Web (`app/Http/Controllers/Web/ProjectController.php`) and API V1 (`app/Http/Controllers/Api/V1/ProjectController.php`) controllers with `StoreProjectRequest` and `UpdateProjectRequest` validation.
- **Blade Views**: Built project listing (`resources/views/projects/index.blade.php`), creation form, edit form, and project detail overview Blade templates.
- **Testing**: Added Pest unit tests (`tests/Unit/ProjectTest.php`) and feature tests (`tests/Feature/Projects/`) covering CRUD operations, authorization, search filtering, and API envelopes (130 passing assertions).

#### Phase 1 — Authentication (v0.1)
- **Session Authentication**: Installed Laravel Breeze Blade stack with Alpine.js and Tailwind CSS (`routes/auth.php`).
- **User Verification**: Implemented `MustVerifyEmail` contract on `User` model.
- **Testing**: Integrated 25 Pest auth feature tests (`tests/Feature/Auth/*`).

#### Phase 0 — Bootstrapping & Infrastructure
- **Framework**: Initialized Laravel 13 running on PHP 8.4 runtime.
- **Docker Topology**: Configured root `docker-compose.yml` for Nginx, PHP-FPM 8.4, Queue Worker, Task Scheduler, PostgreSQL 16, and Redis 7.
- **CI/CD**: Added GitHub Actions workflow (`.github/workflows/ci.yml`) automating Pint formatting, Larastan static analysis, and Pest testing.
- **Code Quality**: Configured Larastan (Level 5 static analysis) and Laravel Pint auto-formatting.
