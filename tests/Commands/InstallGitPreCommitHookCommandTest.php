<?php

namespace HT\PreCommit\Tests\Commands;

use HT\PreCommit\Tests\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

/**
 * Test: InstallGitPreCommitHookCommandTest
 * @package HT\PreCommit\Tests\Commands
 */
class InstallGitPreCommitHookCommandTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $finder;

    /**
     * set up
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->finder = $this->app['files'];
        $this->finder->makeDirectory(base_path('.git'));
        $this->finder->makeDirectory(base_path('.git/hooks'));
        $this->artisan('git:pre-commit-hook:install');
    }

    /**
     * tear down
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->finder->deleteDirectory(base_path('.git'));
    }

    /**
     * @test
     */
    public function it_generates_a_new_php_cs_configuration_file()
    {
        $this->assertTrue(is_file(base_path('.git/hooks/pre-commit')));
    }

    /**
     * @test
     */
    public function it_generated_correct_file_with_content()
    {
        $file = $this->finder->get(base_path('.git/hooks/pre-commit'));
        $this->assertMatchesSnapshot($file);
    }
}
