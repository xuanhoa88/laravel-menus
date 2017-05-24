<?php
namespace Llama\Menus;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('menus', function ($app) {
            $this->loadViewsFrom(realpath(__DIR__ . '/../resources/views'), 'menus');
            return $this->app->make(Menu::class);
        });
    }

    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->mergeConfigFrom($configPath = __DIR__ . '/../config/config.php', 'llama.menus');
        $this->publishes([
            $configPath => config_path('llama/menus.php'),
        ], 'config');
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['menus'];
    }
}
