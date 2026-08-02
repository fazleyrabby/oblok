# atlas-agent

A dependency-free, stack-independent log shipper and request monitor for Atlas.

Run it beside any project — it tails log files and nginx access logs and pushes them
to Atlas's REST API with an API key. No changes to the monitored application are
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
| `ATLAS_URL` | yes | Atlas base URL, e.g. `https://atlas.yourlab.lan` |
| `ATLAS_API_KEY` | yes | A per-project API key (issued in Atlas → Management → API Keys) |
| `ATLAS_PROJECT_ID` | yes | The UUID of the Atlas project |
| `ATLAS_LOG_FILES` | no | Comma-separated log files to tail |
| `ATLAS_ACCESS_LOG` | no | nginx access log path to parse |
| `ATLAS_AGENT_NAME` | no | Label shown in agent output |
| `ATLAS_POLL_INTERVAL` | no | Seconds between file polls (default 2) |
| `ATLAS_FLUSH_INTERVAL` | no | Seconds between metric flushes (default 10) |

## Run locally

```bash
ATLAS_URL=https://atlas.yourlab.lan \
ATLAS_API_KEY=atl_... \
ATLAS_PROJECT_ID=019f... \
ATLAS_LOG_FILES=/srv/app/storage/logs/laravel.log \
ATLAS_ACCESS_LOG=/var/log/nginx/access.log \
php atlas-agent/bin/atlas-agent
```

## Run as a Docker sidecar

Add this to an app's `docker-compose.yml` (or use `docker-compose.atlas-agent.yml`):

```yaml
  atlas-agent:
    build: ./atlas-agent
    restart: unless-stopped
    environment:
      ATLAS_URL: ${ATLAS_URL}
      ATLAS_API_KEY: ${ATLAS_API_KEY}
      ATLAS_PROJECT_ID: ${ATLAS_PROJECT_ID}
      ATLAS_LOG_FILES: /var/log/app/laravel.log
      ATLAS_ACCESS_LOG: /var/log/nginx/access.log
    volumes:
      - ./storage/logs:/var/log/app:ro
      - /var/log/nginx:/var/log/nginx:ro
```

Mount the directories you want to watch read-only; the agent never writes anything.

## Notes

- The agent starts at the end of each file, so it only forwards new lines.
- Log rotation is handled: if a file shrinks or disappears it re-opens from the top.
- Each log line is pushed individually; a batched logs endpoint is planned.
