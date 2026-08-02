<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Analytics;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Albums\AlbumResource;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Bulletins\BulletinResource;
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
                fn (): HtmlString => new HtmlString('<style>.fi-sidebar-nav a[href$="/admin/photos"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/staff-members"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/users"]{margin-inline-start:0.875rem}.fi-sidebar-nav a[href$="/admin/ministries"]{margin-inline-start:1.75rem}.fi-sidebar-nav a[href$="/admin/positions"]{margin-inline-start:2.625rem}.fi-fo-file-upload .filepond--root{min-height:13rem}.fi-fo-file-upload .filepond--drop-label{min-height:13rem}.fi-fo-rich-editor-content{min-height:7.7rem}.fi-one-time-code-input-ctn{width:100%;justify-content:center}.fi-one-time-code-input-ctn .fi-one-time-code-input-digit{flex:1 1 0;min-width:0;max-width:3.75rem;height:3.5rem;font-size:1.25rem;text-align:center}@media (min-width:24rem) and (max-width:39.9375rem){.fi-ta-table-stacked-on-mobile>tbody>tr:not(.fi-ta-group-header-row):not(.fi-ta-summary-row){display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:1rem;align-items:start}.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell.stacked-span-full,.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:has(>.fi-ta-actions),.fi-ta-table-stacked-on-mobile>tbody>tr>.fi-ta-cell:has(.fi-ta-record-checkbox){grid-column:1/-1}}</style>'),
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
                ...static::accessibleItems(SiteSettingResource::class),
                ...static::accessibleItems(AnnouncementResource::class),
                ...static::accessibleItems(EventResource::class),
                ...static::accessibleItems(SermonResource::class),
                ...static::accessibleItems(ServiceTypeResource::class),
                ...static::accessibleItems(OfferingResource::class),
            ]),
            NavigationGroup::make('미디어')->items([
                ...static::accessibleItems(AlbumResource::class),
                ...static::accessibleItems(PhotoResource::class),
                ...static::accessibleItems(BulletinResource::class),
            ]),
            NavigationGroup::make('공동체')->items([
                ...static::accessibleItems(MemberResource::class),
                ...static::accessibleItems(UserResource::class),
                ...static::accessibleItems(StaffMemberResource::class),
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
     * A resource's or page's navigation items, only when the current
     * user is authorised for it. Building the navigation by hand
     * skips Filament's own registration-time authorisation filter,
     * so it has to be applied explicitly here.
     *
     * @return array<NavigationItem>
     */
    protected static function accessibleItems(string $component): array
    {
        if (method_exists($component, 'canAccess') && ! $component::canAccess()) {
            return [];
        }

        return $component::getNavigationItems();
    }
}
