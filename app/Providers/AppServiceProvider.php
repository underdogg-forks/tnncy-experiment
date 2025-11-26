<?php

namespace App\Providers;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Generators\Database\DefaultPasswordGenerator;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Repositories\WebsiteRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PasswordGenerator::class,
            DefaultPasswordGenerator::class
        );
        $this->app->bind(
            \Hyn\Tenancy\Contracts\Repositories\WebsiteRepository::class,
            WebsiteRepository::class
        );
        $this->app->bind(
            \Hyn\Tenancy\Contracts\Website::class,
            Website::class
        );
        $this->app->bind(
            \Hyn\Tenancy\Contracts\Hostname::class,
            Hostname::class
        );
        $this->app->bind(
            \Hyn\Tenancy\Contracts\Repositories\HostnameRepository::class,
            \Hyn\Tenancy\Repositories\HostnameRepository::class
        );
        $this->app->bind(
            \Hyn\Tenancy\Validators\HostnameValidator::class,
            \Hyn\Tenancy\Validators\HostnameValidator::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
