# SALIKSIK

**S**hared **A**rchive of **L**iterature, **I**nformation, **K**nowledge, **S**tudies, **I**nsights, and **K**nowledge

Offline-first digital knowledge repository for academic and research institutions. Not an e-commerce platform — no purchases or payments.

## Stack

- Laravel 13
- Filament 5 (admin panel)
- Vue 3 + Pinia + Vue Router (frontend SPA)
- MySQL 8.4 + Redis 7 (Docker)
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

- **Web app:** http://localhost:8080
- **Admin panel:** http://localhost:8080/admin
- **Vite dev:** http://localhost:5173 (with `dev` profile)

## Default Accounts

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@saliksik.local | password |
| Contributor | author@saliksik.local | password |
| Reader | reader@saliksik.local | password |

## Docker Services

| Service | Container | Port |
|---------|-----------|------|
| Nginx | saliksik-nginx | 8080 |
| PHP-FPM | saliksik-app | 9000 (internal) |
| MySQL | saliksik-mysql | 3306 |
| Redis | saliksik-redis | 6379 |
| Node/Vite | saliksik-node | 5173 (dev profile) |
| Mailpit | saliksik-mailpit | 8025 (dev profile) |

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
resources/js/         # Vue SPA (library, reader, discover)
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

- `GET /api/v1/resources` — list published resources
- `GET /api/v1/resources/{slug}` — resource detail

## Responsive UI

| Viewport | Experience |
|----------|------------|
| Phone (`< 768px`) | Bottom nav, slide-out library drawer, stacked pages |
| Tablet (`≥ 768px`) | Persistent sidebar, 4–5 column library grid |
| Large tablet (`≥ 1024px`) | 3-column resource detail, 3-pane reader (TOC + content + appearance) |

Preview tablet: open http://localhost:8080/library and set browser width to ~1024px.

## NativePHP (Mobile)

Package installed. Complete Android setup from **Windows CMD** (WSL not supported):

```cmd
scripts\native-install-windows.bat
php artisan native:run android
```

See [docs/NATIVEPHP.md](docs/NATIVEPHP.md) for full instructions.

## License

MIT
