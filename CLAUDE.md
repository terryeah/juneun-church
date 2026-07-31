# Korean Church Website

> **This project uses [ponytail](https://github.com/DietrichGebert/ponytail) — lazy senior dev mode.**
> Before writing any code, follow `AGENTS.md`: YAGNI, reuse what's here, stdlib/native/existing-dep first,
> deletion over addition, shortest working diff. No unrequested abstractions. Question complex requests.
> This applies to every future change in this repo.


A Laravel-based church website with Filament admin panel, Cloudflare R2 media storage, and role-based content management for multiple church staff and volunteers.

## Tech Stack

| Component | Technology |
|-----------|------------|
| **Backend Framework** | Laravel 12 (latest, no starter kit) |
| **Admin Panel** | Filament v4 (free, open source) |
| **Frontend** | Blade Templates + Vanilla TypeScript |
| **CSS Framework** | Tailwind CSS |
| **Database** | MySQL 8 |
| **Object Storage** | Cloudflare R2 (S3-compatible, zero egress fees) |
| **CDN / Proxy** | Cloudflare (free tier) |
| **Hosting** | AWS Lightsail Sydney ($7/mo - 1GB RAM, 40GB SSD) |
| **Permissions** | Spatie Laravel Permission + Filament Shield |
| **Authentication** | Custom Laravel Auth (no starter kit, admin-created accounts) |

## Estimated Monthly Costs

- Digital Ocean Droplet: $7.00
- Cloudflare (CDN, DNS, SSL): $0.00 (free tier)
- Cloudflare R2 Storage: ~$0.00 (10GB free tier likely sufficient)
- Domain (~$15/year): ~$1.25
- **Total: ~$8.25/month**

## Public Pages

| Page | Korean | Description | Features |
|------|--------|-------------|----------|
| Home | 홈 | Landing page | Hero section, latest news (3), upcoming events, latest sermon |
| News | 교회 소식 | Church announcements | Blog-style list, pagination, pinned posts, RichEditor content |
| Events | 교회 행사 | Church events | Monthly grouped tables (행사일, 행사명, 행사장) |
| Staff | 섬기는 사람들 | Church servants | Hierarchical display by position, photos, bio |
| Sermons | 예배 | Worship recordings | YouTube integration, featured + grid layout, lazy loading |
| Gallery | 갤러리 | Photo galleries | Albums, thumbnails, lightbox, infinite scroll, R2 storage |
| Bulletins | 주보 | Weekly bulletins | PDF uploads, date-sorted list, R2 storage |
| Location | 오시는 길 | Directions | Google Maps embed (199 Rochedale Rd, Rochedale QLD 4123), service times, contact |

## Database Schema

### Core Tables

```
users
├── id, name, email, password, remember_token, timestamps
└── Managed by: Spatie Permission + Filament Shield

announcements (교회 소식)
├── id, title, slug, content (longText - RichEditor HTML)
├── featured_image (nullable), is_published, is_pinned
└── published_at, expires_at (nullable), created_by, timestamps

events (교회 행사)
├── id, title (행사명), event_date (행사일), event_time (nullable)
├── end_date (nullable), location (행사장), description (nullable)
└── is_published, created_by, timestamps

positions
├── id, name (담임목사, 부목사, 전도사, 장로, 권사, 집사, etc.)
└── category (pastoral, elder, deacon, volunteer), sort_order, timestamps

staff_members (섬기는 사람들)
├── id, name, position_id (FK), department (nullable)
├── photo (R2 path), bio (nullable), email (nullable), phone (nullable)
└── sort_order, is_published, timestamps

service_types
└── id, name (주일예배, 수요예배, 금요기도회, 특별예배), sort_order, timestamps

sermons (예배)
├── id, title, youtube_video_id, preacher (nullable)
├── sermon_date, service_type_id (FK), scripture_reference (nullable)
├── description (nullable), is_published, created_by, timestamps
└── Note: Designed for future YouTube API auto-fetch integration

albums
├── id, title, slug, description (nullable), event_date
└── cover_photo_path (nullable), is_published, created_by, timestamps

photos
├── id, album_id (FK), filename, original_filename
├── path (R2), thumbnail_path (R2), width, height, file_size
└── caption (nullable), sort_order, uploaded_by (FK), timestamps

bulletins (주보)
└── id, title, file_path (R2 - PDF), published_at, created_by, timestamps

site_settings
├── id, key, value, group (contact, service_times, social), timestamps
└── Stores: address, phone, email, service times, social media links
```

## User Roles & Permissions

### Role Hierarchy

| Role | Intended User | Access Level |
|------|---------------|--------------|
| **super_admin** | Developer | Full system access, all permissions |
| **admin** | Pastor / Office Manager | Manage content, create users (not super_admin) |
| **content_editor** | Secretary / Comms Lead | Manage announcements, events, sermons, bulletins |
| **media_coordinator** | Youth Leader / Photographer | Manage albums, upload/edit/delete photos, batch upload |
| **contributor** | Volunteer Members | Upload photos to existing albums, edit/delete own only |

### User Management Approach

- **Pattern:** Single users table with role-based permissions (Spatie)
- **Creation:** Admin creates users and sends email invitation with password setup link
- **No public registration:** Admin panel access is invite-only

### Permission Matrix

| Resource | super_admin | admin | content_editor | media_coordinator | contributor |
|----------|:-----------:|:-----:|:--------------:|:-----------------:|:-----------:|
| Users | Full | Create/Edit* | ❌ | ❌ | ❌ |
| Announcements | Full | Full | Full | ❌ | ❌ |
| Events | Full | Full | Full | ❌ | ❌ |
| Staff Members | Full | Full | ❌ | ❌ | ❌ |
| Sermons | Full | Full | Full | ❌ | ❌ |
| Albums | Full | Full | ❌ | Full | ❌ |
| Photos | Full | Full | ❌ | Full | Own only |
| Bulletins | Full | Full | Full | ❌ | ❌ |
| Site Settings | Full | Full | ❌ | ❌ | ❌ |

*Admin can edit users except super_admin accounts

## Development Phases

### Phase 1: Project Setup & Infrastructure
- [x] Install Laravel 12 (fresh, no starter kit)
- [x] Configure Vite with TypeScript
- [x] Set up Tailwind CSS
- [x] Configure MySQL database
- [x] Set up Cloudflare R2 filesystem driver
- [x] Install and configure Filament v4
- [x] Install Spatie Permission + Filament Shield
- [x] Create custom authentication (login, password reset)
- [x] Seed roles and initial super_admin user

### Phase 2: Database & Models
- [x] Create all migrations
- [x] Create Eloquent models with relationships
- [x] Set up model factories for testing
- [x] Create seeders for positions, service_types, site_settings

### Phase 3: Filament Admin Resources
- [x] AnnouncementResource (RichEditor for content)
- [x] EventResource (simple form fields)
- [x] PositionResource
- [x] StaffMemberResource (with image upload to R2)
- [x] ServiceTypeResource
- [x] SermonResource (YouTube ID validation)
- [x] AlbumResource
- [x] PhotoResource (batch upload, R2 integration, thumbnail generation)
- [x] BulletinResource (PDF upload to R2)
- [x] SiteSettingResource
- [x] UserResource (with role assignment)
- [x] Configure Shield permissions for all resources

### Phase 4: Frontend - Blade Templates & Layout
- [x] Create base layout with navigation
- [x] Build responsive header/footer components
- [x] Create reusable Blade components
- [x] Implement mobile-friendly navigation

### Phase 5: Frontend - Public Pages
- [x] 홈 (Home) - Hero, latest content sections
- [x] 교회 소식 - Announcements list with pagination
- [x] 교회 행사 - Monthly grouped event tables
- [x] 섬기는 사람들 - Staff cards by position hierarchy
- [x] 예배 - YouTube featured + grid with lazy loading
- [x] 갤러리 - Albums index + album detail with lightbox
- [x] 주보 - Bulletins list with PDF download
- [x] 오시는 길 - Google Maps embed, contact info

### Phase 6: TypeScript Components
- [x] YouTube lazy loader (click to load iframe)
- [x] Photo gallery with lightbox
- [x] Infinite scroll for gallery photos
- [x] Mobile navigation toggle

### Phase 7: Testing & Optimisation
- [ ] Test all Filament resources and permissions
- [ ] Test R2 uploads and retrieval
- [ ] Mobile responsiveness testing
- [ ] Performance optimisation (caching, lazy loading)
- [ ] SEO meta tags

### Phase 8: Deployment
- [ ] Provision Digital Ocean droplet
- [ ] Configure Nginx + PHP-FPM
- [ ] Set up Cloudflare DNS and SSL
- [ ] Configure R2 bucket and credentials
- [ ] Set up deployment workflow
- [ ] Configure backups

## File Structure Overview

```
app/
├── Filament/Resources/     # All admin panel resources
├── Http/Controllers/       # Frontend page controllers
├── Models/                 # Eloquent models
├── Services/               # PhotoUploadService, YouTubeService (future)
└── Policies/               # Model policies for authorisation

resources/
├── views/
│   ├── layouts/            # Base Blade layout
│   ├── components/         # Reusable Blade components
│   └── pages/              # Public page views
├── ts/                     # TypeScript source files
│   ├── app.ts              # Main entry point
│   └── components/         # SermonSlider, Lightbox, InfiniteScroll, MobileNav
└── css/                    # Tailwind CSS
```

## Code Style Guidelines

### PHP
- Use comprehensive PHPDoc comments for all classes and functions
- Do not use single-line comments (`//`)
- Follow PSR-12 coding standards
- Use Australian English for all comments and documentation

### TypeScript
- Use JSDoc comments for all functions
- Do not use single-line comments (`//`)
- Use Australian English for all comments and documentation

### SCSS/CSS
- Always use `min-width` for media queries
- Never use `max-width` for media queries

### Example PHP Comment Style

```php
<?php

/**
 * Handles announcement display and management.
 */
class AnnouncementController extends Controller
{
    /**
     * Display a listing of published announcements.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        /** Fetch announcements with pinned items first */
        $announcements = Announcement::where('is_published', true)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('pages.announcements.index', compact('announcements'));
    }
}
```

### Example TypeScript Comment Style

```typescript
/**
 * Lightbox Component
 *
 * Displays images in a fullscreen overlay with navigation.
 */
export class Lightbox {
    private container: HTMLElement;
    private images: HTMLImageElement[];
    private currentIndex: number = 0;

    /**
     * Creates a new Lightbox instance.
     *
     * @param container - The gallery container element
     */
    constructor(container: HTMLElement) {
        this.container = container;
        this.images = Array.from(container.querySelectorAll('img[data-lightbox]'));
        this.init();
    }

    /**
     * Initialises the lightbox and binds event listeners.
     */
    private init(): void {
        this.bindEvents();
    }
}
```

## Key Technical Decisions

1. **No Laravel Breeze/Jetstream** - Custom auth implementation for simplicity
2. **No Wayfinder/Inertia** - Traditional server-rendered Blade templates
3. **No React/Vue** - Vanilla TypeScript for interactive components only
4. **RichEditor (native Filament)** - No third-party editor plugins
5. **Cloudflare R2 over AWS S3** - Zero egress fees, generous free tier
6. **Progressive Enhancement** - Site works without JavaScript, TS enhances UX

## Future Enhancements

- YouTube API integration for automatic sermon fetching
- Email notifications for new announcements
- Event registration system
- Member directory (private, authenticated access)
- Prayer request submission
- Multi-language support (Korean/English)

## Useful Commands

```bash
# Development
php artisan serve
npm run dev

# Generate Filament resources
php artisan make:filament-resource ModelName --generate

# Generate permissions with Shield
php artisan shield:generate --all

# Clear caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```

## Environment Variables (Required)

```env
# App
APP_NAME="Church Name"
APP_URL=https://church-domain.org.au

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=church_db
DB_USERNAME=
DB_PASSWORD=

# Cloudflare R2
CLOUDFLARE_R2_ACCESS_KEY=
CLOUDFLARE_R2_SECRET_KEY=
CLOUDFLARE_R2_BUCKET=church-assets
CLOUDFLARE_R2_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://assets.church-domain.org.au

# Mail (for user invitations)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@church-domain.org.au
```

---

*This document serves as the primary context for Claude Code sessions working on this project.*
