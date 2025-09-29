<?php

namespace Vendor\UserDiscounts;

use Illuminate\Support\ServiceProvider;
use Vendor\UserDiscounts\Contracts\DiscountManagerInterface;
use Vendor\UserDiscounts\Managers\DiscountManager;

class DiscountsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/discounts.php',
            'discounts'
        );
        $this->app->singleton(DiscountManagerInterface::class, function ($app) {
            return new DiscountManager($app['config']['discounts']);
        });
    }
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/discounts.php' =>
                config_path('discounts.php'),
            ], 'config');
            $this->publishes([
                __DIR__ . '/database/migrations/' => database_path('migrations')
            ], 'migrations');
        }
        // load migrations if used in package tests
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
