# AGORA e-Hub

Digital knowledge repository for UPLB OVCRE. Browse guidelines, research, and training materials online or offline.

## Stack

- Laravel 13
- Filament 5 (admin)
- Vue 3 + Pinia + Vue Router (reader app and author portal)
- PostgreSQL 16 + Redis 7 (Docker)
- Laravel Sanctum (API auth)
- Laravel Boost (MCP + AI guidelines)

## Quick start (Docker)

```bash
cp .env.docker .env
docker compose up -d

docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

./build
```

**WSL note:** Do not run `npm run build` from WSL if `which npm` points to `/mnt/c/Program Files/nodejs` — Windows npm cannot use Linux project paths. Use `./build` (Docker) or `scripts\\build-frontend-windows.cmd` from Windows.

Optional dev server:

```bash
docker compose --profile dev up -d node
```

Open:

| URL | App |
|-----|-----|
| http://localhost:8000 | Reader |
| http://localhost:8000/author | Author portal |
| http://localhost:8000/admin | Filament admin |
| http://localhost:5173 | Vite dev (with `dev` profile) |

## Default accounts

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@agora-ehub.local | password |
| Contributor | author@agora-ehub.local | password |
| Reader | reader@agora-ehub.local | password |

## Docker services

| Service | Container | Port |
|---------|-----------|------|
| Nginx | AGORA_e-HUB-nginx | 8000 |
| PHP-FPM | AGORA_e-HUB-app | 9000 |
| PostgreSQL | AGORA_e-HUB-postgres | 5432 |
| Redis | AGORA_e-HUB-redis | 6379 |
| Node/Vite | AGORA_e-HUB-node | 5173 (dev profile) |
| Mailpit | AGORA_e-HUB-mailpit | 8025 (dev profile) |

## Makefile

```bash
make up       # Start containers
make down     # Stop containers
make shell    # Shell into app container
make migrate  # Run migrations
make fresh    # Fresh migrate + seed
```

## Project layout

```
app/                    Models, API controllers, Filament resources
database/migrations/    Schema
resources/js/           Reader SPA (home, library, discover, profile)
resources/js/web/       Author portal
docker/                 PHP and Nginx config
public/brand/           AGORA and UP brand assets
```

## API (v1)

Public:

- `GET /api/v1/resources`
- `GET /api/v1/resources/{slug}`

Authenticated (Sanctum):

- `POST /api/v1/auth/login`
- `GET /api/v1/my/resources`
- `GET /api/v1/lookups/categories`
- `GET /api/v1/lookups/resource-types`

## NativePHP (mobile)

See [docs/NATIVEPHP.md](docs/NATIVEPHP.md). Android setup from Windows CMD:

```cmd
scripts\native-install-windows.bat
php artisan native:run android
```

## License

MIT
