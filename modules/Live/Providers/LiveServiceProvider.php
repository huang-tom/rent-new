<?php

namespace Modules\Live\Providers;

use Illuminate\Support\ServiceProvider;

class LiveServiceProvider extends ServiceProvider
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
		$this->app->register(LiveRepositoryServiceProvider::class);

        $this->app->router->group([
            'namespace' => 'Modules\Live\Http\Controllers',
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
            __DIR__.'/../Config/config.php' => config_path('live.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'live'
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/live');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'live');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'live');
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
