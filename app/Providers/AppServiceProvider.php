<?php

namespace App\Providers;

use App\Models\Calon;
use App\Models\Desa;
use App\Models\TPS;
use App\Models\User;
use App\Observers\CalonObserver;
use App\Observers\DesaObserver;
use App\Observers\TPSObserver;
use App\Policies\CalonPolicy;
use App\Policies\DesaPolicy;
use App\Policies\TPSPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Desa::class, DesaPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Calon::class, CalonPolicy::class);
        Gate::policy(TPS::class, TPSPolicy::class);

        Gate::define('upload-photo', function (User $user) {
            return $user->isAktif();
        });

        Desa::observe(DesaObserver::class);
        Calon::observe(CalonObserver::class);
        TPS::observe(TPSObserver::class);
    }
}
