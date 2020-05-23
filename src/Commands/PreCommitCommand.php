<?php

namespace HT\PreCommit\Commands;

use Illuminate\Console\Command;
use JakubOnderka\PhpParallelLint\ConsoleWriter;
use JakubOnderka\PhpParallelLint\TextOutputColored;
use RuntimeException;
use Illuminate\Support\Facades\Config;

/**
 * Command: PreCommitCommand
 * @package HT\PreCommit\Commands
 */
class PreCommitCommand extends Command
{
    /**
     * Files to be analysed.
     *
     * @var array
     */
    private $files = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:pre-commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Git pre-commit hook, with PHP Lint and PHPCS.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        // check installed dependencies
        $this->checkDependencies();

        // extract PHP files...
        $this->extractFilesToBeAnalysed();

        $output = new TextOutputColored(new ConsoleWriter);

        if (empty($this->files)) {
            $output->writeLine('Success: Nothing to check!', TextOutputColored::TYPE_OK);
            return;
        }

        $this->info('Running PHP lint...');
        if (! $this->lint()) {
            exit($this->fails());
        }

        // Run code sniffer...
        $this->info('Checking PSR-2 Coding Standard...');
        if (! $this->runCodeSniffer()) {
            exit($this->fails());
        }

        $output->writeLine('Your code is perfect, no syntax error found!', TextOutputColored::TYPE_OK);
    }

    /**
     * Check if dependencies exists.
     */
    private function checkDependencies()
    {
        $installedPackages = [];

        exec('composer show -N', $installedPackages);
        $installedPackages = collect($installedPackages);

        $continue = $installedPackages->contains('squizlabs/php_codesniffer');
        if (! $continue) {
            $this->output->error('The packages PHP_CodeSniffer wasn\'t found.');
            exit(1);
        }
    }

    /**
     * Extract PHP files to be analysed from HEAD.
     */
    protected function extractFilesToBeAnalysed()
    {
        if (! $this->exec($command = 'git status --short', $output)) {
            throw new RuntimeException('Unable to run command: ' . $command);
        }

        foreach ($output as $line) {
            if ($path = $this->parseGitStatus($line)) {
                $this->files[] = $path;
            }
        }
    }

    /**
     * Lint the given files.
     * @see https://github.com/php-parallel-lint/PHP-Parallel-Lint
     *
     * @return bool
     */
    protected function lint(): bool
    {
        $process = $this->openParallelLintProcess($pipes);

        foreach ($this->files as $path) {
            fwrite($pipes[0], $path . "\n");
        }

        fclose($pipes[0]);

        if (false === $output = stream_get_contents($pipes[1])) {
            throw new RuntimeException('Unable to get the lint result');
        }

        if (!$this->option('quiet') && trim($output)) {
            $this->output->writeln(trim($output));
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process) === 0;
    }

    /**
     * Opens the parallel-lint program as a process and return the resource
     * (the pipes can be obtained as an out-argument).
     *
     * @param array &$pipes
     * @return resource
     */
    protected function openParallelLintProcess(&$pipes = null)
    {
        $options = [
            '--stdin',
            '--no-progress',
            '--colors',
        ];

        $command = 'vendor/bin/parallel-lint' . ' ' . implode(' ', $options);

        return $this->openProcess($command, $pipes);
    }

    /**
     * Open a process and give the pipes to stdin, stdout, stderr in $pipes
     * out-parameter. Returns the opened process as a resource.
     *
     * @param string $cmd
     * @param array &$pipes
     * @return resource
     */
    protected function openProcess(string $cmd, &$pipes = null)
    {
        $descriptionOrSpec = [
            0 => ['pipe', 'r'],  // stdin is a pipe that the child will read from
            1 => ['pipe', 'w'],  // stdout is a pipe that the child will write to
            2 => ['pipe', 'w'],  // stderr is a pipe that the child will write to
        ];

        return proc_open($cmd, $descriptionOrSpec, $pipes);
    }

    /**
     * Parses the git status line and return the changed file or null if the
     * file hasn't changed.
     *
     * @param string $line
     * @return string|null
     */
    protected function parseGitStatus(string $line)
    {
        if (! preg_match('/^(.)(.)\s(\S+)(\s->\S+)?$/', $line, $matches)) {
            return null; // ignore incorrect lines
        }
        list(, , $second, $path) = $matches;
        if (! in_array($second, ['M', 'A'], true)) {
            return null;
        }
        return $path;
    }

    /**
     * Run Code Sniffer to detect PSR2 code standard.
     */
    protected function runCodeSniffer()
    {
        $options = [
            '--standard=' . Config::get('pre-commit.rules.standard'),
            '--ignore=' . implode(',', Config::get('pre-commit.rules.ignored')),
            '--colors',
        ];

        $cmd = 'vendor/bin/phpcs' . ' ' . implode(' ', $options) . ' ' . implode(' ', $this->files);

        $status = $this->exec($cmd, $output);

        if (!$this->option('quiet') && $output) {
            $this->output->writeln(implode("\n", $output));
        }

        return $status;
    }

    /**
     * Execute the command, return true if status is success, false otherwise.
     *
     * @param string $command
     * @param array &$output
     * @param int &$status
     * @return bool
     */
    protected function exec(string $command, &$output = null, &$status = null): bool
    {
        exec($command, $output, $status);

        return $status == 0;
    }

    /**
     * Command failed message, returns 1.
     *
     * @return int
     */
    protected function fails()
    {
        $message = 'Commit aborted: you have errors in your code!';

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && $this->exec('which cowsay')) {
            $this->exec('cowsay -f unipony-smaller "{$message}"', $output);
            $message = implode("\n", $output);
        }

        $this->output->writeln('<fg=red>' . $message . '</fg=red>');

        return 1;
    }
}
