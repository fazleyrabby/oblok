# Vision

> This document explains **why** oblok exists.
>
> It defines the mission, philosophy, principles, and long-term direction of the project.
>
> For what oblok builds, see [spec.md](spec.md). For how it is built, see [architecture.md](architecture.md).

---

## Mission

Provide engineering teams with a single self-hosted platform for operating backend applications.

oblok replaces the need to stitch together separate tools for monitoring, deployments, logs, queues, webhooks, incidents, and notifications. One interface. One deployment. Full ownership of data.

---

## Vision

oblok should become the operational backbone for small-to-medium engineering teams that prefer self-hosted infrastructure.

A team should be able to deploy oblok alongside their application stack and immediately gain visibility into service health, deployment status, queue throughput, error rates, and operational events — without depending on third-party SaaS platforms.

---

## Philosophy

### Build for the long term

Every decision should optimize for maintainability over speed of delivery. oblok is not a prototype. It is designed to be actively maintained for years.

### Solve real problems

oblok exists to reduce operational overhead for engineers. Every feature should directly reduce time spent context-switching between tools, diagnosing issues, or performing routine operational tasks.

### Earn trust through transparency

As a self-hosted platform, oblok must be fully auditable. No telemetry. No vendor lock-in. No hidden network calls. Engineers should trust the software running inside their infrastructure.

### Keep it simple

Prefer fewer features done well over many features done poorly. Complexity is the primary threat to long-term maintainability.

---

## Core Principles

**Ownership**
Engineers own their data and infrastructure. oblok never phones home.

**Modularity**
Features are organized as independent modules. Disabling a module should not break the platform.

**API-first**
Every UI feature exposes an API. oblok is programmable before it is visual.

**Observability by default**
oblok should be as observable as the applications it monitors. Structured logging, health endpoints, and metrics are built in from day one.

**Progressive complexity**
The default experience should be simple. Advanced configuration should be available but never required.

---

## Target Audience

**Primary**

- Small-to-medium backend engineering teams (2–20 engineers).
- Teams running Laravel, Rails, Django, Node.js, or Go backends.
- Teams that prefer self-hosted solutions over SaaS.
- Solo developers managing multiple production applications.

**Secondary**

- DevOps engineers responsible for operational tooling.
- Engineering managers who need visibility into system health.
- Open-source contributors interested in production-grade Laravel architecture.

---

## Problems Being Solved

| Problem | How oblok Addresses It |
|---------|----------------------|
| Operational data is scattered across multiple tools | Unified dashboard for services, deployments, logs, queues, and incidents |
| SaaS monitoring tools are expensive at scale | Self-hosted with zero licensing cost |
| Context-switching between tools slows down incident response | Single interface for all operational concerns |
| Queue and job visibility is often an afterthought | First-class queue monitoring with job inspection |
| Webhook debugging requires custom tooling | Built-in webhook inspector with payload logging |
| No affordable self-hosted alternative to Datadog/New Relic for small teams | oblok fills this gap with a focused feature set |
| Deployment tracking is disconnected from monitoring | Deployments and monitoring live in the same platform |

---

## Long-Term Goals

1. Become the default self-hosted DevOps platform for Laravel teams.
2. Expand language and framework support beyond Laravel.
3. Build a plugin ecosystem that allows teams to extend oblok.
4. Provide AI-assisted operational workflows (anomaly detection, root cause analysis, automated runbooks).
5. Achieve feature parity with basic SaaS monitoring tools for the common case.
6. Maintain a codebase that serves as a reference implementation for production Laravel architecture.

---

## Non-Goals

oblok intentionally does **not** aim to:

- **Replace APM tools.** oblok provides operational visibility, not code-level performance profiling. It will not instrument application code.
- **Become a full CI/CD platform.** oblok tracks deployments but does not build or ship code. It integrates with existing CI/CD pipelines.
- **Support multi-tenancy in v1.** oblok is designed for a single team per installation. Multi-tenancy is a future consideration, not an MVP requirement.
- **Compete with enterprise observability platforms.** Datadog, New Relic, and Grafana Cloud serve different needs at different scales. oblok targets teams that want something simpler and self-hosted.
- **Provide a managed hosting service.** oblok is self-hosted software. There are no plans for a hosted SaaS offering in the near term.

---

## Success Definition

oblok is successful when:

- A team can deploy oblok in under 15 minutes using Docker Compose.
- An engineer can diagnose a production incident without leaving oblok.
- The codebase is used as a reference for Laravel architecture in technical interviews, blog posts, and conference talks.
- The project receives sustained open-source contributions from outside the core team.
- Teams choose oblok over free tiers of SaaS alternatives because it is good enough and fully under their control.
