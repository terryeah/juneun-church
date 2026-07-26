# Handoff: 브리즈번 주는교회 website (Laravel 12 + Filament v4 + Blade + Tailwind)

## Overview
Visual design for the public-facing pages of the Brisbane Ju-neun Church site. It pairs with the existing project spec (Laravel 12, Filament v4 admin, Blade + vanilla TypeScript, Tailwind, MySQL, Cloudflare R2). This document covers **only the front-end design layer**: tokens, layout, components, breakpoints and states. Data model, admin panel and infrastructure follow the project spec, not this file.

## About the design files
`Juneun Church - Design.dc.html` in this folder is a **design reference built in HTML** — a prototype of the intended look, spacing and behaviour. It is not production code and should not be copied into the repo as markup. Recreate it as Blade components + Tailwind classes inside the existing Laravel app, following the repo's conventions (PSR-12, PHPDoc-only comments, Australian English, `min-width` media queries).

The file renders as a single scrolling design document with numbered sections:

| Section | Contents |
|---|---|
| 01 · WIREFRAMES | greyscale layout/hierarchy studies |
| 02 · TYPE & COLOUR | type specimen + palette |
| 03 · HI-FI | homepage at 1240 px |
| 04 · HI-FI 1920 | homepage at 1920 px (80rem container) |
| 05 · HI-FI | interior pages: 예배 안내, 교회 소식, 주보, 갤러리, 오시는 길 |
| 06 · TABLET | homepage + 예배 안내 at ~580 px frame |
| 07 · MOBILE | homepage, menu (open), 예배 안내, 오시는 길 at 324 px frame |

Photographs are drop-zone placeholders (`<image-slot>`); in production they come from R2.

## Fidelity
**High fidelity.** Colours, type sizes, spacing, radii and states are final. Match them. Photography is placeholder only.

---

## Design tokens

### Colour
| Token | Value | Use |
|---|---|---|
| `navy` | `#16223c` | primary ink, headings, 2px structural rules |
| `navy-900` | `#0d1730` | device bezels, deepest ink |
| `navy-700` | `#233559` | body copy |
| `navy-400` | `#7688aa` | meta, captions, muted labels |
| `cream` | `#f4f1ea` | page ground, text on navy fields |
| `paper` | `#ffffff` | card / section surfaces |
| `accent` | `#004aad` | primary action, kickers, active nav |
| `accent-700` | `#00337a` | pressed / link hover |
| `accent-100` | `#dce7f7` | **nav item hover fill** |
| `on-accent` | `#ffffff` | text on accent |
| `line` | `rgba(22,34,60,.16)` | hairline dividers |
| `line-strong` | `rgba(22,34,60,.9)` | 2px section rules (use `navy`) |

Body copy never uses `accent` at paragraph size — use `navy-700`.

### Type
- **Latin / UI:** Archivo (400, 500, 600, 700, 800, 900) — Google Fonts.
- **Korean display + body:** Gmarket Sans (300/500/700) with Gothic A1 fallback. Self-host both; do not rely on the jsDelivr URLs used in the prototype.
- Scale (fluid in the prototype, listed here at desktop):
  - `h1` — Korean display, weight 400, `clamp(2.1rem, 5.6vw, 3rem)`, line-height 1.0, `-0.01em`
  - `h2` — weight 400, `clamp(1.6rem, 4.2vw, 2.25rem)`, line-height 1.11
  - `h3` — weight 400, `1.25rem`, line-height 1.2
  - Kicker / eyebrow — Archivo 800, 11–13px, `letter-spacing .16–.24em`, uppercase, colour `accent`
  - Body — Archivo or Gmarket 400, 13–17px, line-height 1.55–1.7
  - Meta — Archivo 400, 11–13px, `navy-400`
- All Korean text: `word-break: keep-all; text-wrap: pretty;` — required, otherwise headlines break mid-word.

### Radius
`8px` nav items · `10px` buttons · `12px` photos, video, map · `14px` play button · `18px` outer page frame. Nothing is fully square; nothing is pill-shaped.

### Spacing / layout
- **Container:** `max-width: 80rem` (1280px), centred. Section vertical padding 56–80px desktop, 22–30px mobile.
- **Gutters:** container padding 46px at ~1240 viewport; the 1920 mock shows 320px outer margin because the frame is fixed-width — in production this is simply the 80rem container.
- Section separators: 1px `line` hairlines between bands, 2px `navy` under the header and above the footer.
- Full-bleed elements (photo band, poster band) escape the container to viewport edges.

