<?php

declare(strict_types=1);

namespace GitHooks;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook;
use RuntimeException;
use SebastianFeldmann\Git\Repository;

final class EsLint implements Hook\Action
{
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $stagedFiles = $repository->getIndexOperator()->getStagedFiles();

        $filesToLint = [];

        foreach ($stagedFiles as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            if (in_array($ext, ['vue', 'ts', 'js', 'tsx', 'jsx'], true)) {
                $filesToLint[] = $file;
            }
        }

        if ($filesToLint === []) {
            return;
        }

        $files = implode(' ', array_map('escapeshellarg', $filesToLint));
        $output = [];
        $exitCode = 0;

        exec('npx eslint --fix ' . $files, $output, $exitCode);

        foreach ($filesToLint as $file) {
            shell_exec('git add ' . escapeshellarg($file));
        }

        if ($exitCode !== 0) {
            throw new RuntimeException("ESLint found errors:\n" . implode("\n", $output));
        }
    }
}
