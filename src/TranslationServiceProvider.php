<?php

namespace itsmattburgess\LaravelTranslate;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use itsmattburgess\LaravelTranslate\Contracts\TranslationService;
use itsmattburgess\LaravelTranslate\Contracts\InvalidServiceException;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Boots the service provider
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/translate.php' => config_path('translate.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                TranslateCommand::class
            ]);
        }
    }

    /**
     * Registers the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/translate.php', 'translate');

        $this->registerServices();
    }

    /**
     * Registers services used by this package.
     */
    public function registerServices()
    {
        $this->app->singleton(MethodDiscovery::class, function () {
            $config = $this->app['config']['translate'];
            return new MethodDiscovery(new Filesystem, $config['paths'], $config['methods']);
        });

        $this->app->singleton(TranslationService::class, function () {
            $config = $this->app['config']['translate'];
            $service = 'itsmattburgess\LaravelTranslate\Services\\' . ucwords($config['driver']);

            if (! class_exists($service)) {
                throw new InvalidServiceException('Translation service "' . $config['driver'] . '" is not available');
            }

            return app()->make($service);
        });
    }
}