### Breakpoints (`min-width` only)
| Name | Value | Behaviour |
|---|---|---|
| base | — | single column, mobile nav (hamburger + full-screen menu) |
| `md` | 768px | 2-column bands, tablet nav still full (6 links, 11px) |
| `lg` | 1024px | desktop nav, hero side-by-side, 3-column service strip |
| `xl` | 1280px | 80rem container reached; type at full scale |

---

## Components → Blade map
Suggested files under `resources/views/components/`:

| Blade component | Design element |
|---|---|
| `layout/header.blade.php` | logo + wordmark + 6-item nav |
| `layout/footer.blade.php` | brand block, Locations, Contact, social, copyright |
| `home/hero.blade.php` | kicker, 2-line display headline, lede, 2 buttons, photo |
| `home/service-strip.blade.php` | 3 equal columns of service times |
| `home/value-rows.blade.php` | 3 stacked identity rows (label / verse / description) |
| `home/sermon-news.blade.php` | 교회 소식 list (left) + latest sermon video (right) |
| `home/meal-sharing.blade.php` | 반찬나눔 photo (left) + copy and stats (right) |
| `ui/poster-band.blade.php` | full-bleed navy statement band |
| `ui/photo-band.blade.php` | 3-up full-bleed gallery preview |
| `ui/button.blade.php` | primary (accent fill) / secondary (2px navy outline) |
| `ui/kicker.blade.php` | uppercase accent eyebrow |
| `ui/card-list-item.blade.php` | dated list row used by news, bulletins, sermons |

### Header
- Height: 16–18px vertical padding; `paper` background; `border-bottom: 2px solid navy`.
- Left: heart-and-cross SVG logo (34px tall, `currentColor`, stroke 3.2, no fill except the cross bars) + `브리즈번 주는교회` (Korean, 500, 18px) with `대한예수교 장로회` beneath (500, 10px, `navy-400`, `.04em`).
- Nav: 6 items — 예배 안내 · 교회 소식 · 주보 · 갤러리 · 온라인헌금 · 오시는길. Each is a link with `padding: 9px 12px; border-radius: 8px`.
  - **hover:** `background: accent-100`, ink unchanged.
  - **active / current page:** `background: accent; color: on-accent`. No underline anywhere.
  - **focus-visible:** `outline: 2px solid accent; outline-offset: 2px`.
- Mobile (<lg): logo + hamburger; menu opens full-screen, links stacked at 14px vertical padding with the same `accent-100` hover fill and hairline dividers.

### Hero
Two columns, `gap: 44px` (desktop) — text `flex: 1.05`, photo `flex: 1`, photo height 420px (520px at 1920), `object-fit: cover`, grayscale + `contrast(1.08)`, radius 12px. Copy:
- Kicker: `Brisbane Ju-neun Church · Since 2024`
- H1: `받은 은혜를` / `흘려보내는 교회` (2 lines)
- Lede (Archivo 16px, max-width 400px): `A young Korean church in Brisbane - worshipping together, giving generously, and growing as followers of Jesus Christ.` (hyphen, not em dash)
- Buttons: `예배 안내 →` (accent fill) and `오시는 길` (2px navy outline). Both weight 800, 15px, radius 10px.

### Service strip
Grid `1fr 1px 1fr 1px 1fr` — the 1px cells are the dividers, so the three content columns are exactly equal and the first is flush with the container edge. Column gap 30px, vertical padding 26px, hairline top and bottom. Each column: accent kicker, `h3` time + venue, `navy-400` address line.

### Value rows (주는교회 identities)
Three rows, not columns (deliberately different rhythm from the strip above). Each row: `minmax(0,280px) minmax(0,1fr)`, gap 40px, padding 26px 0, hairline between rows.
Left = name with the English word in `accent` inside parentheses: 주는(Lord)교회 · 보여주는(Revealing)교회 · 주는(Giving)교회.
Right = verse (Korean, 15px, `navy`), reference (Archivo 700, 11px, `accent`, `.08em`), then description (13.5px, `navy-700`).

