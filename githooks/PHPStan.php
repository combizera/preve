<?php

declare(strict_types=1);

namespace GitHooks;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook;
use Illuminate\Support\Arr;
use SebastianFeldmann\Git\Repository;

final class PHPStan implements Hook\Action
{
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $stagedFiles = $repository->getIndexOperator()->getStagedFiles();

        $files = [];

        foreach ($stagedFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                if (!Arr::has($files, $file)) {
                    $files[] = $file;
                }
            }
        }

        $command = 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpstan analyse';
        $flags = ' --memory-limit=1G ';
        $targets = Arr::join($files, ' ');

        echo 'Running ' . $command . $flags . $targets . PHP_EOL;
        shell_exec($command . $flags . $targets);
    }
}
