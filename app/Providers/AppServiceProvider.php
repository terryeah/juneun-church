<?php

namespace App\Providers;

use App\Filament\Analytics\TrafficChartWidget;
use App\Filament\Analytics\TrafficStatsWidget;
use App\Filament\Support\SaveUploadsAsWebp;
use App\Policies\ActivityPolicy;
use Filament\Tables\Table;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityPolicy::class);
        Paginator::defaultView('pagination.juneun');

        Table::configureUsing(fn (Table $table) => $table
            ->stackedOnMobile()
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->defaultDateDisplayFormat('Y-m-d')
            ->defaultDateTimeDisplayFormat('Y-m-d, H:i:s')
            ->defaultTimeDisplayFormat('H:i'));
        SaveUploadsAsWebp::register();

        $this->registerAnalyticsWidgets();
        $this->logAuthenticationActivity();
    }

    /**
     * Register the analytics widgets as Livewire components.
     *
     * They live outside Filament's widget discovery path on purpose so
     * they never appear on the dashboard, which means Livewire needs an
     * explicit registration to resolve their update requests.
     */
    private function registerAnalyticsWidgets(): void
    {
        Livewire::component('app.filament.analytics.traffic-stats-widget', TrafficStatsWidget::class);
        Livewire::component('app.filament.analytics.traffic-chart-widget', TrafficChartWidget::class);
    }

    /**
     * Record sign-in, sign-out and failed attempts in the activity log.
     */
    private function logAuthenticationActivity(): void
    {
        Event::listen(function (Login $event): void {
            activity('auth')
                ->causedBy($event->user)
                ->event('login')
                ->withProperties(['ip' => request()->ip()])
                ->log('로그인');
        });

        Event::listen(function (Logout $event): void {
            if ($event->user) {
                activity('auth')
                    ->causedBy($event->user)
                    ->event('logout')
                    ->withProperties(['ip' => request()->ip()])
                    ->log('로그아웃');
            }
        });

        Event::listen(function (Failed $event): void {
            activity('auth')
                ->event('failed_login')
                ->withProperties(['email' => $event->credentials['email'] ?? null, 'ip' => request()->ip()])
                ->log('로그인 실패');
        });
    }
}
