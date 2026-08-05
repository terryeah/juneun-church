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
                var trace = [];
                var newline = String.fromCharCode(10);

                /**
                 * Temporary on-page diagnostic. Mobile browsers give no
                 * easy console access, so each stage is reported in a
                 * fixed corner panel until autocomplete is confirmed
                 * working, then this helper goes away.
                 */
                function stage(message) {
                    trace.push(message);

                    var panel = document.getElementById('juneun-places-trace');

                    if (!panel) {
                        panel = document.createElement('div');
                        panel.id = 'juneun-places-trace';
                        panel.style.cssText = 'position:fixed;inset-block-end:0.5rem;inset-inline-start:0.5rem;z-index:9999;max-width:22rem;padding:0.5rem 0.625rem;border-radius:0.5rem;background:rgba(17,24,39,0.92);color:#f9fafb;font:0.6875rem/1.4 monospace;white-space:pre-wrap;pointer-events:none';
                        document.body.appendChild(panel);
                    }

                    panel.textContent = 'PLACES 진단' + newline + trace.join(newline);
                }

                window.addEventListener('error', function (event) {
                    stage('window error: ' + (event.message || 'unknown'));
                });

                stage('1. 스크립트 실행됨');

                function makeDropdown(input) {
                    var list = document.createElement('ul');
                    list.style.cssText = 'position:absolute;inset-inline:0;top:calc(100% + 0.25rem);z-index:50;margin:0;padding:0.25rem;list-style:none;background:Canvas;color:CanvasText;border:1px solid color-mix(in srgb, currentColor 20%, transparent);border-radius:0.5rem;box-shadow:0 0.5rem 1.5rem rgba(0,0,0,0.25);display:none;max-height:16rem;overflow-y:auto';
                    list.style.colorScheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                    var wrapper = input.closest('.fi-input-wrp') || input.parentElement;
                    wrapper.style.position = 'relative';
                    wrapper.appendChild(list);
                    return list;
                }

                function notice(list, message, isError) {
                    list.textContent = '';
                    var item = document.createElement('li');
                    item.textContent = message;
                    item.style.cssText = 'padding:0.5rem 0.625rem;font-size:0.75rem;line-height:1.4;' + (isError ? 'color:#ef4444' : 'opacity:0.6');
                    list.appendChild(item);
                    list.style.display = 'block';
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
                        item.style.cssText = 'padding:0.5rem 0.625rem;border-radius:0.375rem;cursor:pointer;font-size:0.875rem;line-height:1.35';
                        item.addEventListener('mouseenter', function () {
                            item.style.background = 'color-mix(in srgb, currentColor 8%, transparent)';
                        });
                        item.addEventListener('mouseleave', function () {
                            item.style.background = 'transparent';
                        });
                        item.addEventListener('pointerdown', function (event) {
                            event.preventDefault();
                            choose(input, list, text);
                        });
                        list.appendChild(item);
                    });

                    list.style.display = list.childElementCount ? 'block' : 'none';
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
                            stage('6. 검색 요청: ' + value);
                            notice(list, '검색 중…', false);
                            sessionToken = sessionToken || new places.AutocompleteSessionToken();

                            places.AutocompleteSuggestion.fetchAutocompleteSuggestions({
                                input: value,
                                sessionToken: sessionToken,
                                includedRegionCodes: ['au'],
                                language: 'en-AU',
                            }).then(function (result) {
                                stage('7. 응답 수신: ' + ((result.suggestions || []).length) + '건');
                                render(input, list, result.suggestions || []);
                            }).catch(function (error) {
                                stage('7. 요청 실패: ' + (error && error.message ? error.message : String(error)));
                                notice(list, '자동완성 오류: ' + (error && error.message ? error.message : String(error)), true);
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
                }

                function attach(places) {
                    var found = document.querySelectorAll('input[data-google-places]');
                    var bound = 0;

                    found.forEach(function (input) {
                        if (boundInputs.has(input)) {
                            return;
                        }

                        boundInputs.add(input);
                        bind(input, places);
                        bound = bound + 1;
                    });

                    if (bound > 0) {
                        stage('5. 주소 필드 ' + bound + '개 연결됨 (총 ' + found.length + ')');
                    }
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
                    stage('3. 구글 로더 콜백 도착');

                    google.maps.importLibrary('places').then(function (places) {
                        placesLibrary = places;
                        stage('4. places 라이브러리 로드됨, AutocompleteSuggestion=' + (places && places.AutocompleteSuggestion ? 'O' : 'X'));
                        attach(places);

                        new MutationObserver(function () {
                            attach(places);
                        }).observe(document.body, {
                            childList: true,
                            subtree: true,
                        });
                    }).catch(function (error) {
                        stage('4. 라이브러리 로드 실패: ' + (error && error.message ? error.message : String(error)));
                    });
                };

                var loader = document.createElement('script');
                loader.src = '{$src}';
                loader.async = true;
                loader.onload = function () {
                    stage('2. 로더 스크립트 다운로드 완료');
                };
                loader.onerror = function () {
                    stage('2. 로더 차단됨 (네트워크/확장프로그램)');
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
