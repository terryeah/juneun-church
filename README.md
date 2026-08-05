# 브리즈번 주는교회 · Brisbane Juneun Church

The website of Brisbane Juneun Church, a Korean church in Brisbane,
Australia — a fast, Korean-language site for the church community.

**Live site:** [www.juneun.com](https://www.juneun.com)

## Stack

| Component | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.4) |
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

- **홈**: hero, service times, church identity, latest news, latest
  sermon, meal-sharing ministry and an auto-playing photo slider with
  dot pagination
- **예배 안내**: service information and a six-video worship archive
  with click-to-load YouTube embeds
- **교회 소식**: announcements with pinned posts and rich content
- **교회 행사**: events grouped into monthly tables
- **주보**: weekly bulletin PDFs
- **갤러리**: photo albums with lightbox and infinite scroll
- **섬기는 사람들**: staff and serving members by position
- **헌금**: bank transfer details
- **오시는 길**: service times and embedded maps for both venues

The whole site runs in Australia/Brisbane time and keeps Korean-first
labelling throughout.

## Media pipeline

- Every uploaded image is converted to WebP (max 2560px, quality 82) —
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

Seeding creates the base data the app needs, plus demo content in the
local environment so every page renders with realistic density. Fill
in the third-party values in `.env` to enable the corresponding
integrations; anything left blank degrades gracefully.

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

Feature tests cover the public pages and the WebP conversion
pipeline.

## Deployment

The production server runs Nginx + PHP-FPM behind Cloudflare with
media on R2. Deploys are a single script that pulls the default
branch, installs dependencies, builds assets, migrates and recycles
caches. See `CLAUDE.md` for the full architectural context.

## Licence

The source code is released under the [MIT Licence](LICENSE), so another
church is free to take it, adapt it and run its own site.

The church's name and logo, its photographs, bulletins, announcements and
the personal details of its congregation are not covered and remain the
property of Brisbane Juneun Church.
