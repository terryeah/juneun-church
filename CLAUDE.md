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
| **Authentication** | Custom Laravel Auth (no starter kit) with public 가입 신청 + admin approval, and TOTP two-factor for staff |

## Estimated Monthly Costs

- Server (see Tech Stack): $7.00
- Cloudflare (CDN, DNS, SSL): $0.00 (free tier)
- Cloudflare R2 Storage: ~$0.00 (10GB free tier likely sufficient)
- Domain (~$15/year): ~$1.25
- **Total: ~$8.25/month**

## Site Pages

Four pages are open to the street; five are 성도 전용 as **whole pages**. The
gate is `User::isChurchMember()` - an account linked to a 교적 (`members`)
record - and never a role, so a staff account with no 교적 record is treated
like anyone else. A reader who fails it is answered with
`pages/members-only.blade.php`: HTTP 200, `noindex`, no query ever run, so no
title or filename reaches the response. The notice offers 로그인 to a guest
(carrying `?next=` back to the page they wanted) and 교적 등록 guidance with
the `contact_email` setting to a signed-in non-성도.

| Page | Korean | Route | Access | Features |
|------|--------|-------|--------|----------|
| Home | 홈 | `/` | Public | Hero photo named in 사이트 설정, latest 4 announcement titles, 하이라이트 announcement, latest sermon, hand-picked photo band |
| Worship | 예배 안내 | `/worship` | Public | YouTube recordings, lazy-loaded iframes |
| Staff | 섬기는 사람들 | `/people` | Public | 성도 grouped by 직분 in `sort_order`, photos, bio |
| Location | 오시는 길 | `/location` | Public | A Google Maps embed per address from 사이트 설정, service times, contact |
| News | 교회 소식 | `/news` | 성도 전용 | Blog-style list, pagination, pinned posts, RichEditor content |
| Events | 교회 행사 | `/events` | 성도 전용 | Monthly grouped tables (행사일, 행사명, 행사장) |
| Downloads | 자료실 | `/downloads` | 성도 전용 | 주보 and 문서 tabs; PDFs are private on R2 and streamed through the app |
| Giving | 헌금 | `/giving` | 성도 전용 | Bank details plus the last 12 weeks of 헌금 내역 |
| Album | 앨범 | `/album` | 성도 전용 | 사진 and 동영상 albums, thumbnails, lightbox, infinite scroll |

Home is the one place restricted content surfaces publicly, deliberately: it
lists the latest four announcement **titles** and shows the 하이라이트 notice's
opening lines and image to everybody. `sitemap.xml` lists the four public
pages only.

A 주보 opens as a page (`bulletin.file`) with the PDF embedded: a guest gets
the sign-in notice there, a signed-in non-성도 gets 404, and a missing object
404s the page. The PDF itself (`bulletin.pdf`) is a separate address that 404s
for anyone off the 교적 and 301s a filename that does not match the record. A
문서 streams straight from `document.file`, named with its real Korean title.
`files:rotate-restricted` rewrites every stored 주보/문서 under a fresh private
name and purges the old URL from Cloudflare.

## Database Schema

### Core Tables

