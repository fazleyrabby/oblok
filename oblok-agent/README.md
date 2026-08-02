# oblok-agent

A dependency-free, stack-independent log shipper and request monitor for oblok.

Run it beside any project — it tails log files and nginx access logs and pushes them
to oblok's REST API with an API key. No changes to the monitored application are
required.

## What it does

- **Log shipping** — tails one or more log files and forwards each line as a log
  entry to `POST /api/v1/projects/{project}/logs`. Handles JSON lines
  (`{"message": "...", "level": "error", ...}`), Laravel text lines
  (`[2026-08-02 12:00:00] production.ERROR: message`), and plain lines.
- **Request metrics** — tails an nginx combined-format access log and aggregates
  per-minute `http_requests_total{method,status}` and
  `http_request_duration_seconds{method,status}` samples to
  `POST /api/v1/projects/{project}/metrics`.

## Configuration

| Env var | Required | Description |
|---|---|---|
| `OBLOK_URL` | yes | oblok base URL, e.g. `https://oblok.yourlab.lan` |
| `OBLOK_API_KEY` | yes | A per-project API key (issued in oblok → Management → API Keys) |
| `OBLOK_PROJECT_ID` | yes | The UUID of the oblok project |
| `OBLOK_LOG_FILES` | no | Comma-separated log files to tail |
| `OBLOK_ACCESS_LOG` | no | nginx access log path to parse |
| `OBLOK_AGENT_NAME` | no | Label shown in agent output |
| `OBLOK_POLL_INTERVAL` | no | Seconds between file polls (default 2) |
| `OBLOK_FLUSH_INTERVAL` | no | Seconds between metric flushes (default 10) |

## Run locally

```bash
OBLOK_URL=https://oblok.yourlab.lan \
OBLOK_API_KEY=atl_... \
OBLOK_PROJECT_ID=019f... \
OBLOK_LOG_FILES=/srv/app/storage/logs/laravel.log \
OBLOK_ACCESS_LOG=/var/log/nginx/access.log \
php oblok-agent/bin/oblok-agent
```

## Run as a Docker sidecar

Add this to an app's `docker-compose.yml` (or use `docker-compose.oblok-agent.yml`):

```yaml
  oblok-agent:
    build: ./oblok-agent
    restart: unless-stopped
    environment:
      OBLOK_URL: ${OBLOK_URL}
      OBLOK_API_KEY: ${OBLOK_API_KEY}
      OBLOK_PROJECT_ID: ${OBLOK_PROJECT_ID}
      OBLOK_LOG_FILES: /var/log/app/laravel.log
      OBLOK_ACCESS_LOG: /var/log/nginx/access.log
    volumes:
      - ./storage/logs:/var/log/app:ro
      - /var/log/nginx:/var/log/nginx:ro
```

Mount the directories you want to watch read-only; the agent never writes anything.

## Notes

- The agent starts at the end of each file, so it only forwards new lines.
- Log rotation is handled: if a file shrinks or disappears it re-opens from the top.
- Each log line is pushed individually; a batched logs endpoint is planned.
