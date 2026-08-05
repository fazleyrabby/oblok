# Deploying oblok

This guide covers getting your own instance of oblok running. It assumes a Linux
host with Docker and Docker Compose installed, and a domain or IP you can reach.

oblok is a standard Laravel application. The repository ships a `docker-compose.yml`
that runs everything in containers:

| Container | Role |
|-----------|------|
| `nginx` | Web server (port `8080` by default) |
| `app` | PHP-FPM application |
| `worker` | Queue worker — runs **Horizon** (`php artisan horizon`) |
| `scheduler` | Runs Laravel's scheduled tasks (`php artisan schedule:work`) |
| `reverb` | WebSocket server for real-time updates |
| `postgres` | PostgreSQL 16 database |
| `redis` | Redis (cache, queues, Horizon) |

---

## 1. Prerequisites

- A host with Docker Engine and Docker Compose v2.
- Git, to clone the repository.
- (Optional) A reverse proxy such as Caddy or Traefik if you want HTTPS / a domain.

---

## 2. Clone and configure

```bash
git clone https://github.com/fazleyrabby/oblok.git
cd oblok
cp .env.example .env
```

Edit `.env` and set at minimum:

```dotenv
APP_NAME=oblok
APP_ENV=production
APP_URL=https://oblok.example.com
APP_KEY=            # generate with: php artisan key:generate

DB_DATABASE=oblok
DB_USERNAME=oblok
DB_PASSWORD=change-me

# Redis (used by queues, cache, and Horizon)
REDIS_HOST=redis
REDIS_PASSWORD=null

# Reverb (WebSocket) — generate your own app id/key/secret
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=oblok.example.com
REVERB_PORT=443
REVERB_SCHEME=https

QUEUE_CONNECTION=redis
```

> The values above match the service names in `docker-compose.yml` (`postgres`,
> `redis`). Keep `DB_HOST=postgres` and `REDIS_HOST=redis` as-is unless you run
> those services outside of Compose.

### AI Assistant configuration (optional)

The AI Assistant is enabled out of the box and answers operational questions about a
project using its live context (services, incidents, deployments, alerts, logs). It
speaks to any OpenAI-compatible `/chat/completions` endpoint.

```dotenv
# AI Assistant — default is Groq's free tier (no cost)
OBLOK_AI_PROVIDER=openai-compatible
OBLOK_AI_ENDPOINT=https://api.groq.com/openai/v1
OBLOK_AI_API_KEY=your-groq-api-key          # get one free at https://console.groq.com
OBLOK_AI_MODEL=openai/gpt-oss-120b          # free alternatives: llama-3.3-70b-versatile, llama-3.1-8b-instant
OBLOK_AI_TIMEOUT=60                          # seconds before the provider call times out
OBLOK_AI_CONTEXT_LIMIT=12                    # entries per category included in the context snapshot
```

Pointing at another provider (OpenAI, OpenRouter, Ollama, LM Studio, vLLM) is just a
matter of changing `OBLOK_AI_ENDPOINT`, `OBLOK_AI_API_KEY`, and `OBLOK_AI_MODEL`.
Leave `OBLOK_AI_API_KEY` empty for local providers that need no auth. For a local
llama.cpp server: `OBLOK_AI_ENDPOINT=http://<host>:8080/v1` with `--api-key` empty.

Chat replies stream token-by-token over Server-Sent Events, and every exchange is
saved to per-project chat history (clearable from the chat panel).

### Anomaly detection configuration (optional)

Anomaly detection compares the most recent portion of each metric series against
its own earlier baseline and flags series that deviate significantly. It is
enabled by default and needs no configuration to work.

```dotenv
# Anomaly detection — sensible defaults shown
OBLOK_ANOMALY_Z_THRESHOLD=3          # z-scores beyond this flag a series
OBLOK_ANOMALY_MIN_SAMPLES=12         # minimum samples per series to evaluate
OBLOK_ANOMALY_WINDOW_HOURS=24        # lookback window for analysis
```

---

## 3. Build and start

```bash
docker compose build
docker compose up -d
```

Then run the first-time setup inside the `app` container:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
```

Open the app in your browser and click **One-Click Demo Login** (enabled in the
default seed) to get in, or create the first user via the registration flow.

---

## 4. The worker runs Horizon

The `worker` container already runs `php artisan horizon` (not `queue:work`).
Horizon's supervisor settings — queues, `tries`, and `timeout` — live in
`config/horizon.php`. To apply changes there, restart the worker:

```bash
docker compose up -d --force-recreate worker
```

Verify it is healthy: open **Queue** in the UI (or `/horizon`) — the status
should read **Active** with the expected number of processes.

---

## 5. Updating

```bash
git pull
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
```

---

## 6. Backups

oblok's state lives in two places: the **PostgreSQL database** and the
**compose / environment configuration**. Back both up regularly.

**Database** — dump from the running container:

```bash
docker compose exec -T postgres \
  pg_dump -U "$DB_USERNAME" "$DB_DATABASE" | gzip > oblok-pg-$(date +%F).sql.gz
```

**Configuration** — copy your `docker-compose.yml` and `.env` (or the whole
repo checkout) to safe storage.

Restore the database with:

```bash
gunzip -c oblok-pg-YYYY-MM-DD.sql.gz | docker compose exec -T postgres \
  psql -U "$DB_USERNAME" "$DB_DATABASE"
```

Automate the above with a scheduled job (cron, systemd timer, or your platform's
task scheduler) and prune old dumps after a retention window that fits your needs.
