<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Analytics;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Members;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Albums\AlbumResource;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Bulletins\BulletinResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Photos\PhotoResource;
use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Sermons\SermonResource;
use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Filament\Resources\Users\UserResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->subNavigationPosition(SubNavigationPosition::Top)
            ->navigation(fn (NavigationBuilder $builder): NavigationBuilder => static::buildNavigation($builder))
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('<style>.fi-sidebar-nav a[href$="/admin/photos"]{margin-inline-start:1.75rem}.fi-fo-file-upload .filepond--root{min-height:13rem}.fi-fo-file-upload .filepond--drop-label{min-height:13rem}</style>'),
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
                FilamentInfoWidget::class,
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
                ...Dashboard::getNavigationItems(),
                ...SiteSettingResource::getNavigationItems(),
            ]),
            NavigationGroup::make('콘텐츠')->items([
                ...AnnouncementResource::getNavigationItems(),
                ...EventResource::getNavigationItems(),
                ...SermonResource::getNavigationItems(),
                ...ServiceTypeResource::getNavigationItems(),
            ]),
            NavigationGroup::make('미디어')->items([
                ...AlbumResource::getNavigationItems(),
                ...PhotoResource::getNavigationItems(),
                ...BulletinResource::getNavigationItems(),
            ]),
            NavigationGroup::make('구성원')->items([
                ...PositionResource::getNavigationItems(),
                ...StaffMemberResource::getNavigationItems(),
                ...Members::getNavigationItems(),
                ...UserResource::getNavigationItems(),
            ]),
            NavigationGroup::make('모니터링')->items([
                ...Analytics::getNavigationItems(),
                ...ActivityResource::getNavigationItems(),
            ]),
            NavigationGroup::make('Filament Shield')->items([
                ...RoleResource::getNavigationItems(),
            ]),
        ]);
    }
}
