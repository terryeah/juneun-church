# 브리즈번 주는교회 · Brisbane Ju-neun Church

Church website for Brisbane Ju-neun Church — public site plus a role-based
admin panel for staff and volunteers.

## Stack

| Component | Technology |
|---|---|
| Backend | Laravel 12 |
| Admin panel | Filament v4 + Filament Shield (Spatie Permission) |
| Frontend | Blade + Tailwind CSS v4 + vanilla TypeScript |
| Database | MySQL 8 |
| Media storage | Cloudflare R2 (local: `public` disk via `MEDIA_DISK`) |

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

The seeder creates roles, positions, service types, site settings and a
super admin account (set `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD` in
`.env` before seeding). In the local environment it also seeds demo
content. The admin panel lives at `/admin`.

## Project documentation

See `CLAUDE.md` for the full specification (schema, roles, phases) and
`design-assets/` for the visual design handoff the frontend follows.
