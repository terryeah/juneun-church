<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Analytics;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DepartmentOverview;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Albums\AlbumResource;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Bulletins\BulletinResource;
use App\Filament\Resources\Cells\CellResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Ministries\MinistryResource;
use App\Filament\Resources\Offerings\OfferingResource;
use App\Filament\Resources\Photos\PhotoResource;
use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Sermons\SermonResource;
use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Filament\Resources\Users\UserResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Auth\MultiFactor\App\AppAuthentication;
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
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ], isRequired: fn (): bool => ! app()->runningUnitTests())
            ->favicon(asset('favicon.svg'))
            ->subNavigationPosition(SubNavigationPosition::Top)
            ->navigation(fn (NavigationBuilder $builder): NavigationBuilder => static::buildNavigation($builder))
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('<style>.fi-sidebar-nav a[href$="/admin/photos"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/staff-members"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/cells"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/department-overview"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/users"]{margin-inline-start:0.875rem}.fi-fo-file-upload .filepond--root{min-height:13rem}.fi-fo-file-upload .filepond--drop-label{min-height:13rem}.fi-fo-rich-editor-content{min-height:7.7rem}.fi-one-time-code-input-ctn{width:100%;justify-content:center}.fi-one-time-code-input-ctn .fi-one-time-code-input-digit{flex:1 1 0;min-width:0;max-width:3.75rem;height:3.5rem;font-size:1.25rem;text-align:center}@media (max-width:39.9375rem){.fi-header{flex-direction:row;flex-wrap:wrap;align-items:center;justify-content:space-between}.fi-header>div:first-child{min-width:0}.fi-header .fi-header-actions-ctn{margin-inline-start:auto;flex-wrap:wrap;justify-content:flex-end}.fi-ta-header{flex-direction:row;flex-wrap:wrap;align-items:center;justify-content:space-between}.fi-ta-header .fi-ta-actions{margin-inline-start:auto;justify-content:flex-end}.fi-ta-ctn .fi-ta-header-toolbar{row-gap:.75rem}.fi-ta-ctn .fi-ta-header-toolbar>*{min-height:0}.fi-ta-ctn .fi-ta-header-toolbar>.fi-ta-actions:not(:has(>:not([x-cloak]):not([style*="display: none"]))){display:none}.fi-ta-ctn .fi-ta-header-toolbar>:nth-child(2){flex:1 1 auto;min-width:0;margin-inline-start:0}.fi-ta-ctn .fi-ta-header-toolbar .fi-ta-search-field{flex:1 1 auto;min-width:0}.fi-ta-ctn .fi-ta-header-toolbar .fi-ta-search-field .fi-input-wrp{width:100%}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody{display:block;padding:.75rem .75rem 0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr:where(:not(.fi-ta-group-header-row):not(.fi-ta-summary-row):not(.fi-ta-row-not-reorderable)){border:1px solid color-mix(in srgb,currentColor 14%,transparent);border-radius:.75rem;background-color:color-mix(in srgb,currentColor 3%,transparent);padding-block:.9rem;margin-block-end:.75rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr.fi-selected{border-color:var(--primary-600)}.dark .fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr.fi-selected{border-color:var(--primary-500)}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr.fi-selected:before{content:none}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-selection-cell{top:1.05rem;inset-inline-end:1rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell){padding-block:.3rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):first-child{padding-inline-start:1rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):last-child{padding-inline-end:1rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-selection-cell+.fi-ta-cell{padding-inline-end:2.5rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell)>.fi-ta-cell-label{padding-top:0;font-size:.7rem;line-height:1.2;letter-spacing:.03em;text-transform:uppercase;opacity:.75}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell)>.fi-ta-cell-content{line-height:1.45;min-height:1.3rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):has(>.fi-ta-actions){margin-top:.35rem;border-top:1px solid color-mix(in srgb,currentColor 15%,transparent);padding-top:.6rem;padding-bottom:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):has(>.fi-ta-actions)>.fi-ta-actions{justify-content:flex-end}}@media (min-width:24rem) and (max-width:39.9375rem){.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr:where(:not(.fi-ta-group-header-row):not(.fi-ta-summary-row):not(.fi-ta-row-not-reorderable)){display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:1.25rem;row-gap:.55rem;align-items:start;padding-inline:1rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell){padding-block:0;padding-inline:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):first-child{padding-inline-start:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):last-child{padding-inline-end:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-selection-cell+.fi-ta-cell{padding-inline-end:0}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-selection-cell+.fi-ta-cell+.fi-ta-cell{padding-inline-end:2.25rem}.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell.stacked-span-full,.fi-ta-table.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:not(.fi-ta-selection-cell):has(>.fi-ta-actions){grid-column:1/-1}}</style>'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString(static::googlePlacesScript()),
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
            ->widgets([
                AccountWidget::class,
            ])
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
            ]);
    }

    /**
     * Build the sidebar navigation as themed groups. A group whose
     * items are all hidden from the current user disappears entirely.
     * 사진 sits directly below 앨범 and is indented via a small style
     * override registered in the AppServiceProvider render hook.
     */
    protected static function buildNavigation(NavigationBuilder $builder): NavigationBuilder
    {
        return $builder->groups([
            NavigationGroup::make()->items([
                ...static::accessibleItems(Dashboard::class),
            ]),
            NavigationGroup::make('콘텐츠')->items([
                ...static::accessibleItems(AnnouncementResource::class),
                ...static::accessibleItems(EventResource::class),
                ...static::accessibleItems(SermonResource::class),
                ...static::accessibleItems(OfferingResource::class),
            ]),
            NavigationGroup::make('미디어')->items([
                ...static::accessibleItems(AlbumResource::class),
                ...static::accessibleItems(PhotoResource::class),
                ...static::accessibleItems(BulletinResource::class),
            ]),
            NavigationGroup::make('공동체')->items([
                ...static::accessibleItems(MemberResource::class),
                ...static::accessibleItems(CellResource::class),
                ...static::accessibleItems(DepartmentOverview::class),
                ...static::accessibleItems(UserResource::class),
                ...static::accessibleItems(StaffMemberResource::class),
            ]),
            NavigationGroup::make('기준 정보')->items([
                ...static::accessibleItems(SiteSettingResource::class),
                ...static::accessibleItems(ServiceTypeResource::class),
                ...static::accessibleItems(MinistryResource::class),
                ...static::accessibleItems(PositionResource::class),
            ]),
            NavigationGroup::make('모니터링')->items([
                ...static::accessibleItems(Analytics::class),
                ...static::accessibleItems(ActivityResource::class),
            ]),
            NavigationGroup::make('Filament Shield')->items([
                ...static::accessibleItems(RoleResource::class),
            ]),
        ]);
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

        $src = 'https://maps.googleapis.com/maps/api/js?key='.rawurlencode($key).'&libraries=places&loading=async&callback=__juneunInitGooglePlaces';

        return <<<HTML
            <script>
            /**
             * Google Places address autocomplete.
             *
             * Binds a classic Autocomplete (restricted to Australian street
             * addresses) to each input[data-google-places], writing the
             * formatted address back through native input/change events so
             * Livewire syncs the value. A MutationObserver rebinds after
             * Livewire morphs the DOM; a dataset flag backed by a WeakSet
             * (which survives attribute morphs) guards double-binding.
             */
            (function () {
                var boundInputs = new WeakSet();

                function attach() {
                    if (!window.google || !google.maps || !google.maps.places) {
                        return;
                    }

                    document.querySelectorAll('input[data-google-places]').forEach(function (input) {
                        if (boundInputs.has(input) || input.dataset.googlePlacesBound) {
                            return;
                        }

                        boundInputs.add(input);
                        input.dataset.googlePlacesBound = 'true';

                        var autocomplete = new google.maps.places.Autocomplete(input, {
                            componentRestrictions: { country: 'au' },
                            fields: ['formatted_address'],
                            types: ['address'],
                        });

                        autocomplete.addListener('place_changed', function () {
                            var place = autocomplete.getPlace();

                            if (!place || !place.formatted_address) {
                                return;
                            }

                            input.value = place.formatted_address;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    });
                }

                window.__juneunInitGooglePlaces = function () {
                    attach();

                    new MutationObserver(attach).observe(document.body, {
                        childList: true,
                        subtree: true,
                    });
                };

                var loader = document.createElement('script');
                loader.src = '{$src}';
                loader.async = true;
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
     * @return array<NavigationItem>
     */
    protected static function accessibleItems(string $component): array
    {
        if (! class_exists($component)) {
            return [];
        }

        if (method_exists($component, 'canAccess') && ! $component::canAccess()) {
            return [];
        }

        return $component::getNavigationItems();
    }
}
