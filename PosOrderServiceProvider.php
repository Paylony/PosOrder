<?php

namespace YourVendor\PosOrder;

use Illuminate\Support\ServiceProvider;

class PosOrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/pos-order.php',
            'pos-order'
        );

        $this->app->singleton('pos-order', function ($app) {
            return new PosOrderService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/pos-order.php' => config_path('pos-order.php'),
            ], 'pos-order-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'pos-order-migrations');
        }
    }
}