<?php

declare(strict_types=1);

namespace GitHooks;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook;
use SebastianFeldmann\Git\Repository;

final class Pint implements Hook\Action
{
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $stagedFiles = $repository->getIndexOperator()->getStagedFiles();

        foreach ($stagedFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                shell_exec('vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'pint ' . $file);
                shell_exec('git add ' . $file);
            }
        }
    }
}
