<?php

namespace App\Providers;

use App\Services\TimelineService;
use App\Services\TimelineServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->instance(TimelineServiceInterface::class, $this->app->make(TimelineService::class));
    }
}
