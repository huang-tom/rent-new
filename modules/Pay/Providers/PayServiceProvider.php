<?php

namespace Modules\Pay\Providers;

use Illuminate\Support\ServiceProvider;

class PayServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(PayRepositoryServiceProvider::class);

        $this->app->router->group([
            'namespace' => 'Modules\Pay\Http\Controllers',
        ], function ($router) {
            require __DIR__ . '/..//Routes/web.php';
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('pay.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'pay'
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/pay');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'pay');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'pay');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
