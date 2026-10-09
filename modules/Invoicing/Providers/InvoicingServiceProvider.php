<?php

namespace Modules\Invoicing\Providers;

use Illuminate\Support\ServiceProvider;

class InvoicingServiceProvider extends ServiceProvider
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
        $this->app->register(InvoicingRepositoryServiceProvider::class);

        $this->app->router->group([
            'namespace' => 'Modules\Invoicing\Http\Controllers',
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
            __DIR__.'/../Config/config.php' => config_path('invoicing.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'invoicing'
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/invoicing');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'invoicing');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'invoicing');
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
