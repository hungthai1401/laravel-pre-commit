<?php

namespace HT\PreCommit\Commands;

use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

/**
 * Command: InstallGitPreCommitHookCommand
 * @package HT\PreCommit\Commands
 */
class InstallGitPreCommitHookCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:pre-commit-hook:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install GIT Pre-commit hook';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws ReflectionException
     */
    public function handle()
    {
        $this->hasBeenInitializedWithGit();
        $this->installHook(PreCommitCommand::class)
            ? $this->info('Hook pre-commit successfully installed')
            : $this->error('Unable to install pre-commit hook');
    }

    /**
     * Check project has been initialized with git
     */
    public function hasBeenInitializedWithGit()
    {
        if (! is_dir(base_path('.git'))) {
            $this->error('Please initialize with git before install git hooks');
            exit(0);
        }
    }

    /**
     * Install the hook command.
     *
     * @param string $class
     * @return bool
     * @throws ReflectionException
     */
    protected function installHook(string $class): bool
    {
        $signature = $this->getCommandSignature($class);
        $script = $this->getHookScript($signature);
        $path = base_path('.git/hooks/pre-commit');

        if (file_exists($path)) {
            if (! $this->confirmToProceed($path . ' already exists, do you want to overwrite it?', true)) {
                return false;
            }
        }

        return $this->writeHookScript($path, $script);
    }

    /**
     * Get the given command's class signature (e.g. git:pre-commit-hook).
     *
     * @param string $class
     * @return string
     * @throws ReflectionException
     */
    protected function getCommandSignature(string $class): string
    {
        $reflect = new ReflectionClass($class);
        $properties = $reflect->getDefaultProperties();

        if (! preg_match('/^(\S+)/', $properties['signature'], $matches)) {
            throw new RuntimeException('Cannot read signature of ' . $class);
        }

        list(, $signature) = $matches;

        return $signature;
    }

    /**
     * Get the hook script content.
     *
     * @param string $signature
     * @return string
     */
    protected function getHookScript(string $signature): string
    {
        $artisan = base_path('artisan');
        return "#!/bin/sh\n/usr/bin/env php " . addslashes($artisan) . ' ' . $signature . "\n";
    }

    /**
     * Writes the git hook script file and return true on success, false otherwise.
     *
     * @param string $path
     * @param string $script
     * @return bool
     */
    protected function writeHookScript(string $path, string $script): bool
    {
        if (! $result = file_put_contents($path, $script)) {
            return false;
        }

        // read + write for owner, execute for everyone
        if (! chmod($path, 0755)) {
            return false;
        }

        return true;
    }
}
