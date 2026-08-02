# AGENTS.md

# Project oblok — AI Engineering Guide

> This document defines how AI coding agents should contribute to Project oblok.
>
> It is the primary source of truth for coding conventions, architectural constraints, development workflow, and engineering standards.
>
> Every AI agent **must read this document before making changes** to the codebase.

---

# Project Overview

Project oblok is a self-hosted Developer Operations Platform.

The goal is to provide engineering teams with a unified workspace for operating modern backend applications, including:

- Project Management
- Service Monitoring
- Deployments
- Queue Monitoring
- Log Aggregation
- Webhook Inspection
- Incident Management
- Notifications and Alerting
- API Management
- Scheduler Monitoring
- AI-assisted Operations
- Team Collaboration
- Analytics

oblok is intentionally designed as a long-term open-source project and portfolio centerpiece showcasing production-grade software engineering.

---

# Documentation Map

Before making changes, consult the correct document.

| Concern | Document |
|---------|----------|
| Why oblok exists | `docs/vision.md` |
| What to build (requirements) | `docs/spec.md` |
| How it is built (architecture) | `docs/architecture.md` |
| UX patterns and user flows | `docs/design.md` |
| Design system (tokens, components) | `DESIGN.md` |
| Technology choices and rationale | `docs/tech-stack.md` |
| Engineering process and standards | `docs/development.md` |
| Timeline and milestones | `docs/roadmap.md` |
| AI agent coding rules | `AGENTS.md` (this document) |

If a topic spans multiple documents, it is defined in one and referenced by others. See the documentation strategy in `docs/spec.md` for the full ownership map.

---

# Engineering Philosophy

oblok values:

- Simplicity over cleverness.
- Maintainability over shortcuts.
- Explicitness over magic.
- Composition over inheritance.
- Small focused classes.
- Clean architecture.
- SOLID principles.
- Testability.
- Documentation-first development.

Every feature should be understandable by another engineer six months later.

---

# Development Workflow

Every task should follow this order.

1. Read `docs/vision.md`
2. Read `docs/spec.md`
3. Read `docs/architecture.md`
4. Read relevant documentation
5. Plan implementation
6. Implement
7. Test
8. Update documentation

Never skip documentation updates.

---

# Documentation First

Documentation is part of the product.

Before implementing a major feature:

- Update the specification
- Update architecture if necessary
- Update design if UI changes
- Update roadmap if scope changes

Code and documentation should evolve together.

---

# Architecture Rules

oblok follows **Idiomatic Laravel with Actions**.

```
Presentation (Controllers, Requests, Resources, Views)
 ↓
Application (Actions, Jobs, Events, Notifications, Policies)
 ↓
Persistence & Domain (Eloquent Models, Enums, Scopes)
 ↓
Services (Third-Party Clients & Drivers)
```

Rules:

- Business logic belongs in Action classes (`app/Actions/{Feature}/`) or Services when coordinating multiple drivers.
- Never use Repositories wrapping Eloquent. Use Eloquent Models, Model Scopes, and Custom Query Builders directly.
- Avoid fat controllers. Controllers should orchestrate validation, action execution, and response rendering only.
- Do not create custom module loaders (`app/Modules/`). Use standard Laravel directory structures with clean sub-namespaces.
- Interfaces should only be created when multiple concrete implementations (drivers) exist.

---

# Folder Philosophy

Every folder follows standard Laravel conventions with sub-directories organized by feature area.

Prefer standard Laravel directory placement (`app/Actions`, `app/Models`, `app/Jobs`, `app/Events`, `app/Policies`).

---

# Laravel Conventions

Use:

- Form Requests for validation.
- Policies for authorization.
- Service classes for business logic.
- Actions for use cases.
- Events for decoupled communication.
- Queues for long-running work.
- Jobs should be idempotent.
- Notifications instead of custom mail logic.
- Resource classes for API responses.

Avoid:

- Static helpers for business logic.
- Global state.
- Massive controllers.
- Massive models.
- Hidden side effects.

---

# Coding Standards

Follow:

- PSR-12
- Laravel conventions
- SOLID
- DRY
- KISS
- YAGNI

Prefer readable code over clever code.

Code is read far more often than it is written.

---

# Naming

Names should describe intent.

Good

```
CreateProjectAction

DeployApplicationJob

HealthCheckService

IncidentRepository
```

Bad

```
Helper

Utils

Manager

ProcessData
```

Avoid abbreviations unless universally understood.

---

# Database Rules

- Use PostgreSQL.
- UUIDs where appropriate.
- Foreign keys required.
- Soft deletes only when necessary.
- Index frequently queried columns.
- Never optimize prematurely.

Every migration should be reversible.

---

# API Rules

oblok is API-first.

Every UI feature should eventually expose an API.

Rules:

- Version APIs.
- Consistent JSON responses.
- Proper HTTP status codes.
- Pagination by default.
- Validation errors should be predictable.

---

# Security

Always assume user input is untrusted.

Requirements:

- Validate everything.
- Escape output.
- Use Policies.
- Rate limit public endpoints.
- Never expose secrets.
- Never log credentials.
- Encrypt sensitive values.

Follow OWASP recommendations whenever possible.

---

# Performance

Optimize only after correctness.

General principles:

- Prevent N+1 queries.
- Use eager loading.
- Cache expensive operations.
- Queue slow tasks.
- Prefer indexes over caching when appropriate.

Measure before optimizing.

---

# Frontend Principles

Frontend should be:

- Fast
- Accessible
- Predictable
- Minimal

Use:

- Blade
- Alpine.js
- Tailwind CSS

Avoid unnecessary JavaScript frameworks.

---

# UI Philosophy

oblok is a professional developer tool.

Inspired by:

- Linear
- GitHub
- Raycast
- Vercel
- Railway

Avoid:

- Excessive animations
- Bright gradients
- Glassmorphism
- Decorative UI

Prioritize usability over visual effects.

---

# Testing

Every feature should include tests.

Use:

- Pest
- Feature Tests
- Unit Tests

Critical business logic should never ship without test coverage.

---

# Git Workflow

Keep commits focused.

Good examples:

```
feat: add service health monitoring

fix: prevent duplicate webhook processing

refactor: simplify deployment pipeline
```

Avoid unrelated changes in the same commit.

---

# Pull Requests

Each PR should:

- Solve one problem.
- Pass all tests.
- Update documentation.
- Follow coding standards.
- Include screenshots if UI changes.

---

# Definition of Done

A task is complete only if:

- Feature works.
- Tests pass.
- Documentation updated.
- Code reviewed.
- No obvious technical debt introduced.
- Naming follows conventions.
- No debug code remains.

---

# AI Agent Rules

Always:

- Prefer existing patterns.
- Reuse components.
- Keep changes minimal.
- Explain architectural decisions.
- Preserve backwards compatibility where practical.

Never:

- Rewrite unrelated code.
- Introduce unnecessary dependencies.
- Ignore failing tests.
- Break documented conventions.
- Duplicate existing functionality.

---

# Long-Term Vision

oblok is not a demo project.

It is intended to become:

- A production-quality open-source platform.
- A showcase of backend engineering.
- A reference implementation for Laravel architecture.
- A practical tool for developers and engineering teams.

Every contribution should move the project closer to that vision.