```
users
├── id, name, email, password, remember_token, created_by, timestamps
├── is_test_account, is_audit_exempt, last_login_at, email_verified_at
├── app_authentication_secret, app_authentication_recovery_codes (2단계 인증)
└── Roles by Spatie Permission + Filament Shield. 성도-ness is NOT a role:
    it is members.user_id, asked through User::isChurchMember()

members (교적 / 성도, and 섬기는 사람들 by another name)
├── id, name, gender, birth_date, phone, email, address, photo (R2)
├── position_id (FK), department, bio, sort_order, is_published
├── baptism_type, baptism_date, status, registered_at, new_family_completed_at
├── head_id (FK members), cell_id (FK), relationship, notes
└── user_id (FK users, nullable) - the 사이트 계정 toggle, timestamps

cells (셀)
└── id, name, leader_id (FK members), description, sort_order, timestamps

membership_requests (가입 신청)
├── id, name, birth_date, phone, email, password, note, status
├── matched_member_id (FK), reviewed_by (FK), reviewed_at, redacted_at
└── verification_method, verification_note, timestamps

announcements (교회 소식)
├── id, title, slug, content (longText - RichEditor HTML)
├── featured_image (nullable), is_published, is_pinned, is_highlighted
└── published_at, expires_at (nullable), created_by, timestamps

events (교회 행사)
├── id, title (행사명), event_date (행사일), event_time (nullable)
├── end_date, end_time (nullable), location (행사장), description (nullable)
└── is_published, created_by, timestamps

positions (직분)
└── id, name (담임목사, 부목사, 선교사, 전도사, 장로, 권사, 집사, 성도), sort_order, timestamps

ministries (부서)
└── id, name, description (nullable), sort_order, timestamps

service_types
└── id, name (주일예배, 수요예배, 금요기도회, 주일학교, 특별예배), sort_order, timestamps

sermons (예배 영상)
├── id, title, youtube_video_id, thumbnail_path (R2), preacher (nullable)
├── sermon_date, service_type_id (FK), scripture_reference (nullable)
├── description (nullable), is_published, created_by, timestamps
└── Filled automatically by the hourly youtube:import command

albums
├── id, title, slug, type (photo|video), description (nullable), event_date
├── cover_photo_path, cover_thumbnail_path (nullable)
└── is_published, created_by, timestamps

photos
├── id, album_id (FK), filename, original_filename
├── path (R2), thumbnail_path (R2), width, height, file_size
├── caption (nullable), sort_order, featured_in_slider (home band, max 10)
└── uploaded_by (FK), timestamps

videos
└── id, album_id (FK), youtube_id, title, description, sort_order,
    created_by, timestamps

bulletins (주보)
└── id, title, file_path (R2 - private PDF), published_at, created_by, timestamps

documents (자료실 문서/서식)
└── id, title, description, file_path (R2 - private PDF), published_at,
    created_by, timestamps

offerings (헌금 내역)
└── id, sunday_date, items (JSON), note, created_by, timestamps

personal_offerings (개인 헌금)
└── id, offering_id (FK), member_id (FK), name, category, amount, note, timestamps

site_settings
├── id, key, value, group (contact, service_times, social, home, giving), timestamps
└── Stores: church name, addresses, phone, email, service times, giving
    accounts, social links, home_hero_photo (a photos.filename)
```

No `staff_members` table: 섬김이 is `StaffMemberResource`, a read-only view of
`members` that have a 직분 or a 부서. There is no per-record 성도 전용 column
anywhere - `2026_08_16_000000_drop_is_members_only_columns.php` removed the
last of them from announcements, bulletins, documents and albums.

## User Roles & Permissions

### Role Hierarchy

| Role | Korean | Intended User | Access Level |
|------|--------|---------------|--------------|
| **super_admin** | 최고 관리자 | Owner | Every permission |
| **developer** | 개발자 | Developer | Every permission, plus the developer-only screens (활동 기록, DB 구조, Google Analytics, 역할, 비밀번호 재설정 링크) |
| **admin** | 관리자 | Pastor / Office Manager | Everything bar the developer-only screens |
| **content_editor** | 편집자 | Secretary / Comms Lead | Content and media only |
| **finance_officer** | 재정부 | Treasurer | 헌금 내역 and 개인 헌금, nothing else |
| **general_member** | 일반회원 | Approved 가입 신청 | No permissions; reaches only their own profile page |

`media_coordinator` and `contributor` were tried and retired in
`2026_08_06_140000_retire_media_coordinator_and_contributor_roles.php`.
`member` became `general_member` in
`2026_08_12_000000_rename_the_member_role_to_general_member.php`.

**A role never makes somebody a 성도.** That is `User::isChurchMember()`, which
asks whether the account is linked to a 교적 record. Roles govern the admin
panel; the 교적 governs the 성도 전용 pages of the public site.

