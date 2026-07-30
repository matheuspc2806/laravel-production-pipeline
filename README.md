# Laravel Production Pipeline

A reference pipeline for shipping Laravel to production the way it should be shipped: **build the artifact once, prove it works, promote that exact artifact.**

Most Laravel projects I get called into deploy by rsync, FTP, or a `git pull` on the server. There's a test suite in the repo that CI never runs. Nobody is quite sure how to roll back. It works right up until the afternoon it doesn't.

This repository is the alternative, in full, working, and readable in about ten minutes.

---

## The four rules this pipeline enforces

**1. The artifact is built once and never rebuilt.**
The image is tagged with the commit SHA. Staging validates `:abc123`. Production runs `:abc123` — the same bytes, not a rebuild of the same branch. A rebuild is a different artifact, and a different artifact is an untested artifact.

**2. A green pipeline is not the same as a working application.**
Every deploy ends with a health gate that polls the real application over HTTP and refuses to pass unless it answers `200` *and* reports the release it was supposed to receive. This is what kills the "pipeline green, API returning 502" scenario. See [`scripts/health-gate.sh`](scripts/health-gate.sh).

**3. Production is a manual decision.**
Staging deploys itself on every merge to `main`. Production waits for a human to press the button — implemented with a GitHub Environment protection rule. Automation should remove the toil, not the judgement.

**4. The running application can always prove what it is.**
`GET /health` returns the release SHA baked in at build time, plus a live check of every dependency it needs. You never have to guess what's actually deployed.

```json
{
  "status": "ok",
  "release": "9f3c1d2",
  "checks": {
    "database": { "ok": true, "ms": 1.84 },
    "cache": { "ok": true, "ms": 0.42 }
  }
}
```

If a dependency is down this returns `503` and the deploy gate fails. That is the intended behaviour.

---

## Pipeline shape

```
push to main
    │
    ├── test ──────────── Pint (style) → PHPStan level 6 → PHPUnit
    │                     Fails here, nothing gets built.
    │
    ├── build ─────────── multi-stage image → ghcr.io/<repo>:<sha>
    │                     APP_RELEASE baked in at build time.
    │
    ├── staging ───────── run :<sha> → health gate (must report <sha>)
    │                     Automatic. Fails loudly.
    │
    └── production ────── manual approval → promote the same :<sha>
                          No rebuild. Health gate again, roll back on failure.
```

Defined in [`.github/workflows/ci.yml`](.github/workflows/ci.yml).

---

## Running it locally

```bash
cp .env.example .env
docker compose up --build
```

Then:

```bash
curl -s localhost:8080/health | jq
```

MySQL and Redis come up with health checks, and the app container waits for them rather than crash-looping while they boot.

## Running the checks

```bash
composer install
php artisan test
vendor/bin/phpstan analyse --memory-limit=512M
vendor/bin/pint --test
```

These are the same three commands CI runs. If they pass locally they pass in CI — there is no separate CI-only configuration to drift out of sync.

---

## Notes on the choices

**FrankenPHP over php-fpm + nginx.** One process, one container, no fpm socket tuning. For a small-to-medium Laravel app this removes an entire class of configuration and an entire container.

**Multi-stage build.** Composer dependencies and Vite assets are built in throwaway stages. The runtime image carries no Composer, no Node, no dev dependencies, and no build cache.

**`opcache.validate_timestamps = 0`.** The code in the image never changes at runtime, so there is no reason to stat every file on every request. This is safe precisely *because* the artifact is immutable — the deployment model is what makes the optimisation available.

**The health check tests dependencies, not the framework.** Laravel's built-in `/up` tells you the framework booted. That is not the same as "this application can serve a request", which needs the database and the cache to actually answer. A gate is only worth something if it checks the things that actually break.

**Migrations behind `RUN_MIGRATIONS` and `--isolated`.** Opt-in per environment, and only one container in a multi-replica deploy will run them.

---

## Adapting it to a real deployment

The `production` job in this repository prints what it would run instead of running it, because there is no server attached to a public demo. The `staging` job is real: it genuinely starts the built image and genuinely gates on its health endpoint, and it will genuinely fail if the image is broken.

In a real engagement the deploy step becomes whatever the target is — `docker compose pull && up -d` over SSH, a swarm service update, a platform API call — and the health gate stays exactly as it is, pointed at the real hostname. The gate is the part that matters, and it is deliberately transport-agnostic.

---

## Who wrote this

I'm a DevOps/platform engineer. I've run this migration across roughly ten production Laravel repositories — moving teams off manual deploys onto immutable image promotion with real deploy gates, incrementally, without a big-bang rewrite and without a maintenance window.

I do this as a fixed-scope engagement for teams who know their deploy is fragile and don't want to hire a full-time DevOps engineer to fix it. Existing client work is under NDA, which is why this repository exists — it's the same pipeline, extracted and made public.

If your deploy story sounds like the first paragraph of this README, open an issue or reach out. I'll write you a short assessment of what's fragile in your current setup, free, whether or not we end up working together.

## License

MIT.