### Sermon + news band
Grid `1fr 1.3fr`. **Left = 교회 소식** (accent kicker, 3 dated rows with hairline tops, `PINNED` flag in accent on the first, `소식 전체 보기 →` footer link). **Right = 최근 예배** (kicker + `YouTube →` link, 16:9 video block radius 12px with a 58px accent play square radius 14px, then title `h3` and `엄현준 담임목사 · date`). Video is click-to-load per the TS spec — poster frame first, iframe on click.

### Meal sharing (반찬나눔)
Grid `1.3fr 1fr`. **Left = photo**, 16:9, radius 12px, vertically centred in its column. **Right = copy**, `border-left: 1px line`: accent kicker `Meal Sharing · 반찬나눔`, headline `정성껏 준비한 반찬을 / 이웃과 나눕니다` (1.7rem at 1240), paragraph, then two stats above a hairline (`71명 / 지난 나눔 참여`, `매월 정기 / Regular serving`). The photo column and the sermon video are the same rendered width — keep that alignment.

### Poster band
Full-bleed `navy` field, padding 76px (110px at 1920). Kicker `Visit Us · Sunday 11:30 AM` in `cream` at 55% opacity; statement `누구나 환영합니다. / 이번 주일, 함께 예배해요.` in `cream` at h1 scale. This is the one saturated field on the page — do not add a second.

### Photo band
3 equal square photos, `gap: 2px` on a navy ground, radius 10px, grayscale. Acts as a gallery preview before the footer.

### Footer
`grid-template-columns: minmax(0,1fr) auto auto; column-gap: 88px` — brand block left, **Locations and Contact pushed to the right edge**. Hairline above the copyright row. Copyright row: `© 2024–2026 Brisbane Ju-neun Church` left, `대한예수교장로회` right (Archivo 400, 11px, `navy-400`). Social icons (Instagram, YouTube) are 22px stroke icons, `navy`, hover `accent`, linking to the church accounts.

---

## Interactions & behaviour
- Nav hover/active as above; every interactive element gets a themed hover, a pressed state (`accent-700`) and a 2px `accent` focus ring. No browser defaults.
- Sermon video: poster → click loads the YouTube iframe (progressive enhancement; without JS the link goes to YouTube).
- Gallery: album grid → lightbox with keyboard nav; infinite scroll for photos. The design document's gallery block is a Swiper carousel used only as a preview — the production gallery page is a grid.
- Mobile nav: hamburger toggles a full-screen menu; body scroll locked while open.
- Images: grayscale + `contrast(1.08)` on every content photograph, all breakpoints. Lazy-load below the fold.
- Transitions: 150ms ease on background/colour changes only. No motion on layout.

## Assets
- Logo: inline SVG (heart + cross), single path pair, stroke 3.2 on a `2 12 76 66` viewBox. Ship as a Blade partial so it inherits `currentColor`.
- Fonts: Archivo (Google), Gmarket Sans (self-host the woff files).
- Photography: none supplied. Placeholders mark intent — worship/congregation hero, 반찬나눔 serving photo, 3 gallery squares (fellowship, worship, next-gen).
- Icons: Instagram + YouTube inline SVGs are in the design file; use Lucide for anything new.

## Content notes / decisions still needed
1. **Nav vs. spec pages.** The design's nav is 예배 안내 · 교회 소식 · 주보 · 갤러리 · 온라인헌금 · 오시는길. The project spec also lists **교회 행사** and **섬기는 사람들**, and has no 온라인헌금 page. Reconcile before building the header — 8 items is too many for one row; consider grouping (예배 / 소식 / 미디어 / 교회) or dropping 온라인헌금 into the footer.
2. **Address.** The design uses `24 Levington Rd, Eight Mile Plains` (본당) and `147 Kameruka St, Calamvale` (교육관). The spec says `199 Rochedale Rd, Rochedale QLD 4123`. A later flyer gives `71 Newnham Rd` with services at 10:30 and 1:30. Confirm the live details; they appear in the header strip, 예배 안내, 오시는 길 and the footer.
3. **Giving details** (Westpac BSB 034069 / acct 615113) are not yet placed in any screen — needs an 온라인헌금 page design if that nav item stays.
4. Service times in the design (주일 11:30, 수요 19:30, 주일학교 11:30) must be driven by `site_settings`, not hard-coded in Blade.

## Files in this bundle
- `Juneun Church - Design.dc.html` — the design document (open in a browser)
- `support.js`, `image-slot.js` — runtime the file needs to render
- `tailwind-tokens.js` — the tokens above as a Tailwind theme extension
