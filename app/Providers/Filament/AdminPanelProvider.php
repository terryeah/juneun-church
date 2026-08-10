<?php

namespace App\Providers\Filament;

use App\Filament\Auth\EditProfile;
use App\Filament\Pages\Analytics;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DatabaseGraph;
use App\Filament\Pages\GoogleAnalytics;
use App\Filament\Pages\SiteIntroduction;
use App\Filament\Pages\Videos;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Albums\AlbumResource;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Bulletins\BulletinResource;
use App\Filament\Resources\Cells\CellResource;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\Ministries\MinistryResource;
use App\Filament\Resources\Offerings\OfferingResource;
use App\Filament\Resources\PersonalOfferings\PersonalOfferingResource;
use App\Filament\Resources\Photos\Pages\EditPhoto;
use App\Filament\Resources\Photos\PhotoResource;
use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Sermons\SermonResource;
use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Filament\Resources\Users\UserResource;
use App\Http\Middleware\ExemptMembersFromMultiFactorAuthentication;
use App\Http\Middleware\RedirectMembersToProfile;
use App\Support\RoleLabel;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\Pages\Login;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Sidebar badges for pages, which hold no model and so cannot be
     * read from the granted permissions the way a resource can. Their
     * audience lives in each page's own canAccess() instead.
     *
     * @var array<class-string, string>
     */
    protected const PAGE_BADGES = [
        Analytics::class => 'admin',
        SiteIntroduction::class => 'admin',
        DatabaseGraph::class => 'developer',
        GoogleAnalytics::class => 'developer',
    ];

    /**
     * Roles whose grants say nothing about how wide a resource's
     * audience is, and which roleBadge() therefore ignores.
     *
     * The badge means "beyond an ordinary content editor". A super
     * admin holds everything, so counting it would erase every badge;
     * a single-purpose specialist holds one corner of the panel, so
     * counting it would erase the badge on exactly that corner -
     * finance officers reach 헌금 내역 and 개인 헌금, which are still
     * administrator menus for everyone else in the church.
     *
     * Deriving this from the grant counts instead (a role holding one
     * or two models is a specialist) would need no editing at all, but
     * it fails silently in the wrong direction: a future two-model role
     * over 성도 and 셀 would keep the administrator badge on the
     * congregation's personal details while a non-administrator read
     * them. Forgetting to list a role here only drops a badge, which is
     * visible on the very screen that is wrong, and a new role already
     * touches RoleSeeder, RolePermissionSeeder and a migration - one
     * more line in the same change is cheap.
     *
     * @var list<string>
     */
    protected const NON_WIDENING_ROLES = ['super_admin', 'finance_officer'];

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->profile(EditProfile::class)
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ], isRequired: fn (): bool => ! app()->runningUnitTests())
            ->multiFactorAuthenticationRequiredMiddlewareName(ExemptMembersFromMultiFactorAuthentication::class)
            ->defaultThemeMode(ThemeMode::System)
            ->favicon(asset('favicon.svg'))
            ->subNavigationPosition(SubNavigationPosition::Top)
            ->navigation(fn (NavigationBuilder $builder): NavigationBuilder => static::buildNavigation($builder))
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('<style>html:lang(ko),html:lang(ko) .fi-body{word-break:keep-all;overflow-wrap:break-word}.fi-ta-text-item-label,.fi-in-text,.fi-fo-field-wrp-hint,.fi-fo-field-wrp-helper-text,.fi-sc-text,.fi-header-subheading,.fi-section-description,.fi-modal-description{word-break:keep-all;overflow-wrap:break-word}.fi-login-theme-switcher{display:flex;justify-content:center;margin-block-start:1.5rem}.fi-avatar-initials{display:inline-flex;flex-shrink:0;align-items:center;justify-content:center;container-type:inline-size;overflow:hidden;background-color:var(--gray-950);color:var(--gray-50);user-select:none}.dark .fi-avatar-initials{background-color:var(--gray-700)}.fi-avatar-initials>span{font-size:0.8125rem;font-weight:600;line-height:1;letter-spacing:-0.02em;white-space:nowrap}.fi-sidebar-nav a[href$="/admin/photos"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/videos"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/personal-offerings"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/staff-members"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/cells"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/users"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/membership-requests"]{margin-inline-start:0.875rem}.fi-sidebar-item-btn::after{content:var(--role-badge,none);display:inline-flex;align-items:center;border-radius:0.375rem;padding:0.0625rem 0.3125rem;font-size:0.625rem;line-height:1.5;font-weight:500;color:var(--role-badge-color);box-shadow:inset 0 0 0 0.0625rem currentColor;opacity:.7}.dark .fi-sidebar-item-btn::after{color:var(--role-badge-color-dark)}.fi-fo-file-upload .filepond--root{min-height:13rem}.fi-fo-file-upload .filepond--drop-label{min-height:13rem}.fi-fo-rich-editor-content{min-height:7.7rem}.fi-in-table-repeatable{overflow-x:auto}.fi-in-table-repeatable>table{display:table;width:max-content;min-width:100%}.fi-ta-header-cell.lg\:fi-visible{display:none}@media (min-width:64rem){.fi-ta-header-cell.lg\:fi-visible{display:table-cell}}.fi-one-time-code-input-ctn{width:100%;justify-content:center}.fi-one-time-code-input-ctn .fi-one-time-code-input-digit{flex:1 1 0;min-width:0;max-width:3.75rem;height:3.5rem;font-size:1.25rem;text-align:center}#content\.app .fi-sc-actions{gap:.5rem}#content\.app .fi-sc-actions-label-ctn{display:contents}#content\.app .fi-sc-actions-label{order:1}#content\.app .fi-sc-actions>.fi-sc{order:2;flex:0 0 auto}#content\.app .fi-sc-actions>.fi-sc .fi-sc-text{font-size:.8125rem;line-height:1.55;color:var(--gray-500)}.dark #content\.app .fi-sc-actions>.fi-sc .fi-sc-text{color:var(--gray-400)}#content\.app .fi-sc-actions-label-ctn>.fi-sc{order:3;flex:0 0 auto;width:100%;margin-block-start:.375rem}#content\.app .fi-sc-actions-label-ctn>.fi-sc>*,#content\.app .fi-sc-actions-label-ctn>.fi-sc .fi-sc-component{width:100%}#content\.app .fi-ac{order:4;gap:1.25rem;margin-block-start:.25rem}#content\.app .fi-badge{display:flex;width:100%;max-width:26rem;align-items:center;justify-content:flex-start;gap:.5rem;border-radius:.625rem;padding:.8125rem .9375rem;font-size:1rem;line-height:1.25;font-weight:600;box-shadow:none;background-color:color-mix(in srgb,var(--gray-500) 10%,transparent);color:var(--gray-700)}.dark #content\.app .fi-badge{background-color:color-mix(in srgb,var(--gray-400) 12%,transparent);color:var(--gray-300)}#content\.app .fi-badge .fi-badge-label-ctn{align-self:center}#content\.app .fi-badge::before{content:"";flex:none;width:.5rem;height:.5rem;border-radius:9999px;background-color:currentColor;opacity:.4}#content\.app .fi-badge.fi-color-success{background-color:color-mix(in srgb,var(--success-500) 12%,transparent);color:var(--success-700)}.dark #content\.app .fi-badge.fi-color-success{background-color:color-mix(in srgb,var(--success-400) 14%,transparent);color:var(--success-300)}#content\.app .fi-badge.fi-color-success::before{background-color:var(--success-500);opacity:1;box-shadow:0 0 0 .1875rem color-mix(in srgb,var(--success-500) 20%,transparent)}#content\.app .fi-ac .fi-link.fi-color-danger{margin-inline-start:auto;font-weight:400;color:var(--gray-500)}.dark #content\.app .fi-ac .fi-link.fi-color-danger{color:var(--gray-400)}#content\.app .fi-ac .fi-link.fi-color-danger>.fi-icon:not(.fi-loading-indicator){display:none}#content\.app .fi-ac .fi-link.fi-color-danger:hover{color:var(--danger-600)}.dark #content\.app .fi-ac .fi-link.fi-color-danger:hover{color:var(--danger-400)}@media (max-width:39.9375rem){.fi-header{flex-direction:row;flex-wrap:wrap;align-items:center;justify-content:space-between}.fi-header>div:first-child{min-width:0}.fi-header .fi-header-actions-ctn{margin-inline-start:auto;flex-wrap:wrap;justify-content:flex-end}.fi-ta-header{flex-direction:row;flex-wrap:wrap;align-items:center;justify-content:space-between}.fi-ta-header .fi-ta-actions{margin-inline-start:auto;justify-content:flex-end}.fi-ta-ctn .fi-ta-header-toolbar{row-gap:.75rem}.fi-ta-ctn .fi-ta-header-toolbar>*{min-height:0}.fi-ta-ctn .fi-ta-header-toolbar>.fi-ta-actions:not(:has(>:not([x-cloak]):not([style*="display: none"]))){display:none}.fi-ta-ctn .fi-ta-header-toolbar>:nth-child(2){flex:1 1 auto;min-width:0;margin-inline-start:0}.fi-ta-ctn .fi-ta-header-toolbar .fi-ta-search-field{flex:1 1 auto;min-width:0}.fi-ta-ctn .fi-ta-header-toolbar .fi-ta-search-field .fi-input-wrp{width:100%}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody{display:block;padding:.75rem .75rem 0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr:where(:not(.fi-ta-group-header-row):not(.fi-ta-summary-row):not(.fi-ta-row-not-reorderable)){border:1px solid color-mix(in srgb,currentColor 14%,transparent);border-radius:.75rem;background-color:color-mix(in srgb,currentColor 3%,transparent);padding-block:.9rem;margin-block-end:.75rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr.fi-selected{border-color:var(--primary-600)}.dark .fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr.fi-selected{border-color:var(--primary-500)}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr.fi-selected:before{content:none}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-selection-cell{top:1.05rem;inset-inline-end:1rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell){padding-block:.3rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):first-child{padding-inline-start:1rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):last-child{padding-inline-end:1rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-selection-cell+.fi-ta-cell{padding-inline-end:2.5rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell)>.fi-ta-cell-label{padding-top:0;font-size:.7rem;line-height:1.2;letter-spacing:.03em;text-transform:uppercase;opacity:.75}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell)>.fi-ta-cell-content{line-height:1.45;min-height:1.3rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):has(>.fi-ta-actions){margin-top:.35rem;border-top:1px solid color-mix(in srgb,currentColor 15%,transparent);padding-top:.6rem;padding-bottom:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):has(>.fi-ta-actions)>.fi-ta-actions{justify-content:flex-end}.fi-ta-cell-content [role="switch"]{margin-block-start:.25rem}.fi-ta-cell.stacked-hide-label>.fi-ta-cell-label{display:none}.fi-ta-cell.stacked-media img{width:100%!important;height:10rem!important;max-width:none;object-fit:cover;border-radius:.625rem}.fi-ta-cell.stacked-media .fi-ta-image-empty{display:none}}@media (min-width:24rem) and (max-width:39.9375rem){.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr:where(:not(.fi-ta-group-header-row):not(.fi-ta-summary-row):not(.fi-ta-row-not-reorderable)){display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:1.25rem;row-gap:.55rem;align-items:start;padding-inline:1rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell){padding-block:0;padding-inline:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):first-child{padding-inline-start:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):last-child{padding-inline-end:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-selection-cell+.fi-ta-cell{padding-inline-end:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-selection-cell+.fi-ta-cell+.fi-ta-cell{padding-inline-end:2.25rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell.stacked-span-full,.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):has(>.fi-ta-actions){grid-column:1/-1}}</style>'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString(static::googlePlacesScript()),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString(static::copyPhotoFilenameScript()),
                scopes: EditPhoto::class,
            )
            /** The light / dark / system trio, centred under the login card. */
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_END,
                fn (): View => view('filament.login-theme-switcher'),
                scopes: Login::class,
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
                RedirectMembersToProfile::class,
            ]);
    }

    /**
     * Build the sidebar navigation as themed groups. A group whose
     * items are all hidden from the current user disappears entirely.
     * 사진 sits directly below 앨범 and is indented via a small style
     * override registered in the AppServiceProvider render hook.
     *
     * The order here is the order on screen: this builder replaces
     * Filament's own sorting, so a resource's navigationSort has no
     * say. Groups run in the order the office works through them -
     * what is published each week, then the pictures that go with it,
     * then the offering, then the register, then the reference data
     * nobody touches twice a year, and finally the read-only views.
     *
     * 재정 is a group of its own rather than part of 콘텐츠 because the
     * offering is a department's work, not a publication, and the
     * finance_officer role sees this group and nothing else.
     */
    protected static function buildNavigation(NavigationBuilder $builder): NavigationBuilder
    {
        return $builder->groups([
            NavigationGroup::make()->items([
                ...static::accessibleItems(Dashboard::class),
                ...static::accessibleItems(SiteIntroduction::class),
            ]),
            NavigationGroup::make('콘텐츠')->items([
                ...static::accessibleItems(AnnouncementResource::class),
                ...static::accessibleItems(EventResource::class),
                ...static::accessibleItems(SermonResource::class),
                ...static::accessibleItems(BulletinResource::class),
                ...static::accessibleItems(DocumentResource::class),
            ]),
            NavigationGroup::make('미디어')->items([
                ...static::accessibleItems(AlbumResource::class),
                ...static::accessibleItems(PhotoResource::class),
                ...static::accessibleItems(Videos::class),
            ]),
            NavigationGroup::make('재정')->items([
                ...static::accessibleItems(OfferingResource::class),
                ...static::accessibleItems(PersonalOfferingResource::class),
            ]),
            NavigationGroup::make('공동체')->items([
                ...static::accessibleItems(MemberResource::class),
                ...static::accessibleItems(CellResource::class),
                ...static::accessibleItems(StaffMemberResource::class),
                ...static::accessibleItems(MembershipRequestResource::class),
                ...static::accessibleItems(UserResource::class),
            ]),
            NavigationGroup::make('기준 정보')->items([
                ...static::accessibleItems(SiteSettingResource::class),
                ...static::accessibleItems(ServiceTypeResource::class),
                ...static::accessibleItems(MinistryResource::class),
                ...static::accessibleItems(PositionResource::class),
            ]),
            NavigationGroup::make('모니터링')->items([
                ...static::accessibleItems(Analytics::class),
                ...static::accessibleItems(GoogleAnalytics::class),
                ...static::accessibleItems(ActivityResource::class),
                ...static::accessibleItems(DatabaseGraph::class),
            ]),
            NavigationGroup::make('Filament Shield')->items([
                ...static::accessibleItems(RoleResource::class),
            ]),
        ]);
    }

    /**
     * Tapping the photograph on its edit screen copies its filename.
     *
     * 사이트 설정 picks the home page hero by filename, and on a phone
     * that name cannot be selected out of a read-only field, so the
     * picture itself is made the control. The capture phase stops
     * FilePond opening its file browser on the same tap, while its own
     * action buttons - remove, and the like - are left alone.
     */
    protected static function copyPhotoFilenameScript(): string
    {
        return <<<'HTML'
            <script>
            document.addEventListener('click', (event) => {
                if (event.target.closest('.filepond--file-action-button')) {
                    return;
                }

                const preview = event.target.closest('.filepond--image-preview-wrapper, .filepond--image-preview, .filepond--file-info');

                if (! preview) {
                    return;
                }

                const field = preview.closest('form')?.querySelector('input[id$="filename"]');

                if (! field?.value) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const notify = (title, style) => new window.FilamentNotification().title(title)[style]().send();

                navigator.clipboard.writeText(field.value)
                    .then(() => notify('파일 이름을 복사했어요', 'success'))
                    .catch(() => notify('복사하지 못했어요. 아래 칸의 복사 버튼을 눌러주세요', 'danger'));
            }, true);
            </script>
            HTML;
    }

    /**
     * An inline script wiring Google Places address autocomplete onto
     * every input marked with data-google-places. Empty when no API
     * key is configured, so environments without one skip the
     * external request entirely.
     */
    protected static function googlePlacesScript(): string
    {
        $key = config('services.google_maps.key');

        if (blank($key)) {
            return '';
        }

        $src = 'https://maps.googleapis.com/maps/api/js?key='.rawurlencode($key).'&libraries=places&v=weekly&loading=async&callback=__juneunInitGooglePlaces';

        return <<<HTML
            <script>
            /**
             * Google Places (New) address autocomplete.
             *
             * The classic Autocomplete widget needs the legacy Places API,
             * which new Google Cloud projects can no longer enable, so this
             * uses AutocompleteSuggestion from Places API (New) and draws a
             * small dropdown under each input[data-google-places]. A chosen
             * suggestion is written back through native input/change events
             * so Livewire syncs the value. A MutationObserver rebinds after
             * Livewire morphs the DOM; a WeakSet guards double-binding
             * because it survives attribute morphs. Session tokens group a
             * typing session into one billable autocomplete request.
             */
            (function () {
                /**
                 * Filament navigates between pages with Livewire's SPA
                 * mode, which re-executes body scripts. Loading the Maps
                 * bootstrap twice makes Google skip the callback, so the
                 * whole setup runs once per document and later visits
                 * only rescan for new inputs.
                 */
                if (window.__juneunPlaces) {
                    window.__juneunPlaces.rescan();

                    return;
                }

                var boundInputs = new WeakSet();
                var debounceTimer = null;
                var sessionToken = null;
                var placesLibrary = null;

                /**
                 * The suggestion list lives directly on the body and is
                 * positioned over the input, rather than nested inside
                 * the field. Filament's form sections clip and stack
                 * their children, and Livewire re-morphs the field
                 * markup, both of which swallow a nested dropdown.
                 */
                function makeDropdown(input) {
                    var dark = document.documentElement.classList.contains('dark');
                    var list = document.createElement('ul');
                    list.style.cssText = 'position:fixed;z-index:2147483000;margin:0;padding:0.25rem;list-style:none;border-radius:0.5rem;box-shadow:0 0.5rem 1.5rem rgba(0,0,0,0.35);display:none;max-height:16rem;overflow-y:auto;'
                        + (dark
                            ? 'background:#1f2937;color:#f9fafb;border:1px solid rgba(255,255,255,0.14);'
                            : 'background:#ffffff;color:#111827;border:1px solid rgba(17,24,39,0.12);');
                    document.body.appendChild(list);

                    return list;
                }

                function place(input, list) {
                    var box = input.getBoundingClientRect();
                    list.style.insetInlineStart = box.left + 'px';
                    list.style.width = box.width + 'px';
                    list.style.top = (box.bottom + 4) + 'px';
                }

                function show(input, list) {
                    place(input, list);
                    list.style.display = 'block';
                }

                function notice(input, list, message, isError) {
                    list.textContent = '';
                    var item = document.createElement('li');
                    item.textContent = message;
                    item.style.cssText = 'padding:0.5rem 0.625rem;font-size:0.75rem;line-height:1.4;' + (isError ? 'color:#f87171' : 'opacity:0.6');
                    list.appendChild(item);
                    show(input, list);
                }

                function choose(input, list, text) {
                    input.value = text;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    list.style.display = 'none';
                    list.textContent = '';
                    sessionToken = null;
                }

                function render(input, list, suggestions) {
                    list.textContent = '';

                    suggestions.slice(0, 5).forEach(function (suggestion) {
                        var text = suggestion.placePrediction && suggestion.placePrediction.text
                            ? suggestion.placePrediction.text.text
                            : null;

                        if (!text) {
                            return;
                        }

                        var item = document.createElement('li');
                        item.textContent = text;
                        item.style.cssText = 'padding:0.625rem;border-radius:0.375rem;cursor:pointer;font-size:0.875rem;line-height:1.35';
                        item.addEventListener('pointerenter', function () {
                            item.style.background = 'rgba(128,138,160,0.18)';
                        });
                        item.addEventListener('pointerleave', function () {
                            item.style.background = 'transparent';
                        });
                        item.addEventListener('pointerdown', function (event) {
                            event.preventDefault();
                            choose(input, list, text);
                        });
                        list.appendChild(item);
                    });

                    if (list.childElementCount) {
                        show(input, list);
                    } else {
                        list.style.display = 'none';
                    }
                }

                function bind(input, places) {
                    var list = makeDropdown(input);

                    input.addEventListener('input', function () {
                        var value = input.value.trim();
                        window.clearTimeout(debounceTimer);

                        if (value.length < 3) {
                            list.style.display = 'none';
                            return;
                        }

                        debounceTimer = window.setTimeout(function () {
                            notice(input, list, '검색 중…', false);
                            sessionToken = sessionToken || new places.AutocompleteSessionToken();

                            places.AutocompleteSuggestion.fetchAutocompleteSuggestions({
                                input: value,
                                sessionToken: sessionToken,
                                includedRegionCodes: ['au'],
                                language: 'en-AU',
                            }).then(function (result) {
                                render(input, list, result.suggestions || []);
                            }).catch(function (error) {
                                notice(input, list, '자동완성 오류: ' + (error && error.message ? error.message : String(error)), true);
                            });
                        }, 250);
                    });

                    input.addEventListener('blur', function () {
                        window.setTimeout(function () {
                            list.style.display = 'none';
                        }, 150);
                    });

                    input.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            list.style.display = 'none';
                        }
                    });

                    /**
                     * A body-level dropdown does not travel with the
                     * field, so it follows scrolling while it is open.
                     */
                    window.addEventListener('scroll', function () {
                        if (list.style.display !== 'none') {
                            place(input, list);
                        }
                    }, true);

                    window.addEventListener('resize', function () {
                        if (list.style.display !== 'none') {
                            place(input, list);
                        }
                    });
                }

                function attach(places) {
                    document.querySelectorAll('input[data-google-places]').forEach(function (input) {
                        if (boundInputs.has(input)) {
                            return;
                        }

                        boundInputs.add(input);
                        bind(input, places);
                    });
                }

                window.__juneunPlaces = {
                    rescan: function () {
                        if (placesLibrary) {
                            attach(placesLibrary);
                        }
                    },
                };

                document.addEventListener('livewire:navigated', function () {
                    window.__juneunPlaces.rescan();
                });

                window.__juneunInitGooglePlaces = function () {
                    google.maps.importLibrary('places').then(function (places) {
                        placesLibrary = places;
                        attach(places);

                        new MutationObserver(function () {
                            attach(places);
                        }).observe(document.body, {
                            childList: true,
                            subtree: true,
                        });
                    }).catch(function (error) {
                        console.error('places library import failed', error);
                    });
                };

                var loader = document.createElement('script');
                loader.src = '{$src}';
                loader.async = true;
                loader.onerror = function () {
                    console.error('google maps loader blocked or unreachable');
                };
                document.head.appendChild(loader);
            })();
            </script>
            HTML;
    }

    /**
     * A resource's or page's navigation items, only when the current
     * user is authorised for it. Building the navigation by hand
     * skips Filament's own registration-time authorisation filter,
     * so it has to be applied explicitly here. Components that do not
     * exist (yet) are skipped so the sidebar degrades gracefully.
     *
     * A sidebar item has room for exactly one badge: NavigationItem
     * types it as string|Closure|null, getBadge() narrows it to ?string
     * (so an Htmlable is stringified before it ever reaches the view)
     * and item.blade.php prints it escaped inside a single
     * <x-filament::badge> whose colour is set on that one wrapper.
     * Claiming it for the role would therefore throw away whatever
     * getNavigationBadge() returned, which is what used to happen to
     * 가입 신청's pending count.
     *
     * So the resource keeps the badge - its live, colour-carrying
     * "needs attention" count - and the role tag is drawn after it as
     * a pseudo-element on the item, fed by the custom properties set
     * here. The count therefore sits to the left of the role tag, an
     * item with no count shows the role tag alone, and an item with no
     * role shows the count alone, because the pseudo-element resolves
     * to content:none without --role-badge.
     *
     * The tag itself is drawn in Korean, through the same RoleLabel map
     * the user table and the member form read: it names a role, and the
     * rest of the sidebar is Korean. Only the display text is
     * translated - roleBadge() keeps returning the internal key, so the
     * PAGE_BADGES map and the tests still compare against 'admin' and
     * 'developer'. The label is a CSS string, so it stays inside the
     * single quotes the custom property already carried; Korean needs
     * no further escaping in a UTF-8 document, and the labels are a
     * constant map holding no quote or semicolon that could close the
     * declaration early.
     *
     * @return array<NavigationItem>
     */
    public static function accessibleItems(string $component): array
    {
        if (! class_exists($component)) {
            return [];
        }

        if (method_exists($component, 'canAccess') && ! $component::canAccess()) {
            return [];
        }

        $items = $component::getNavigationItems();

        if ($badge = static::roleBadge($component)) {
            $hue = $badge === 'developer' ? 'success' : 'warning';
            $label = RoleLabel::label($badge);

            foreach ($items as $item) {
                $item->extraAttributes([
                    'style' => "--role-badge:'{$label}';--role-badge-color:var(--{$hue}-600);--role-badge-color-dark:var(--{$hue}-400)",
                ], merge: true);
            }
        }

        return $items;
    }

    /**
     * The role a resource is restricted to, shown as a badge beside its
     * sidebar label: 'developer' when nobody below a developer may view
     * it, 'admin' when only admins and above may, and null when any
     * other role reaches it too.
     *
     * The answer comes from the granted ViewAny permissions rather than
     * a hardcoded list, so it follows RolePermissionSeeder, minus the
     * roles in NON_WIDENING_ROLES. Resources whose permission was never
     * generated (the activity log, whose policy checks the developer
     * role directly) fall through to the developer badge, which is what
     * an empty grant set means.
     *
     * The returned value is the internal role key; the sidebar shows its
     * Korean label. Callers compare against the key.
     */
    public static function roleBadge(string $component): ?string
    {
        if (array_key_exists($component, static::PAGE_BADGES)) {
            return static::PAGE_BADGES[$component];
        }

        if (! method_exists($component, 'getModel')) {
            return null;
        }

        $viewers = array_diff(
            static::viewAnyGrants()[class_basename($component::getModel())] ?? [],
            static::NON_WIDENING_ROLES,
        );

        return match (true) {
            empty(array_diff($viewers, ['developer'])) => 'developer',
            empty(array_diff($viewers, ['developer', 'admin'])) => 'admin',
            default => null,
        };
    }

    /**
     * Role names holding each model's ViewAny permission, keyed by
     * model name. Resolved with one query and memoised for the rest of
     * the request, since the sidebar asks for every item in turn.
     *
     * @return array<string, list<string>>
     */
    protected static function viewAnyGrants(): array
    {
        return once(fn (): array => DB::table('permissions')
            ->join('role_has_permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('permissions.name', 'like', 'ViewAny:%')
            ->get(['permissions.name as permission', 'roles.name as role'])
            ->groupBy(fn (object $grant): string => str($grant->permission)->after('ViewAny:')->value())
            ->map(fn ($grants): array => $grants->pluck('role')->all())
            ->all());
    }
}
