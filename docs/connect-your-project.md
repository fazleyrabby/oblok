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
      OBLOK_URL: http://your-oblok:8081
      OBLOK_API_KEY: ${OBLOK_API_KEY}
      OBLOK_PROJECT_ID: ${OBLOK_PROJECT_ID}
      OBLOK_AGENT_NAME: my-app-agent
      OBLOK_LOG_FILES: /var/log/app/laravel-*.log
      OBLOK_ACCESS_LOG: /var/log/nginx/access.log
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
OBLOK_URL=... OBLOK_API_KEY=... OBLOK_PROJECT_ID=... \
OBLOK_LOG_FILES=/srv/app/storage/logs/laravel-*.log \
OBLOK_ACCESS_LOG=/var/log/nginx/access.log \
php oblok-agent/bin/oblok-agent
```

---

## 4b. Dockerized Laravel project — complete walkthrough

Everything above, applied to a real Dockerized Laravel app. This is the exact flow used
to wire the projects in the reference homelab.

**1. Create the project and API key** (in oblok: open the project → **Management →
API Keys** → Generate). Note the project UUID from the address bar and the key.

**2. Make Laravel write logs to files.** The agent tails files, so the app must use a
file channel. Set in the app's `.env`:

```dotenv
LOG_CHANNEL=daily
```

If the app uses a config cache, clear it or the change won't apply:

```bash
php artisan config:clear
```

**3. Add an nginx access-log volume** so request metrics can be read. In the app's
`docker-compose.yml`, mount the nginx log directory on the `app` service:

```yaml
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
      - ./nginx-logs:/var/log/nginx
```

**4. Add the `oblok-agent` sidecar** to the same compose file:

```yaml
  oblok-agent:
    build: /path/to/oblok/oblok-agent
    restart: unless-stopped
    env_file:
      - .env
    volumes:
      - ./storage/logs:/var/log/app:ro
      - ./nginx-logs:/var/log/nginx:ro
```

**5. Add the agent credentials** to the app's `.env`:

```dotenv
# oblok observability agent
OBLOK_URL=http://your-oblok:8081
OBLOK_API_KEY=atl_...
OBLOK_PROJECT_ID=019f...-xxxx
OBLOK_AGENT_NAME=my-app-agent
OBLOK_LOG_FILES=/var/log/app/laravel-*.log
OBLOK_ACCESS_LOG=/var/log/nginx/access.log
```

> **No nginx?** (e.g. FrankenPHP, artisan serve, Node) — omit `OBLOK_ACCESS_LOG`.
> Logs still ship; push request counts via the metrics API instead (section 3b).

**6. Ensure log permissions.** If a root process created the log files, php-fpm may not
be able to write them (this silently breaks error logging). Fix ownership:

```bash
docker compose exec app sh -c "chown -R www-data:www-data storage/logs && chmod -R 775 storage/logs"
```

**7. Recreate and start:**

```bash
docker compose up -d --build oblok-agent
docker compose up -d --force-recreate app
```

**8. Verify:** hit the app (e.g. trigger an error or use the app normally), then in oblok
open the project → **Logs Stream** (entries appear within seconds) and **Metrics**
(`http_requests_total{method,status}`).

---

## 4c. Bare-metal / non-Dockerized project

oblok does not require your project to be containerized. The `oblok-agent` is a plain
PHP CLI process that reads local files, so it works identically on a bare-metal server,
VPS, or shared host. It auto-detects its environment (container vs host) and tags every
resource sample accordingly.

**What works without Docker:**

| Monitoring | How | Docker required? |
|------------|-----|------------------|
| Health checks | outbound ping from oblok | No |
| Logs | agent tails `OBLOK_LOG_FILES` | No |
| Request analytics | agent parses the web-server access log | No |
| CPU / RAM / Disk / cores | agent reads `/proc` directly on the host | No |
| Container RAM | only when a cgroup limit exists (containers) | Only for this card |

**1. Install the agent** — copy the `oblok-agent` directory to the server (it only needs
PHP CLI; no framework, no Composer install, no Docker):

```bash
scp -r ./oblok-agent user@server:/opt/oblok-agent
```

**2. Configure it** via a systemd-style env file (any of the `OBLOK_*` variables from
[`oblok-agent/README.md`](../oblok-agent/README.md)):

```bash
# /etc/oblok-agent/oblok-agent.env
OBLOK_URL=http://your-oblok:8081
OBLOK_API_KEY=atl_...
OBLOK_PROJECT_ID=019f...-xxxx
OBLOK_AGENT_NAME=my-app-agent
OBLOK_LOG_FILES=/var/www/app/storage/logs/laravel-*.log
OBLOK_ACCESS_LOG=/var/log/nginx/app-access.log
```

**3. Run it as a service (systemd):**

```ini
# /etc/systemd/system/oblok-agent.service
[Unit]
Description=oblok observability agent
After=network.target

[Service]
Type=simple
EnvironmentFile=/etc/oblok-agent/oblok-agent.env
ExecStart=/usr/bin/php /opt/oblok-agent/bin/oblok-agent
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now oblok-agent
journalctl -u oblok-agent -f   # watch it work
```

> **Supervisor instead of systemd?** The `ExecStart` line is the only thing that matters
> — point any supervisor at `php /opt/oblok-agent/bin/oblok-agent`.

**4. View in oblok** → **Resources**. On a bare-metal host the dashboard automatically
hides the **Container RAM** card (no cgroup limit exists) and shows a `Host` badge;
containerized agents get the full four-card layout with a `Container` badge.

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
