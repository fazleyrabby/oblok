# Development Guide

> Engineering handbook for Project oblok.
>
> This document defines how engineers (human and AI) work on the codebase.
>
> For architectural decisions, see [architecture.md](architecture.md). For AI-specific rules, see [AGENTS.md](../AGENTS.md). For technology rationale, see [tech-stack.md](tech-stack.md).

---

## Coding Standards

oblok follows PSR-12 and standard Laravel conventions enforced automatically by tooling.

| Tool | Purpose | Command |
|------|---------|---------|
| Laravel Pint | Code formatting | `./vendor/bin/pint --test` |
| PHPStan (Larastan) | Static analysis | `./vendor/bin/phpstan analyse` |
| Pest | Testing | `./vendor/bin/pest` |

---

## Naming Conventions

Names describe intent cleanly and follow standard Laravel patterns.

### Classes

| Type | Pattern | Example |
|------|---------|---------|
| Action | `{Verb}{Noun}` | `CreateProject`, `ExecuteHealthCheck` |
| Controller | `{Noun}Controller` | `ProjectController` |
| Model | `{Noun}` (Singular) | `Project`, `Service` |
| Form Request | `{Verb}{Noun}Request` | `StoreProjectRequest` |
| Resource | `{Noun}Resource` | `ProjectResource` |
| Policy | `{Noun}Policy` | `ProjectPolicy` |
| Enum | `{Noun}` | `ServiceStatus` |
| Event | `{Noun}{PastTense}` | `ServiceStatusChanged` |
| Listener | `{Verb}{Noun}` | `RecordStatusChangeInHistory` |
| Job | `{Verb}{Noun}Job` | `ProcessHealthCheckPingJob` |
| Notification | `{Noun}Alert` | `ServiceDownAlert` |

---

## Folder Structure

oblok uses standard Laravel root folders with feature sub-directories:

- `app/Actions/{Feature}/`
- `app/Events/{Feature}/`
- `app/Http/Controllers/{Api|Web}/`
- `app/Http/Requests/`
- `app/Http/Resources/`
- `app/Jobs/{Feature}/`
- `app/Listeners/{Feature}/`
- `app/Models/`
- `app/Policies/`
- `app/Services/`

---

## Git & Commit Workflow

Conventional Commits are required:

```
feat: add service health check execution
fix: resolve queue timeout on ping
docs: update architecture diagram
test: add feature tests for project creation
```

---

## Testing Strategy

All features ship with Pest feature & unit tests:

```bash
# Run test suite
./vendor/bin/pest
```

---

## Definition of Done

A task is complete when:
1. The feature works as specified.
2. Pest tests pass.
3. PHPStan reports zero errors.
4. Pint formatting passes.
5. Relevant documentation is updated.
