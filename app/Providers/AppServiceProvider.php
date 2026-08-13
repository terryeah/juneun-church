<?php

namespace App\Providers;

use App\Filament\Analytics\TrafficChartWidget;
use App\Filament\Analytics\TrafficStatsWidget;
use App\Filament\Support\SaveUploadsAsWebp;
use App\Models\User;
use App\Policies\ActivityPolicy;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Enums\FiltersLayout;
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
     * How a date is written everywhere in the admin panel.
     */
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * How a date and a time are written together everywhere in the admin
     * panel. The comma is what separates them: a bare space reads as one
     * run of digits at a glance, which is the whole problem with
     * '2026-08-13 09:41'.
     *
     * Seconds are left out. Nothing the office does is ordered by them,
     * and they are three characters of noise on every 게시 일시 and
     * 신청일 in the panel. The activity log appends ':s' for itself,
     * being the one place where two rows can share a minute and the
     * order of them is the point.
     */
    public const DATE_TIME_FORMAT = 'Y-m-d, H:i';

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

        Table::configureUsing(fn (Table $table) => self::configureFilters($table)
            ->stackedOnMobile()
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->defaultDateDisplayFormat(self::DATE_FORMAT)
            ->defaultDateTimeDisplayFormat(self::DATE_TIME_FORMAT)
            ->defaultTimeDisplayFormat('H:i'));

        /**
         * The same formats for everything that is not a table.
         *
         * Only Table carried them, and an infolist entry reads its
         * default off the Schema it sits in - so every 일시 outside a
         * table, the activity log's own view modal included, fell back
         * to Filament's American 'M j, Y H:i:s'. Schema is the one place
         * that covers infolists and every modal built out of them.
         *
         * Date pickers are unaffected: DateTimePicker keeps its own copy
         * of these settings rather than reading the container's.
         */
        Schema::configureUsing(fn (Schema $schema) => $schema
            ->defaultDateDisplayFormat(self::DATE_FORMAT)
            ->defaultDateTimeDisplayFormat(self::DATE_TIME_FORMAT)
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
     * How every table in the panel asks for its filters.
     *
     * A dropdown anchored to a 36px icon is a 320px card floating over
     * the rows on a phone, with no backdrop and the apply button in the
     * far corner. The same filters as a modal are a full-width sheet on
     * a phone and an ordinary dialog on a laptop, which is the shape
     * every other action in this panel already opens in - so the office
     * learns one gesture. The breakpoint difference is drawn in CSS,
     * because the layout is chosen here, once, on the server.
     *
     * Tables with no filters are untouched: Filament draws no trigger
     * when filters() is empty, so none of this reaches them.
     */
    private static function configureFilters(Table $table): Table
    {
        return $table
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormColumns(['sm' => 2])
            ->filtersFormWidth(Width::Large)
            ->filtersApplyAction(fn (Action $action): Action => $action->label('결과 보기'))
            /**
             * An emptied list explains itself. Filament's own heading
             * tells the reader to create the first record, which is the
             * wrong advice when what emptied the list was a filter.
             *
             * The heading falls back to the vendor's when nothing is
             * filtered; the description does not, because
             * getEmptyStateDescription() has no ?? fallback and returns
             * whatever this closure gives it. Dropping it is deliberate:
             * the sentence it removes is '사진 을(를) 만들어 시작하세요',
             * whose 을(를) is a particle the vendor could not resolve and
             * which reads as a bug to the office. The heading alone says
             * enough.
             */
            ->emptyStateHeading(fn (): ?string => $table->isFiltered() ? '조건에 맞는 결과가 없어요' : null)
            ->emptyStateDescription(fn (): ?string => $table->isFiltered() ? '필터를 바꾸거나 모두 지워 보세요.' : null)
            /**
             * The count badge is drawn even when it reads 0 - the view
             * sets it after this closure runs - and a funnel wearing a
             * '0' reads as "no results" long before it reads as "no
             * filters". So the badge is hidden in CSS and the button
             * carries the state instead: grey when nothing is filtered,
             * the panel's own colour when something is. The chips under
             * the toolbar say which, which matters on 활동 기록, where a
             * default filter is already on when the page paints.
             *
             * 닫기 is dropped: the header keeps its close button, and
             * 결과 보기 applies and closes in the one tap.
             */
            ->filtersTriggerAction(fn (Action $action): Action => $action
                ->button()
                /**
                 * Hiding the count takes it out of the accessibility
                 * tree with it, which would leave the fill colour as the
                 * only thing saying a filter is on. The label carries it
                 * instead, and keeps the visible '필터' inside itself so
                 * the two still match for anyone speaking the button.
                 */
                ->extraAttributes([
                    'class' => 'fi-ta-filters-trigger',
                    'aria-label' => $action->getTable()->isFiltered() ? '필터 (적용 중)' : '필터',
                ], merge: true)
                ->color($action->getTable()->isFiltered() ? 'primary' : 'gray')
                ->modalCancelAction(false)
                ->stickyModalFooter()
                ->extraModalFooterActions([
                    $action->getTable()->getFiltersApplyAction()->close(),
                    /**
                     * removeTableFilters, not resetTableFiltersForm.
                     * The reset one calls fill(), which restores each
                     * filter's default rather than clearing it - so on
                     * 활동 기록, where 시스템 기록 defaults to off, a
                     * button reading 모두 지우기 put back the filter the
                     * reader had just removed, and did nothing at all
                     * from a freshly loaded page.
                     */
                    Action::make('removeFilters')
                        ->label('모두 지우기')
                        ->color('gray')
                        ->table($action->getTable())
                        ->action('removeTableFilters')
                        ->button(),
                ]));
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
            /**
             * Stamped for everyone, exempt or not: this is not a record
             * of what somebody did but of whether the account is still
             * in use, and it is what the account list is reviewed on.
             */
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }

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
