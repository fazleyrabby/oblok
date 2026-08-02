# Connect Your Project to oblok

A step-by-step guide for wiring any application — Laravel, Node, Python, Go, or a
plain static site — into oblok. Everything here is stack-independent and requires
**no changes to your application code**.

The two integration styles:

- **Agentless (push)** — your CI/CD or app posts to oblok's REST API using an API key.
- **Agent sidecar (collect)** — the `oblok-agent` container tails log files and nginx
  access logs next to your app and ships them automatically.

---

## 1. Prerequisites

1. oblok is deployed and reachable (see the Docker Compose deploy in `docker-compose.yml`).
2. You are logged in and created a **project** for your application.
3. You issued an **API key** for that project: open the project → **Management → API Keys**
   → **Generate API Key**, and copy the token. It is shown only once.
4. Note the **project UUID** from the browser address bar:
   `http://your-oblok/projects/019f…-xxxx`.

---

## 2. Health checks (uptime)

oblok pings an HTTP endpoint on a schedule. Register the URL in the UI
(**Management → Services → Add Service**), or via the API:

```bash
curl -X POST "$OBLOK_URL/api/v1/projects/$PROJECT_ID/services" \
  -H "Authorization: Bearer $OBLOK_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "app-health",
    "type": "http",
    "target": "https://app.example.com/up",
    "check_interval": 60,
    "expected_status_code": 200
  }'
```

> **Laravel tip:** Laravel ships a `/up` route — use `https://app.example.com/up`.
> For any other stack, expose any endpoint that returns your expected status code.

---

## 3. Metrics

### 3a. Prometheus-compatible scrape target

Point oblok at any endpoint exposing Prometheus text format — `node_exporter`
(host CPU/memory/disk, `:9100/metrics`), `cAdvisor` (container metrics,
`:8080/metrics`), or your app's own `/metrics`. oblok scrapes it every minute;
no Prometheus server needed.

UI: **Management → Metrics → Add Target**.

```bash
curl -X POST "$OBLOK_URL/api/v1/projects/$PROJECT_ID/metrics/targets" \
  -H "Authorization: Bearer $OBLOK_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "node-exporter",
    "url": "http://10.0.0.5:9100/metrics"
  }'
```

### 3b. Push custom metrics

Any service can push counters/gauges with labels:

```bash
curl -X POST "$OBLOK_URL/api/v1/projects/$PROJECT_ID/metrics" \
  -H "Authorization: Bearer $OBLOK_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"metrics":[
    {"name":"http_requests_total","value":42,"labels":{"env":"prod","status":"200"}}
  ]}'
```

Charts appear under **Management → Metrics** with 1H/6H/24H/7D ranges.

---

## 4. Logs and request metrics (oblok-agent sidecar)

The agent is a dependency-free PHP CLI container that tails files and posts to oblok.
It reads **log files** (JSON, Laravel text, or plain lines) and **nginx access logs**
(combined format) for request metrics.

Add it as a service in your application's `docker-compose.yml`:

```yaml
services:
  oblok-agent:
    build: ./oblok-agent
    restart: unless-stopped
    environment:
      ATLAS_URL: http://your-oblok:8081
      ATLAS_API_KEY: ${ATLAS_API_KEY}
      ATLAS_PROJECT_ID: ${ATLAS_PROJECT_ID}
      ATLAS_AGENT_NAME: my-app-agent
      ATLAS_LOG_FILES: /var/log/app/laravel.log
      ATLAS_ACCESS_LOG: /var/log/nginx/access.log
    volumes:
      - ./storage/logs:/var/log/app:ro
      - /var/log/nginx:/var/log/nginx:ro
```

What happens automatically:

- Every new line in `laravel.log` → a log entry in oblok (**Logs Stream**).
- nginx access log lines → `http_requests_total{method,status}` and
  `http_request_duration_seconds{method,status}` samples → the **Metrics** dashboard.

The agent starts at the end of each file, survives log rotation, and only reads new lines.
Run it locally without Docker:

```bash
ATLAS_URL=... ATLAS_API_KEY=... ATLAS_PROJECT_ID=... \
ATLAS_LOG_FILES=/srv/app/storage/logs/laravel.log \
ATLAS_ACCESS_LOG=/var/log/nginx/access.log \
php oblok-agent/bin/oblok-agent
```

---

## 5. Deployments (webhook)

oblok exposes a **public** webhook (no auth) so any CI/CD can record a deployment by
project **slug**:

```bash
curl -X POST "$OBLOK_URL/api/v1/webhooks/deployments/YOUR-PROJECT-SLUG" \
  -H "Content-Type: application/json" \
  -d '{
    "environment": "production",
    "commit_hash": "a1b2c3d",
    "commit_message": "ship it",
    "author": "ci-bot",
    "status": "successful"
  }'
```

Works with GitHub Actions, GitLab CI, Jenkins, or a plain `curl` after `docker compose up`.

---

## 6. Repository context (GitHub integration)

Under **Management → GitHub**, link the project to a repository to surface recent
commits and pull requests. Enter `owner/name` and a GitHub token with `repo` scope.
oblok syncs commit/PR context automatically every 15 minutes.

---

## 7. Verify

- **Services** — status badge flips healthy/unhealthy; latency chart fills in.
- **Metrics** — new charts appear under **Management → Metrics** after the first
  scrape or push.
- **Logs** — entries stream into the project's **Logs Stream** as the agent tails.
- **Deployments** — webhook posts appear in the deployment timeline (and in the
  Webhook Inspector under **Management → Webhooks**).

---

## Environment reference

| Variable | Purpose |
|----------|---------|
| `OBLOK_URL` | oblok base URL (e.g. `http://your-oblok:8081`) |
| `OBLOK_API_KEY` | project API key (Management → API Keys) |
| `PROJECT_ID` | project UUID from the address bar |
| `YOUR-PROJECT-SLUG` | project slug used by the public deployment webhook |

Agent variables are documented in [`oblok-agent/README.md`](../oblok-agent/README.md).
