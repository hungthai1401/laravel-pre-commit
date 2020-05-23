<?php

namespace HT\PreCommit\Tests\Commands;

use HT\PreCommit\Tests\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

/**
 * Test: PublishPhpCSConfigurationFileCommandTest
 * @package HT\PreCommit\Tests\Commands
 */
class PublishPhpCSConfigurationFileCommandTest extends TestCase
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
        $this->finder->copy(__DIR__ . '/../../phpcs.xml', base_path('phpcs.xml'));
        $this->artisan('git:publish-phpcs');
    }

    /**
     * tear down
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->finder->delete(base_path('phpcs.xml'));
    }

    /**
     * @test
     */
    public function it_generates_a_new_php_cs_configuration_file()
    {
        $this->assertTrue(is_file(base_path('phpcs.xml')));
    }

    /**
     * @test
     */
    public function it_generated_correct_file_with_content()
    {
        $file = $this->finder->get(base_path('phpcs.xml'));
        $this->assertMatchesSnapshot($file);
    }
}
