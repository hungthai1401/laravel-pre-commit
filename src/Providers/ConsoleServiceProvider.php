<?php

namespace HT\PreCommit\Providers;

use HT\PreCommit\Commands\InstallGitPreCommitHookCommand;
use HT\PreCommit\Commands\PreCommitCommand;
use HT\PreCommit\Commands\PublishPhpCSConfigurationFileCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Provider: ConsoleServiceProvider
 * @package HT\PreCommit\Providers
 */
class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            PreCommitCommand::class,
            PublishPhpCSConfigurationFileCommand::class,
            InstallGitPreCommitHookCommand::class,
        ]);
    }
}
