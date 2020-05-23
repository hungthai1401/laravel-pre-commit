<?php

namespace HT\PreCommit\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

/**
 * Command: PublishPhpCSConfigurationFileCommand
 * @package HT\PreCommit\Commands
 */
class PublishPhpCSConfigurationFileCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:publish-phpcs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish default phpcs.xml configuration file';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return bool
     */
    public function handle()
    {
        $sampleConfigFilePath = base_path('vendor/hungthai1401/laravel-pre-commit/phpcs.xml');
        $rootConfigFilePath = base_path('phpcs.xml');
        if (! file_exists($sampleConfigFilePath)) {
            $this->error('The sample phpcs.xml does not exist! Try to reinstall hungthai1401/laravel-pre-commit package!');
            return false;
        }

        if (file_exists($rootConfigFilePath)) {
            if (! $this->confirmToProceed('phpcs.xml already exists, do you want to overwrite it?', true)) {
                return false;
            }

            //remove old phpcs.xml file form root
            unlink($rootConfigFilePath);
        }

        if (! $this->writePHPCSConfigFile($sampleConfigFilePath, $rootConfigFilePath)) {
            $this->error('Unable to create phpcs.xml');
            return false;
        }

        $this->info('phpcs.xml successfully created!');
        return true;
    }

    /**
     * Copy phpcs.xml file to root and return true on success, false otherwise.
     *
     * @param string $sampleConfigFilePath
     * @param string $rootConfigFilePath
     * @return bool
     */
    protected function writePHPCSConfigFile(string $sampleConfigFilePath, string $rootConfigFilePath): bool
    {
        // copy sample phpcs.xml file to root
        if (! copy($sampleConfigFilePath, $rootConfigFilePath)) {
            return false;
        }

        return true;
    }
}
