<?php

namespace HT\PreCommit\Tests;

use HT\PreCommit\Providers\PreCommitServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Test: TestCase
 * @package HT\PreCommit\Tests
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array|string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            PreCommitServiceProvider::class,
        ];
    }
}