### User Management Approach

- **Pattern:** Single users table with role-based permissions (Spatie)
- **Public sign-up:** `/signup` files a 가입 신청; an administrator matches it
  against the 교적 and approves it, and the account is created with the
  password the applicant chose
- **Or by hand:** the 사이트 계정 section of a 성도 record creates the account;
  switching it off *deletes* the account
- **UserResource is a read-only list.** Accounts are made and removed from the
  성도 screen, never here
- **2단계 인증:** mandatory for staff accounts, waived for 일반회원 and test
  accounts (`User::isExemptFromMultiFactorAuthentication()`)

### Permission Matrix

super_admin and developer hold every permission, so only the three working
roles are listed. Source of truth: `RolePermissionSeeder` plus the grant
migrations.

| Resource | admin | content_editor | finance_officer |
|----------|:-----:|:--------------:|:---------------:|
| Announcements | Full | Full | ❌ |
| Events | Full | Full | ❌ |
| Sermons | Full | Full | ❌ |
| Bulletins | Full | Full | ❌ |
| Documents | Full | Full | ❌ |
| Albums | Full | Full | ❌ |
| Photos | Full | Full | ❌ |
| Videos | Full | Full | ❌ |
| Service Types | Full | Full | ❌ |
| Ministries | Full | Full | ❌ |
| Positions | Full | ❌ | ❌ |
| Site Settings | Full | ❌ | ❌ |
| Members / 섬김이 | Full | ❌ | ❌ |
| Cells | Full | ❌ | ❌ |
| Membership Requests | Full | ❌ | ❌ |
| Offerings | Full | ❌ | Full |
| Personal Offerings | Full | ❌ | Full |
| Users (read-only list) | Full | ❌ | ❌ |
| Roles (Shield) | ❌ | ❌ | ❌ |

Albums and Photos reached content editors through
`2026_08_06_160000_grant_gallery_and_roster_to_content_editors.php`; Site
Settings and Positions were pulled back to administrators by
`2026_08_06_170000_restrict_settings_and_positions_to_admins.php`. 성도 and 셀
were deliberately left with administrators - they hold the congregation's
personal details.

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
- [x] 콘텐츠: Announcement (RichEditor), Event, Sermon, Bulletin, Document
- [x] 미디어: Album, Photo (WebP conversion + thumbnails on R2), Video
- [x] 재정: Offering, PersonalOffering
- [x] 교적: Member, Cell, StaffMember (read-only view of Member)
- [x] 계정: MembershipRequest, User (read-only list)
- [x] 기준 정보: SiteSetting, ServiceType, Ministry, Position
- [x] 모니터링: Activity, plus the Analytics / GoogleAnalytics / DatabaseGraph pages
- [x] Wiki page (`resources/views/filament/pages/wiki.blade.php`) - the Korean
      handbook for church staff; keep it true whenever behaviour changes
- [x] Configure Shield permissions for all resources

### Phase 4: Frontend - Blade Templates & Layout
- [x] Create base layout with navigation
- [x] Build responsive header/footer components
- [x] Create reusable Blade components
- [x] Implement mobile-friendly navigation

### Phase 5: Frontend - Site Pages
- [x] 홈 (Home) - Hero, latest content sections
- [x] 예배 안내 - YouTube archive with lazy loading
- [x] 섬기는 사람들 - Cards by 직분 hierarchy
- [x] 오시는 길 - Google Maps embeds, contact info
- [x] 교회 소식 - Announcements list with pagination
- [x] 교회 행사 - Monthly grouped event tables
- [x] 자료실 - 주보 and 문서 tabs, PDFs streamed through the app
- [x] 헌금 - Bank details plus weekly 헌금 내역
- [x] 앨범 - Albums index + photo and video album detail
- [x] 로그인 / 가입 신청, and the shared 성도 전용 notice

