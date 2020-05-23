<?php

namespace HT\PreCommit\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Provider: PreCommitServiceProvider
 * @package HT\PreCommit\Providers
 */
class PreCommitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../../config/pre-commit.php';
        $this->mergeConfigFrom($configPath, 'pre-commit');
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $configPath => config_path('pre-commit.php'),
            ], 'config');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(ConsoleServiceProvider::class);
    }
}
