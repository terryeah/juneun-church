# 브리즈번 주는교회 · Brisbane Juneun Church

The website of Brisbane Juneun Church, a Korean church in Brisbane,
Australia — a fast, Korean-language public site backed by a fully
localised, role-based admin panel for pastors, staff and volunteers.

**Live site:** [www.juneun.com](https://www.juneun.com)

## Stack

| Component | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.4) |
| Admin panel | Filament v4 + Filament Shield (Spatie Permission) |
| Frontend | Blade + Tailwind CSS v4 + vanilla TypeScript |
| Animation | GSAP + ScrollTrigger (respects `prefers-reduced-motion`) |
| Database | MySQL 8 |
| Media storage | Cloudflare R2 (S3-compatible, zero egress fees) |
| Image processing | Intervention Image (WebP conversion + thumbnails) |
| CDN / DNS / SSL | Cloudflare |
| Hosting | Ubuntu VPS · Nginx · PHP-FPM · Let's Encrypt |

## Public site

Server-rendered Blade pages, no JavaScript framework. TypeScript
components progressively enhance the experience and every page works
without JavaScript.

- **홈** — hero, service times, church identity, latest news, latest
  sermon, meal-sharing ministry and an auto-playing photo slider with
  dot pagination
- **예배 안내** — service information and a six-video worship archive
  with click-to-load YouTube embeds
- **교회 소식** — announcements with pinned posts and rich content
- **교회 행사** — events grouped into monthly tables
- **주보** — weekly bulletin PDFs
- **갤러리** — photo albums with lightbox and infinite scroll
- **섬기는 사람들** — staff and serving members by position
- **온라인 헌금** — bank transfer details
- **오시는 길** — service times and embedded maps for both venues

The whole site runs in Australia/Brisbane time and keeps Korean-first
labelling throughout.

## Admin panel (`/admin`)

A fully Korean Filament panel, organised into grouped navigation that
only shows what the signed-in user is authorised to access.

- **콘텐츠** — announcements, events, sermons and service types
- **미디어** — albums with nested photos, and bulletins; photo uploads
  land in per-album folders automatically
- **구성원** — positions, staff members and site users
- **모니터링** — visitor analytics and a developer-only activity log

### Roles

| Role | Typical user | Access |
|---|---|---|
| `super_admin` | Developer | Everything |
| `developer` | Developer | Everything incl. activity log |
| `admin` | Pastor / office | Content, media, people, analytics |
| `content_editor` | Secretary | Announcements, events, sermons, bulletins |
| `media_coordinator` | Photographer | Albums and photos |
| `contributor` | Volunteer | Upload photos, edit own uploads only |

### Visitor analytics

Cloudflare zone metrics and Web Analytics (RUM) are snapshotted into
the local database hourly, so history survives the free-plan retention
window. The dashboard shows real-visitor counts (bot-free), page
views, total requests, a 30-day dual-axis chart, a daily breakdown
table and page-view breakdowns by country, path, referer, host,
browser, operating system and device type.

### Activity log

Spatie Activitylog records every content change and login with a
six-month retention policy, visible only to the developer role.

## Media pipeline

- Every upload is converted to WebP (max 2560px, quality 82) —
  including oversized WebP originals; iPhone HEIC is supported when
  Imagick with libheif is available; GIFs pass through untouched
- An 800px WebP thumbnail is generated per photo and served in every
  grid, slider and featured slot; full-size images load only in the
  lightbox
- Objects are stored as `albums/{album-slug}/…` and `bulletins/…` with
  one-year immutable cache headers, served through a Cloudflare-proxied
  custom domain

## Content automation

Scheduled via the Laravel scheduler (all times Australia/Brisbane):

| Schedule | Task |
|---|---|
| Hourly | Import new sermon uploads from the church YouTube channel |
| Hourly | Snapshot Cloudflare analytics into the local database |
| Daily 03:15 | Prune activity log entries older than six months |
| Daily 03:30 | Import new photo posts from the church Instagram as albums |

## SEO & performance

- Per-page titles, meta descriptions, canonical URLs and Open Graph tags
- `sitemap.xml` generated from live content, referenced from `robots.txt`
- Church structured data (JSON-LD)
- Subset self-hosted Korean webfonts (woff2 + preload)
- Preconnects for media hosts and a prioritised LCP image
- Apex domain 301-redirects to the canonical `www` host

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

Seeding creates the roles, positions, service types and site settings
the app needs, plus demo content in the local environment so every
page renders with realistic density. Fill in the third-party values in
`.env` to enable the corresponding integrations; anything left blank
degrades gracefully. The admin panel lives at `/admin`.

Useful artisan commands:

| Command | Purpose |
|---|---|
| `instagram:import` | Import recent Instagram photo posts as albums |
| `youtube:import` | Import new sermons from the YouTube channel |
| `analytics:snapshot` | Pull Cloudflare analytics into the database |
| `media:thumbnails` | Generate grid thumbnails for gallery photos |
| `media:convert-webp` | Convert stored photos to WebP |
| `media:restructure` | Reorganise media into per-album folders |

## Testing

```bash
php artisan test
```

Feature tests cover the public pages, admin authorisation (analytics
and activity-log access per role), admin wording, and the WebP
conversion pipeline.

## Deployment

The production server runs Nginx + PHP-FPM behind Cloudflare with
media on R2. Deploys are a single script that pulls the default
branch, installs dependencies, builds assets, migrates and recycles
caches. See `CLAUDE.md` for the full architectural context.