### Phase 6: TypeScript Components
- [x] YouTube lazy loader (click to load iframe) and video modal
- [x] Photo gallery with lightbox
- [x] Infinite scroll for gallery photos
- [x] Mobile navigation toggle
- [x] Home photo slider, tabbed section swap, scroll animations

### Phase 7: Testing & Optimisation
- [x] Feature tests over the panel, the roles and the 성도 전용 pages
      (`tests/Feature`, run by `.github/workflows/tests.yml` on every push)
- [x] R2 upload conversion covered (`UploadConversionTest`)
- [x] SEO meta tags, Open Graph and `sitemap.xml`; 성도 전용 pages send `noindex`
- [ ] Mobile responsiveness testing
- [ ] Performance optimisation (caching, lazy loading)

### Phase 8: Deployment
The site is live behind Cloudflare, with R2 for media and scheduled commands
running (`routes/console.php`). CI runs the suite on every push to `master`.
Server provisioning and backups are managed outside this repository.

## File Structure Overview

```
app/
├── Console/Commands/       # youtube:import, instagram:import, files:rotate-restricted, ...
├── Filament/
│   ├── Resources/          # All admin panel resources
│   ├── Pages/              # Dashboard, Wiki, Analytics, DatabaseGraph
│   └── Support/            # SaveUploadsAsWebp, Author, shared field helpers
├── Http/Controllers/       # Public page controllers, Auth/, RestrictedFileController
├── Models/                 # Eloquent models (+ Concerns/ for shared traits)
├── Services/               # CloudflareCachePurger, Cloudflare/Google analytics, YoutubeThumbnail
└── Policies/               # Model policies for authorisation

resources/
├── views/
│   ├── components/layout/  # app layout, header, footer, nav-link
│   ├── components/ui/      # Reusable Blade components (sign-in-required, buttons, ...)
│   ├── pages/              # Site page views, incl. members-only.blade.php
│   ├── filament/pages/     # Panel page views, incl. the Korean staff wiki
│   ├── auth/ and errors/   # Login, sign-up, 404
├── ts/                     # TypeScript source files
│   ├── app.ts              # Main entry point
│   ├── admin/              # Panel-only scripts
│   └── components/         # Lightbox, InfiniteScroll, MobileNav, PhotoSlider,
│                           # SectionSwap, VideoModal, YouTubeLazy, Animations
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

- Email notifications for new announcements
- Event registration system
- Prayer request submission
- Multi-language support (Korean/English)

Already shipped, so no longer on this list: automatic sermon fetching from
YouTube (`youtube:import`) and the private 교적 directory (교적 › 성도).

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

# Give every 주보/문서 a fresh private address and purge the old one
# from the CDN. Run it if a link is ever thought to have escaped.
php artisan files:rotate-restricted

# Scheduled by routes/console.php, runnable by hand
php artisan youtube:import
php artisan instagram:import
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

# Session - 43200 minutes is the 30 days the site promises 성도
SESSION_LIFETIME=43200

# Cloudflare R2 (MEDIA_DISK selects it; 'public' is the local fallback)
MEDIA_DISK=r2
CLOUDFLARE_R2_ACCESS_KEY=
CLOUDFLARE_R2_SECRET_KEY=
CLOUDFLARE_R2_BUCKET=church-assets
CLOUDFLARE_R2_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://assets.church-domain.org.au

# Cloudflare API - edge cache purging when media is replaced or rotated
CLOUDFLARE_API_TOKEN=
CLOUDFLARE_ZONE_ID=
CLOUDFLARE_ACCOUNT_ID=

# Mail (가입 신청 notice to the office, 승인 notice to the applicant, password resets)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=no-reply@church-domain.org.au
MAIL_REPLY_TO_ADDRESS=hello@church-domain.org.au
```

Optional: `GOOGLE_MAPS_API_KEY` (address autocomplete in the panel),
`UMAMI_*` (방문자 통계), `ANALYTICS_IGNORED_IPS`.

---

*This document serves as the primary context for Claude Code sessions working on this project.*
