<?php

namespace App\Providers;

use App\Policies\ActivityPolicy;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
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

        $this->logAuthenticationActivity();
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
                ->log('로그인');
        });

        Event::listen(function (Logout $event): void {
            if ($event->user) {
                activity('auth')
                    ->causedBy($event->user)
                    ->event('logout')
                    ->log('로그아웃');
            }
        });

        Event::listen(function (Failed $event): void {
            activity('auth')
                ->event('failed_login')
                ->withProperties(['email' => $event->credentials['email'] ?? null])
                ->log('로그인 실패');
        });
    }
}
