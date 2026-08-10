<?php

namespace App\Providers;

use App\Filament\Analytics\TrafficChartWidget;
use App\Filament\Analytics\TrafficStatsWidget;
use App\Filament\Support\SaveUploadsAsWebp;
use App\Models\User;
use App\Policies\ActivityPolicy;
use Filament\Actions\CreateAction;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Table;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Support\CauserResolver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * Send the panel's password reset mail after the response has
         * been flushed, rather than on the request thread.
         *
         * Filament resolves this notification from the container, so
         * rebinding it is the only place its queue connection can be
         * pinned. It already implements ShouldQueue, but the server
         * runs no queue worker: on the default 'database' connection
         * the job would sit in the table for ever and the mail would
         * never arrive, and on 'sync' the request would block on SMTP
         * for as long as the mail host takes - which is what turned
         * the public reset form into a membership oracle, because only
         * an address that exists pays that wait.
         *
         * The 'deferred' connection runs the send in this same process
         * once the response is on its way, so no worker is needed and
         * the two answers cost the same.
         */
        $this->app->bind(
            ResetPasswordNotification::class,
            fn ($app, array $parameters): ResetPasswordNotification => (new ResetPasswordNotification($parameters['token']))
                ->onConnection('deferred'),
        );
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
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->defaultDateDisplayFormat('Y-m-d')
            ->defaultDateTimeDisplayFormat('Y-m-d, H:i:s')
            ->defaultTimeDisplayFormat('H:i'));

        /**
         * Calendar weeks start on Sunday (7, per the vendor's own
         * weekStartsOnSunday helper). Registered on DateTimePicker so
         * the configuration inherits to DatePicker as well.
         */
        DateTimePicker::configureUsing(fn (DateTimePicker $picker) => $picker->firstDayOfWeek(7));

        SaveUploadsAsWebp::register();
        CreateAction::configureUsing(fn (CreateAction $action) => $action->createAnother(false));

        $this->registerAnalyticsWidgets();
        $this->anonymiseExemptCausers();
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
     * Strip the causer from anything an exempt account does.
     *
     * The rows themselves are still written - what changed on the site
     * is worth keeping whoever made the change - but they carry no
     * name, so the log shows them as 시스템 rather than as a person.
     *
     * This covers every write, both the ones that name their causer and
     * the model events that let the package find it, so an exempt
     * account cannot be attributed by some path that was overlooked.
     */
    private function anonymiseExemptCausers(): void
    {
        app(CauserResolver::class)->resolveUsing(function (Model|int|string|null $subject): ?Model {
            $causer = $subject instanceof Model ? $subject : Auth::user();

            return $causer instanceof User && $causer->is_audit_exempt ? null : $causer;
        });
    }

    /**
     * Record sign-in, sign-out and failed attempts in the activity log.
     */
    private function logAuthenticationActivity(): void
    {
        Event::listen(function (Login $event): void {
            if ($event->user instanceof User && $event->user->is_audit_exempt) {
                return;
            }

            activity('auth')
                ->causedBy($event->user)
                ->event('login')
                ->withProperties(['ip' => request()->ip()])
                ->log('로그인');
        });

        Event::listen(function (Logout $event): void {
            if ($event->user && ! ($event->user instanceof User && $event->user->is_audit_exempt)) {
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
