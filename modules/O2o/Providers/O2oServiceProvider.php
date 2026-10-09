<?php

namespace Modules\O2o\Providers;

use Illuminate\Support\ServiceProvider;

class O2oServiceProvider extends ServiceProvider
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
        $this->app->register(O2oRepositoryServiceProvider::class);

        $this->app->router->group([
            'namespace' => 'Modules\O2o\Http\Controllers',
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
            __DIR__ . '/../Config/config.php' => config_path('o2o.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php', 'o2o'
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/o2o');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'o2o');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'o2o');
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
