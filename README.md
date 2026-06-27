# AGORA e-Hub

Offline-first digital knowledge repository for UPLB OVCRE and academic institutions.

## Stack

- Laravel 13
- Filament 5 (admin panel)
- Vue 3 + Pinia + Vue Router (reader app + author web portal)
- PostgreSQL 16 + Redis 7 (Docker)
- Laravel Sanctum (API auth for author portal and future NativePHP sync)
- Laravel Boost (MCP + AI guidelines)

## Quick Start (Docker)

```bash
# Copy environment and start services
cp .env.docker .env
docker compose up -d

# Install PHP dependencies (first time)
docker compose exec app composer install

# Generate key, migrate, seed
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

# Install Laravel Boost MCP (optional, for Cursor AI)
docker compose exec app php artisan boost:install --guidelines --skills --mcp --no-interaction

# Frontend dev server (optional profile)
docker compose --profile dev up -d node
# Or build assets once:
npm install && npm run build
```

Open:

- **Reader app:** http://localhost:8080
- **Author portal:** http://localhost:8080/author
- **Admin panel:** http://localhost:8080/admin
- **Vite dev:** http://localhost:5173 (with `dev` profile)

## Default Accounts

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@agora-ehub.local | password |
| Contributor | author@agora-ehub.local | password |
| Reader | reader@agora-ehub.local | password |

## Docker Services

| Service | Container | Port |
|---------|-----------|------|
| Nginx | AGORA_e-HUB-nginx | 8080 |
| PHP-FPM | AGORA_e-HUB-app | 9000 (internal) |
| PostgreSQL | AGORA_e-HUB-postgres | 5432 |
| Redis | AGORA_e-HUB-redis | 6379 |
| Node/Vite | AGORA_e-HUB-node | 5173 (dev profile) |
| Mailpit | AGORA_e-HUB-mailpit | 8025 (dev profile) |

## Makefile Commands

```bash
make up          # Start containers
make down        # Stop containers
make shell       # Shell into app container
make migrate     # Run migrations
make fresh       # Fresh migrate + seed
make artisan migrate:status
make composer require some/package
```

## Project Structure

```
app/
  Enums/              # ResourceStatus, DownloadStatus, etc.
  Filament/Resources/ # Admin CRUD (Filament 5)
  Http/Controllers/Api/V1/
  Models/             # Eloquent models
database/migrations/  # Normalized schema from UPLB AGORA spec
resources/js/         # Vue reader SPA (library, discover, profile)
resources/js/web/     # Vue author portal (login, dashboard, resource CRUD)
docker/               # PHP, Nginx configs
```

## Database Design Notes

The schema extends the UPLB AGORA Excel structure with:

- **Spatie Permission** for RBAC (administrator, contributor, reader)
- **Normalized tags** (many-to-many) instead of per-resource keywords
- **Separate authors** entity with pivot roles
- **resource_metadata** for academic fields (ISBN, DOI, publisher, etc.)
- **User library tables**: downloads, reading_progress, bookmarks, highlights, notes, favorites
- **Analytics**: resource_views, activity_logs
- **Soft deletes** on core entities

## API (v1)

Public:

- `GET /api/v1/resources` — list published resources
- `GET /api/v1/resources/{slug}` — resource detail

Authenticated (Sanctum):

- `POST /api/v1/auth/login` — author/admin login
- `GET /api/v1/my/resources` — contributor resource CRUD
- `GET /api/v1/lookups/categories` — form lookups
- `GET /api/v1/lookups/resource-types` — form lookups

## Responsive UI

| Viewport | Experience |
|----------|------------|
| Phone | Bottom nav, slide-out library drawer |
| Tablet portrait | Full-width reader layout with drawer navigation |
| Tablet landscape | Persistent library sidebar + bottom nav |
| Author portal | Desktop web dashboard at `/author` |

## NativePHP (Mobile)

Package installed. Complete Android setup from **Windows CMD** (WSL not supported):

```cmd
scripts\native-install-windows.bat
php artisan native:run android
```

See [docs/NATIVEPHP.md](docs/NATIVEPHP.md) for full instructions.

## License

MIT
